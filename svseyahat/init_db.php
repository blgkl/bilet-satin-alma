<?php
// Basit DB başlatma dosyası. data/database.sqlite oluşturur ve tabloları doldurur.
require_once __DIR__ . '/src/db.php';
$db = get_db();

$sqls = [
"CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT, role TEXT, credit INTEGER DEFAULT 0, firm_id INTEGER)",
"CREATE TABLE IF NOT EXISTS firms (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)",
"CREATE TABLE IF NOT EXISTS rides (id INTEGER PRIMARY KEY AUTOINCREMENT, firm_id INTEGER, from_city TEXT, to_city TEXT, depart_at TEXT, price INTEGER, seats_total INTEGER, seats_booked INTEGER DEFAULT 0)",
"CREATE TABLE IF NOT EXISTS tickets (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, ride_id INTEGER, seat INTEGER, price_paid INTEGER, created_at TEXT)",
"CREATE TABLE IF NOT EXISTS coupons (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE, discount_percent INTEGER, usage_limit INTEGER, expires_at TEXT, used_count INTEGER DEFAULT 0)",
];
foreach($sqls as $s) $db->exec($s);

// örnek veri
$db->exec("INSERT OR IGNORE INTO users (id, username, password, role, credit) VALUES (1, 'admin', '".password_hash('admin123', PASSWORD_DEFAULT)."', 'admin', 0)");
$db->exec("INSERT OR IGNORE INTO firms (id, name) VALUES (1, 'Ankara Tur')");
$db->exec("INSERT OR IGNORE INTO users (id, username, password, role, credit, firm_id) VALUES (2, 'firma1', '".password_hash('firma123', PASSWORD_DEFAULT)."', 'firm_admin', 0, 1)");
$db->exec("INSERT OR IGNORE INTO users (id, username, password, role, credit) VALUES (3, 'user1', '".password_hash('user123', PASSWORD_DEFAULT)."', 'user', 500)");

$db->exec("INSERT OR IGNORE INTO rides (id, firm_id, from_city, to_city, depart_at, price, seats_total) VALUES (1,1,'Ankara','Istanbul','2025-11-01 10:00:00',100,40)");

echo "DB initialized.\n";