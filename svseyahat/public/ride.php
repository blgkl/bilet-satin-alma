<?php
require_once __DIR__ . '/../src/config.php';
$db = get_db();
$id = $_GET['id'] ?? null; if(!$id) header('Location: /index.php');
$stmt = $db->prepare('SELECT r.*, f.name as firm_name FROM rides r LEFT JOIN firms f ON r.firm_id=f.id WHERE r.id=?');
$stmt->execute([$id]); $ride = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!doctype html><html><body>
<h1>Sefer Detayı</h1>
<p>Firma: <?= preg_replace("/[^a-zA-Z0-9\s]/", "", $ride['firm_name']) ?></p>
<p><?=htmlspecialchars($ride['from_city'])?> -> <?=htmlspecialchars($ride['to_city'])?></p>
<p>Tarih: <?=htmlspecialchars($ride['depart_at'])?></p>
<p>Fiyat: <?=htmlspecialchars($ride['price'])?></p>
<?php if(current_user()): ?>
<form method="post" action="/purchase.php">
<input type="hidden" name="ride_id" value="<?=$ride['id']?>">
Koltuk seç (1-<?=$ride['seats_total']?>): <input name="seat" type="number" min="1" max="<?=$ride['seats_total']?>">
Kupon: <input name="coupon">
<button>Satın Al</button>
</form>
<?php else: ?>
<p><a href="/login.php">Giriş yap</a> bilet almak için.</p>
<?php endif; ?>
</body></html>