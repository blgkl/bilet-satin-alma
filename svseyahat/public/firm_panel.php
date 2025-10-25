<?php
require_once __DIR__ . '/../src/config.php';
$u = current_user(); if(!$u || $u['role']!=='firm_admin') header('Location: /login.php');
$db = get_db();
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['new_ride'])){
    $stmt = $db->prepare('INSERT INTO rides (firm_id, from_city, to_city, depart_at, price, seats_total) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$u['firm_id'], $_POST['from_city'], $_POST['to_city'], $_POST['depart_at'], $_POST['price'], $_POST['seats_total']]);
    flash('Sefer eklendi'); header('Location: /firm_panel.php'); exit;
}
$rides = $db->prepare('SELECT * FROM rides WHERE firm_id = ?'); $rides->execute([$u['firm_id']]); $rides = $rides->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html><body>
<h1>Firma Paneli</h1>
<form method="post">
<input name="from_city" placeholder="Kalkış"> <input name="to_city" placeholder="Varış"> <input name="depart_at" placeholder="YYYY-MM-DD HH:MM:SS"> <input name="price" placeholder="Fiyat"> <input name="seats_total" placeholder="Koltuk sayısı"> <button name="new_ride">Ekle</button>
</form>
<ul><?php foreach($rides as $r) echo '<li>'.htmlspecialchars($r['from_city']).'->'.htmlspecialchars($r['to_city']).' '.$r['depart_at'].'</li>'; ?></ul>
</body></html>