<?php
require_once __DIR__ . '/../src/config.php';
$db = get_db();
if($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: /index.php');
$u = current_user(); if(!$u){ flash('Önce giriş yapın'); header('Location: /login.php'); exit; }
$ride_id = $_POST['ride_id']; $seat = intval($_POST['seat']); $coupon = trim($_POST['coupon'] ?? '');
$stmt = $db->prepare('SELECT * FROM rides WHERE id = ?'); $stmt->execute([$ride_id]); $ride = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$ride) { flash('Sefer bulunamadı'); header('Location: /index.php'); exit; }
if($ride['seats_booked'] >= $ride['seats_total']){ flash('Sefer dolu'); header('Location: /ride.php?id='.$ride_id); exit; }
// basit koltuk kontrol (ayrıntılı koltuk map yok)
$price = $ride['price'];
if($coupon){
    $cstmt = $db->prepare('SELECT * FROM coupons WHERE code = ? AND (expires_at IS NULL OR expires_at > datetime("now")) AND used_count < usage_limit');
    $cstmt->execute([$coupon]); $c = $cstmt->fetch(PDO::FETCH_ASSOC);
    if($c){ $price = intval($price * (100 - $c['discount_percent'])/100); $db->exec('UPDATE coupons SET used_count = used_count + 1 WHERE id = '.$c['id']); }
}
//if($u['credit'] < $price){ flash('Yetersiz bakiye'); header('Location: /ride.php?id='.$ride_id); exit; }
// satın al
$pdo = $db;
$pdo->beginTransaction();
$pdo->prepare('UPDATE users SET credit = credit - ? WHERE id = ?')->execute([$price, $u['id']]);
$pdo->prepare('UPDATE rides SET seats_booked = seats_booked + 1 WHERE id = ?')->execute([$ride_id]);
$pdo->prepare('INSERT INTO tickets (user_id, ride_id, seat, price_paid, created_at) VALUES (?, ?, ?, ?, datetime("now"))')->execute([$u['id'],$ride_id,$seat,$price]);
$pdo->commit();
flash('Satın alma başarılı'); header('Location: /my_tickets.php'); exit;
