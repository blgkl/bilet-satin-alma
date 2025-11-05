<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$user = null;
$tickets = [];
$error = '';
$success = '';

// Get user info
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user tickets
$tickets = getUserTickets($_SESSION['user_id']);

// Handle ticket cancellation
if ($_POST && isset($_POST['cancel_ticket'])) {
    $ticketId = $_POST['ticket_id'];
    
    try {
        cancelTicket($ticketId, $_SESSION['user_id']);
        $success = 'Bilet başarıyla iptal edildi!';
        // Refresh tickets
        $tickets = getUserTickets($_SESSION['user_id']);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesabım - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Bilet Platformu</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Ana Sayfa</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Hesabım</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Çıkış</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Profil Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                        <p><strong>Kullanıcı Adı:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>E-posta:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Telefon:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Belirtilmemiş'); ?></p>
                        <p><strong>Kredi:</strong> <?php echo number_format($user['credit'], 2); ?> ₺</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Biletlerim</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tickets)): ?>
                            <div class="alert alert-info">
                                Henüz bilet satın almadınız.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sefer</th>
                                            <th>Tarih</th>
                                            <th>Saat</th>
                                            <th>Koltuk</th>
                                            <th>Fiyat</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $ticket): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($ticket['company_name']); ?><br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($ticket['departure_city']); ?> → 
                                                        <?php echo htmlspecialchars($ticket['arrival_city']); ?>
                                                    </small>
                                                </td>
                                                <td><?php echo date('d.m.Y', strtotime($ticket['departure_date'])); ?></td>
                                                <td><?php echo date('H:i', strtotime($ticket['departure_time'])); ?></td>
                                                <td><?php echo $ticket['seat_number']; ?></td>
                                                <td>
                                                    <?php echo number_format($ticket['price'] - $ticket['discount_amount'], 2); ?> ₺
                                                    <?php if ($ticket['discount_amount'] > 0): ?>
                                                        <br><small class="text-success">İndirim: -<?php echo number_format($ticket['discount_amount'], 2); ?> ₺</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="download_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary">PDF İndir</a>
                                                        
                                                        <?php
                                                        $departureDateTime = $ticket['departure_date'] . ' ' . $ticket['departure_time'];
                                                        $departureTime = strtotime($departureDateTime);
                                                        $currentTime = time();
                                                        $oneHour = 3600;
                                                        $canCancel = ($departureTime - $currentTime) >= $oneHour;
                                                        ?>
                                                        
                                                        <?php if ($canCancel): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="confirmCancel(<?php echo $ticket['id']; ?>)">
                                                                İptal Et
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                                İptal Edilemez
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Ticket Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bilet İptali</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu bileti iptal etmek istediğinizden emin misiniz?</p>
                    <p class="text-muted">Bilet ücreti hesabınıza iade edilecektir.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="ticket_id" id="cancelTicketId">
                        <button type="submit" name="cancel_ticket" class="btn btn-danger">Evet, İptal Et</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmCancel(ticketId) {
            document.getElementById('cancelTicketId').value = ticketId;
            new bootstrap.Modal(document.getElementById('cancelModal')).show();
        }
    </script>
</body>
</html>
