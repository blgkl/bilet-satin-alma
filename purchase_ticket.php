<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmanız gerekiyor!']);
    exit();
}

if ($_POST) {
    $routeId = $_POST['route_id'] ?? 0;
    $seatNumber = $_POST['seat_number'] ?? 0;
    $couponCode = $_POST['coupon_code'] ?? '';
    
    try {
        $route = getRouteById($routeId);
        if (!$route) {
            throw new Exception('Sefer bulunamadı!');
        }
        
        $success = purchaseTicket($_SESSION['user_id'], $routeId, $seatNumber, $route['price'], $couponCode);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Bilet başarıyla satın alındı!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bilet satın alınamadı!']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek!']);
}
?>
