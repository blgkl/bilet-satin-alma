<?php
require_once __DIR__ . '/../src/config.php';
$db = get_db(); $u = current_user(); if(!$u) header('Location: /login.php');
$stmt = $db->prepare('SELECT t.*, r.from_city, r.to_city, r.depart_at FROM tickets t JOIN rides r ON t.ride_id = r.id WHERE t.user_id = ?');
$stmt->execute([$u['id']]); $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html><body>
<h1>Biletlerim</h1>
<?php foreach($tickets as $t): ?>
<div>
<p>ID: <?=$t['id']?> | <?=$t['from_city']?> -> <?=$t['to_city']?> | <?=$t['depart_at']?> | Koltuk: <?=$t['seat']?></p>
<p><a href="/cancel.php?id=<?=$t['id']?>">İptal Et</a> | <a href="/download_ticket.php?id=<?=$t['id']?>">PDF İndir</a></p>
</div>
<?php endforeach; ?>
</body></html>