<?php
require_once __DIR__ . '/../src/config.php';
$db = get_db();

// Örnek kullanıcı session (giriş yapılmış varsayıyoruz)
if (!isset($_SESSION['user'])) {
    // Normalde login sistemi burada olmalı
    $_SESSION['user'] = [
        'id' => 3,
        'username' => 'user1',
        'credit' => 500
    ];
}
$user = $_SESSION['user'];

// Sefer arama
$q_from = $_GET['from'] ?? '';
$q_to   = $_GET['to'] ?? '';

$params = [];
$where  = [];

if($q_from){ 
    $where[] = 'from_city LIKE ?'; 
    $params[] = "%$q_from%"; 
}
if($q_to){ 
    $where[] = 'to_city LIKE ?'; 
    $params[] = "%$q_to%"; 
}

$sql = 'SELECT r.*, f.name as firm_name 
        FROM rides r 
        LEFT JOIN firms f ON r.firm_id = f.id' . 
       (count($where) ? ' WHERE '.implode(' AND ', $where) : '');

$stmt = $db->prepare($sql); 
$stmt->execute($params); 
$rides = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ana Sayfa</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<h1>Sefer Ara</h1>
<form method="get">
    Kalkış: <input name="from" value="<?= safe_html($q_from) ?>"> 
    Varış: <input name="to" value="<?= safe_html($q_to) ?>"> 
    <button>Ara</button>
</form>

<?php if($rides): ?>
<table border="1" cellpadding="5" cellspacing="0">
<tr>
    <th>Firma</th>
    <th>Kalkış</th>
    <th>Varış</th>
    <th>Tarih</th>
    <th>Fiyat</th>
    <th>Seats</th>
    <th>İşlem</th>
</tr>
<?php foreach($rides as $r): ?>
<tr>
    <td><?= safe_html($r['firm_name'] ?? 'Bilinmiyor') ?></td>
    <td><?= safe_html($r['from_city'] ?? '') ?></td>
    <td><?= safe_html($r['to_city'] ?? '') ?></td>
    <td><?= safe_html($r['depart_at'] ?? '') ?></td>
    <td><?= safe_html($r['price'] ?? '') ?></td>
    <?php 
        $booked = $r['seats_booked'] ?? 0;
        $total  = $r['seats_total'] ?? 0;
        $available = $total - $booked;
    ?>
    <td><?= $available . "/" . $total ?></td>
    <td>
        <a href="/ride.php?id=<?= $r['id'] ?>">Detay</a>
        <?php if ($available > 0): ?>
            <form method="post" action="/purchase.php" style="display:inline">
                <input type="hidden" name="ride_id" value="<?= $r['id'] ?>">
                <button type="submit">Satın Al</button>
            </form>
        <?php else: ?>
            <button disabled>Dolu</button>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>Sefer bulunamadı.</p>
<?php endif; ?>
</body>
</html>
