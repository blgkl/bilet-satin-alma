<?php
require_once 'config/database.php';

// User authentication functions
function loginUser($username, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['company_id'] = $user['company_id'];
        return true;
    }
    
    return false;
}

function registerUser($username, $email, $password, $fullName, $phone) {
    global $db;
    
    // Check if username or email already exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return false;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO users (username, email, password, full_name, phone, credit) 
        VALUES (?, ?, ?, ?, ?, 100.00)
    ");
    
    return $stmt->execute([$username, $email, $hashedPassword, $fullName, $phone]);
}

function logoutUser() {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Route functions
function searchRoutes($from, $to, $date) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT r.*, c.name as company_name,
               (SELECT COUNT(*) FROM tickets t WHERE t.route_id = r.id AND t.status = 'active') as booked_seats
        FROM routes r
        JOIN companies c ON r.company_id = c.id
        WHERE r.departure_city LIKE ? AND r.arrival_city LIKE ? AND r.departure_date = ?
        ORDER BY r.departure_time
    ");
    
    $stmt->execute(["%$from%", "%$to%", $date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRouteById($id) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT r.*, c.name as company_name, c.description as company_description
        FROM routes r
        JOIN companies c ON r.company_id = c.id
        WHERE r.id = ?
    ");
    
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getBookedSeats($routeId) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT seat_number FROM tickets 
        WHERE route_id = ? AND status = 'active'
    ");
    
    $stmt->execute([$routeId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Ticket functions
function purchaseTicket($userId, $routeId, $seatNumber, $price, $couponCode = null) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // Check if seat is available
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM tickets 
            WHERE route_id = ? AND seat_number = ? AND status = 'active'
        ");
        $stmt->execute([$routeId, $seatNumber]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Bu koltuk zaten dolu!");
        }
        
        // Calculate discount
        $discountAmount = 0;
        if ($couponCode) {
            $discountAmount = calculateDiscount($couponCode, $price);
            if ($discountAmount > 0) {
                updateCouponUsage($couponCode);
            }
        }
        
        $finalPrice = $price - $discountAmount;
        
        // Check user credit
        $stmt = $db->prepare("SELECT credit FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userCredit = $stmt->fetchColumn();
        
        if ($userCredit < $finalPrice) {
            throw new Exception("Yetersiz kredi! Mevcut kredi: " . number_format($userCredit, 2) . " ₺");
        }
        
        // Deduct credit
        $stmt = $db->prepare("UPDATE users SET credit = credit - ? WHERE id = ?");
        $stmt->execute([$finalPrice, $userId]);
        
        // Create ticket
        $stmt = $db->prepare("
            INSERT INTO tickets (user_id, route_id, seat_number, price, discount_amount, coupon_code) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $routeId, $seatNumber, $price, $discountAmount, $couponCode]);
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function cancelTicket($ticketId, $userId) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // Get ticket details
        $stmt = $db->prepare("
            SELECT t.*, r.departure_date, r.departure_time 
            FROM tickets t
            JOIN routes r ON t.route_id = r.id
            WHERE t.id = ? AND t.user_id = ? AND t.status = 'active'
        ");
        $stmt->execute([$ticketId, $userId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            throw new Exception("Bilet bulunamadı!");
        }
        
        // Check cancellation time (1 hour before departure)
        $departureDateTime = $ticket['departure_date'] . ' ' . $ticket['departure_time'];
        $departureTime = strtotime($departureDateTime);
        $currentTime = time();
        $oneHour = 3600; // 1 hour in seconds
        
        if (($departureTime - $currentTime) < $oneHour) {
            throw new Exception("Kalkış saatine 1 saatten az kaldığı için bilet iptal edilemez!");
        }
        
        // Refund credit
        $refundAmount = $ticket['price'] - $ticket['discount_amount'];
        $stmt = $db->prepare("UPDATE users SET credit = credit + ? WHERE id = ?");
        $stmt->execute([$refundAmount, $userId]);
        
        // Cancel ticket
        $stmt = $db->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$ticketId]);
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function getUserTickets($userId) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT t.*, r.departure_city, r.arrival_city, r.departure_date, r.departure_time, 
               r.arrival_date, r.arrival_time, c.name as company_name
        FROM tickets t
        JOIN routes r ON t.route_id = r.id
        JOIN companies c ON r.company_id = c.id
        WHERE t.user_id = ? AND t.status = 'active'
        ORDER BY r.departure_date, r.departure_time
    ");
    
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Coupon functions
function validateCoupon($code) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT * FROM coupons 
        WHERE code = ? AND is_active = 1 
        AND (expiry_date IS NULL OR expiry_date >= DATE('now'))
        AND (usage_limit IS NULL OR used_count < usage_limit)
    ");
    
    $stmt->execute([$code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function calculateDiscount($couponCode, $price) {
    $coupon = validateCoupon($couponCode);
    
    if (!$coupon) {
        return 0;
    }
    
    if ($coupon['discount_type'] === 'percentage') {
        return ($price * $coupon['discount_value']) / 100;
    } else {
        return min($coupon['discount_value'], $price);
    }
}

function updateCouponUsage($couponCode) {
    global $db;
    
    $stmt = $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
    $stmt->execute([$couponCode]);
}

// Company functions
function getCompanies() {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM companies ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCompanyRoutes($companyId) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT r.*, 
               (SELECT COUNT(*) FROM tickets t WHERE t.route_id = r.id AND t.status = 'active') as booked_seats
        FROM routes r
        WHERE r.company_id = ?
        ORDER BY r.departure_date, r.departure_time
    ");
    
    $stmt->execute([$companyId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Utility functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function requireRole($requiredRole) {
    requireLogin();
    
    if ($_SESSION['user_role'] !== $requiredRole) {
        header('Location: ../index.php');
        exit();
    }
}

function requireAdmin() {
    requireRole('admin');
}

function requireCompanyAdmin() {
    requireRole('company_admin');
}

function formatPrice($price) {
    return number_format($price, 2) . ' ₺';
}

function formatDateTime($date, $time) {
    return date('d.m.Y H:i', strtotime($date . ' ' . $time));
}
?>
