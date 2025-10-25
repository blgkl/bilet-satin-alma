<?php
// SQLite ile örnek
$pdo = new PDO('sqlite:./database.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function initialize_database($pdo) {
    $sqls = [
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT, 
            username TEXT UNIQUE, 
            password TEXT, 
            role TEXT, 
            credit INTEGER DEFAULT 0, 
            firm_id INTEGER
        )",
        "CREATE TABLE IF NOT EXISTS firms (
            id INTEGER PRIMARY KEY AUTOINCREMENT, 
            name TEXT
        )",
        "CREATE TABLE IF NOT EXISTS rides (
            id INTEGER PRIMARY KEY AUTOINCREMENT, 
            firm_id INTEGER, 
            from_city TEXT, 
            to_city TEXT, 
            depart_at TEXT, 
            price INTEGER, 
            seats_total INTEGER, 
            seats_booked INTEGER DEFAULT 0
        )",
        "CREATE TABLE IF NOT EXISTS tickets (
            id INTEGER PRIMARY KEY AUTOINCREMENT, 
            user_id INTEGER, 
            ride_id INTEGER, 
            seat INTEGER, 
            price_paid INTEGER, 
            created_at TEXT
        )",
        "CREATE TABLE IF NOT EXISTS coupons (
            id INTEGER PRIMARY KEY AUTOINCREMENT, 
            code TEXT UNIQUE, 
            discount_percent INTEGER, 
            usage_limit INTEGER, 
            expires_at TEXT, 
            used_count INTEGER DEFAULT 0
        )"
    ];

    foreach ($sqls as $sql) {
        $pdo->exec($sql);
    }

    // Varsayılan kullanıcılar ve firma
    $pdo->exec("INSERT OR IGNORE INTO users (id, username, password, role, credit) VALUES (1, 'admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin', 0)");
    $pdo->exec("INSERT OR IGNORE INTO firms (id, name) VALUES (1, 'Ankara Tur')");
    $pdo->exec("INSERT OR IGNORE INTO users (id, username, password, role, credit, firm_id) VALUES 
        (2, 'firma1', '" . password_hash('firma123', PASSWORD_DEFAULT) . "', 'firm_admin', 0, 1)");
    $pdo->exec("INSERT OR IGNORE INTO users (id, username, password, role, credit) VALUES 
        (3, 'user1', '" . password_hash('user123', PASSWORD_DEFAULT) . "', 'user', 500)");
}

// Fonksiyonu çağır
initialize_database($pdo);

// Test için seferler
$rides = [
    ['from'=>'Ankara','to'=>'İstanbul','depart'=>'2025-11-01 08:00:00','price'=>450,'total'=>45,'booked'=>30],
    ['from'=>'İstanbul','to'=>'Ankara','depart'=>'2025-11-01 14:00:00','price'=>450,'total'=>45,'booked'=>20],
    ['from'=>'İzmir','to'=>'Ankara','depart'=>'2025-11-02 09:30:00','price'=>500,'total'=>40,'booked'=>10],
    ['from'=>'Ankara','to'=>'İzmir','depart'=>'2025-11-02 13:00:00','price'=>500,'total'=>40,'booked'=>20],
    ['from'=>'İstanbul','to'=>'İzmir','depart'=>'2025-11-03 07:00:00','price'=>550,'total'=>46,'booked'=>0],
    ['from'=>'İzmir','to'=>'İstanbul','depart'=>'2025-11-03 19:00:00','price'=>550,'total'=>46,'booked'=>0],
];

// Güvenli htmlspecialchars fonksiyonu
function safe_html($text){
    if(!$text) return '';
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Ana Sayfa</title></head>
<body>
<h1>Seferler</h1>
<table border="1" cellpadding="5" cellspacing="0">
<tr>
    <th>Kalkış</th><th>Varış</th><th>Tarih</th><th>Fiyat</th><th>Seats</th>
</tr>
<?php foreach($rides as $r): ?>
<tr>
    <td><?= safe_html($r['from']) ?></td>
    <td><?= safe_html($r['to']) ?></td>
    <td><?= safe_html($r['depart']) ?></td>
    <td><?= safe_html($r['price']) ?></td>
    <td><?= ($r['total'] - ($r['booked'] ?? 0))."/".$r['total'] ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
