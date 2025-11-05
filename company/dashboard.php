<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireCompanyAdmin();

$error = '';
$success = '';

// Get company routes
$routes = getCompanyRoutes($_SESSION['company_id']);

// Handle route deletion
if ($_POST && isset($_POST['delete_route'])) {
    $routeId = $_POST['route_id'];
    
    // Check if route belongs to this company
    $stmt = $db->prepare("SELECT company_id FROM routes WHERE id = ?");
    $stmt->execute([$routeId]);
    $routeCompanyId = $stmt->fetchColumn();
    
    if ($routeCompanyId == $_SESSION['company_id']) {
        // Check if route has active tickets
        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE route_id = ? AND status = 'active'");
        $stmt->execute([$routeId]);
        $ticketCount = $stmt->fetchColumn();
        
        if ($ticketCount > 0) {
            $error = 'Bu seferde aktif biletler bulunduğu için silinemez!';
        } else {
            $stmt = $db->prepare("DELETE FROM routes WHERE id = ?");
            if ($stmt->execute([$routeId])) {
                $success = 'Sefer başarıyla silindi!';
                $routes = getCompanyRoutes($_SESSION['company_id']);
            } else {
                $error = 'Sefer silinirken bir hata oluştu!';
            }
        }
    } else {
        $error = 'Bu seferi silme yetkiniz yok!';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Paneli - Bilet Platformu</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Firma Panel</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
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
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Firma Paneli</h2>
                    <a href="add_route.php" class="btn btn-primary">Yeni Sefer Ekle</a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Seferlerim</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($routes)): ?>
                            <div class="alert alert-info">
                                Henüz sefer eklenmemiş.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kalkış</th>
                                            <th>Varış</th>
                                            <th>Tarih</th>
                                            <th>Saat</th>
                                            <th>Fiyat</th>
                                            <th>Koltuk Durumu</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($routes as $route): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($route['departure_city']); ?></td>
                                                <td><?php echo htmlspecialchars($route['arrival_city']); ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($route['departure_date'])); ?></td>
                                                <td><?php echo date('H:i', strtotime($route['departure_time'])); ?></td>
                                                <td><?php echo number_format($route['price'], 2); ?> ₺</td>
                                                <td>
                                                    <span class="badge bg-success"><?php echo $route['total_seats'] - $route['booked_seats']; ?></span>
                                                    /
                                                    <span class="badge bg-secondary"><?php echo $route['total_seats']; ?></span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_route.php?id=<?php echo $route['id']; ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmDelete(<?php echo $route['id']; ?>)">
                                                            Sil
                                                        </button>
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

    <!-- Delete Route Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sefer Silme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu seferi silmek istediğinizden emin misiniz?</p>
                    <p class="text-danger">Bu işlem geri alınamaz!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="route_id" id="deleteRouteId">
                        <button type="submit" name="delete_route" class="btn btn-danger">Evet, Sil</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(routeId) {
            document.getElementById('deleteRouteId').value = routeId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
