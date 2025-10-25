<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;
function generate_ticket_pdf($ticket){
    $html = "<h1>Bilet</h1>";
    $html .= "<p>Ticket ID: {$ticket['id']}</p>";
    $html .= "<p>Kullanıcı ID: {$ticket['user_id']}</p>";
    $html .= "<p>Sefer ID: {$ticket['ride_id']}</p>";
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();
    return $dompdf->output();
}