<?php
require_once __DIR__ . '/db.php';
function current_user(){
    if(!isset($_SESSION['user_id'])) return null;
    $db = get_db();
    $stmt = $db->prepare('SELECT id, username, role, credit, firm_id FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function require_role($roles){
    $u = current_user();
    if(!$u || !in_array($u['role'], (array)$roles)){
        header('Location: /login.php'); exit;
    }
}