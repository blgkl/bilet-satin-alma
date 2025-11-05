<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$ticketId = $_GET['id'] ?? 0;

// Get ticket details
$stmt = $db->prepare("
    SELECT t.*, r.departure_city, r.arrival_city, r.departure_date, r.departure_time, 
           r.arrival_date, r.arrival_time, c.name as company_name, c.description as company_description,
           u.full_name, u.email, u.phone
    FROM tickets t
    JOIN routes r ON t.route_id = r.id
    JOIN companies c ON r.company_id = c.id
    JOIN users u ON t.user_id = u.id
    WHERE t.id = ? AND t.user_id = ? AND t.status = 'active'
");

$stmt->execute([$ticketId, $_SESSION['user_id']]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    header('Location: dashboard.php');
    exit();
}

// Create PDF content
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bilet - ' . htmlspecialchars($ticket['company_name']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #333; }
        .ticket-title { font-size: 20px; margin-top: 10px; }
        .ticket-info { margin: 20px 0; }
        .info-row { display: flex; margin: 10px 0; }
        .info-label { font-weight: bold; width: 150px; }
        .info-value { flex: 1; }
        .route-info { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .passenger-info { background-color: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">' . htmlspecialchars($ticket['company_name']) . '</div>
        <div class="ticket-title">OTOBÜS BİLETİ</div>
    </div>
    
    <div class="ticket-info">
        <div class="info-row">
            <div class="info-label">Bilet No:</div>
            <div class="info-value">' . $ticket['id'] . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Satın Alma Tarihi:</div>
            <div class="info-value">' . date('d.m.Y H:i', strtotime($ticket['purchase_date'])) . '</div>
        </div>
    </div>
    
    <div class="route-info">
        <h3>Sefer Bilgileri</h3>
        <div class="info-row">
            <div class="info-label">Kalkış:</div>
            <div class="info-value">' . htmlspecialchars($ticket['departure_city']) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Varış:</div>
            <div class="info-value">' . htmlspecialchars($ticket['arrival_city']) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Kalkış Tarihi:</div>
            <div class="info-value">' . date('d.m.Y', strtotime($ticket['departure_date'])) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Kalkış Saati:</div>
            <div class="info-value">' . date('H:i', strtotime($ticket['departure_time'])) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Varış Tarihi:</div>
            <div class="info-value">' . date('d.m.Y', strtotime($ticket['arrival_date'])) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Varış Saati:</div>
            <div class="info-value">' . date('H:i', strtotime($ticket['arrival_time'])) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Koltuk No:</div>
            <div class="info-value">' . $ticket['seat_number'] . '</div>
        </div>
    </div>
    
    <div class="passenger-info">
        <h3>Yolcu Bilgileri</h3>
        <div class="info-row">
            <div class="info-label">Ad Soyad:</div>
            <div class="info-value">' . htmlspecialchars($ticket['full_name']) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">E-posta:</div>
            <div class="info-value">' . htmlspecialchars($ticket['email']) . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Telefon:</div>
            <div class="info-value">' . htmlspecialchars($ticket['phone'] ?? 'Belirtilmemiş') . '</div>
        </div>
    </div>
    
    <div class="ticket-info">
        <h3>Fiyat Bilgileri</h3>
        <div class="info-row">
            <div class="info-label">Bilet Fiyatı:</div>
            <div class="info-value">' . number_format($ticket['price'], 2) . ' ₺</div>
        </div>';
        
if ($ticket['discount_amount'] > 0) {
    $html .= '
        <div class="info-row">
            <div class="info-label">İndirim:</div>
            <div class="info-value">-' . number_format($ticket['discount_amount'], 2) . ' ₺</div>
        </div>';
}

$html .= '
        <div class="info-row">
            <div class="info-label"><strong>Toplam Ödenen:</strong></div>
            <div class="info-value"><strong>' . number_format($ticket['price'] - $ticket['discount_amount'], 2) . ' ₺</strong></div>
        </div>
    </div>
    
    <div class="footer">
        <p>Bu bilet ' . date('d.m.Y H:i') . ' tarihinde yazdırılmıştır.</p>
        <p>İyi yolculuklar dileriz!</p>
    </div>
</body>
</html>';

// Set headers for PDF download
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="bilet_' . $ticket['id'] . '.html"');

echo $html;
?>
