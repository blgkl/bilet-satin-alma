<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_POST) {
    $couponCode = $_POST['coupon_code'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    
    if (empty($couponCode)) {
        echo json_encode(['valid' => false, 'message' => 'Kupon kodu boş olamaz!']);
        exit();
    }
    
    $coupon = validateCoupon($couponCode);
    
    if (!$coupon) {
        echo json_encode(['valid' => false, 'message' => 'Geçersiz kupon kodu!']);
        exit();
    }
    
    $discountAmount = calculateDiscount($couponCode, $price);
    $finalPrice = $price - $discountAmount;
    
    echo json_encode([
        'valid' => true,
        'discount_amount' => $discountAmount,
        'final_price' => number_format($finalPrice, 2),
        'message' => 'Kupon kodu geçerli!'
    ]);
} else {
    echo json_encode(['valid' => false, 'message' => 'Geçersiz istek!']);
}
?>
