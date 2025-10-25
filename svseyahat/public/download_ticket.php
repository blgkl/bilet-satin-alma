<?php
require_once __DIR__ . '/../src/config.php';
$db = get_db(); $u = current_user(); if(!$u) header('Location: /login.php');
$id = $_GET['id'] ?? null; if(!$id) header('Location: /my_tickets.php');
$stmt = $db->prepare('SELECT * FROM tickets WHERE id = ? AND user_id = ?'); $stmt->execute([$id, $u['id']]); $t = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$t) { flash('Bilet bulunamadı'); header('Location: /my_tickets.php'); exit; }
require_once __DIR__ . '/../src/pdf_ticket.php';
$pdf = generate_ticket_pdf($t);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="ticket_'.$t['id'].'.pdf"');
echo $pdf; exit;