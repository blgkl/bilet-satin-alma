<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
function get_db() {
    $db_file = __DIR__ . '/../data/database.sqlite';
    try {
        $pdo = new PDO('sqlite:' . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
    }
}
function base_url($path = ''){
    $p = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $p . ($path ? '/' . ltrim($path, '/') : '');
}