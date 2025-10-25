<?php
require_once __DIR__ . '/../src/config.php';
$db = get_db();
$u = current_user(); if(!$u) { header('Location: /login.php'); exit; }
$id = $_GET['id'] ?? null; if(!$id) header('Location: /my_tickets.php');
$stmt = $db->prepare('SELECT t.*, r.depart_at FROM tickets t JOIN rides r ON t.ride_id = r.id WHERE t.id = ? AND t.user_id = ?');
$stmt->execute([$id, $u['id']]); $t = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$t){ flash('Bilet bulunamadı'); header('Location: /my_tickets.php'); exit; }
$depart = strtotime($t['depart_at']);
if($depart - time() < 3600){ flash('Kalkışa 1 saatten az süresi kalan bilet iptal edilemez'); header('Location: /my_tickets.php'); exit; }
$db->beginTransaction();
$db->prepare('DELETE FROM tickets WHERE id = ?')->execute([$id]);
$db->prepare('UPDATE rides SET seats_booked = seats_booked - 1 WHERE id = ?')->execute([$t['ride_id']]);
$db->prepare('UPDATE users SET credit = credit + ? WHERE id = ?')->execute([$t['price_paid'], $u['id']]);
$db->commit();
flash('İptal başarılı'); header('Location: /my_tickets.php'); exit;