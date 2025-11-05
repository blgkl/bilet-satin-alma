<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireCompanyAdmin();

$routeId = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Get route details
$stmt = $db->prepare("SELECT * FROM routes WHERE id = ? AND company_id = ?");
$stmt->execute([$routeId, $_SESSION['company_id']]);
$route = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$route) {
    header('Location: dashboard.php');
    exit();
}

if ($_POST) {
    $departureCity = $_POST['departure_city'] ?? '';
    $arrivalCity = $_POST['arrival_city'] ?? '';
    $departureDate = $_POST['departure_date'] ?? '';
    $departureTime = $_POST['departure_time'] ?? '';
    $arrivalDate = $_POST['arrival_date'] ?? '';
    $arrivalTime = $_POST['arrival_time'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $totalSeats = intval($_POST['total_seats'] ?? 0);
    
    if (empty($departureCity) || empty($arrivalCity) || empty($departureDate) || 
        empty($departureTime) || empty($arrivalDate) || empty($arrivalTime) || 
        $price <= 0 || $totalSeats <= 0) {
        $error = 'Tüm alanları doldurun ve geçerli değerler girin!';
    } else {
        // Check if there are active tickets and new seat count is less than booked seats
        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE route_id = ? AND status = 'active'");
        $stmt->execute([$routeId]);
        $bookedSeats = $stmt->fetchColumn();
        
        if ($totalSeats < $bookedSeats) {
            $error = 'Toplam koltuk sayısı, satılmış koltuk sayısından (' . $bookedSeats . ') az olamaz!';
        } else {
            $stmt = $db->prepare("
                UPDATE routes 
                SET departure_city = ?, arrival_city = ?, departure_date = ?, departure_time = ?, 
                    arrival_date = ?, arrival_time = ?, price = ?, total_seats = ?
                WHERE id = ? AND company_id = ?
            ");
            
            if ($stmt->execute([$departureCity, $arrivalCity, $departureDate, $departureTime, $arrivalDate, $arrivalTime, $price, $totalSeats, $routeId, $_SESSION['company_id']])) {
                $success = 'Sefer başarıyla güncellendi!';
                // Refresh route data
                $stmt = $db->prepare("SELECT * FROM routes WHERE id = ? AND company_id = ?");
                $stmt->execute([$routeId, $_SESSION['company_id']]);
                $route = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Sefer güncellenirken bir hata oluştu!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Düzenle - Firma Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Bilet Platformu</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Firma Panel</a>
                <a class="nav-link" href="../auth/logout.php">Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Sefer Düzenle</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="departure_city" class="form-label">Kalkış Şehri</label>
                                        <input type="text" class="form-control" id="departure_city" name="departure_city" value="<?php echo htmlspecialchars($route['departure_city']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="arrival_city" class="form-label">Varış Şehri</label>
                                        <input type="text" class="form-control" id="arrival_city" name="arrival_city" value="<?php echo htmlspecialchars($route['arrival_city']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="departure_date" class="form-label">Kalkış Tarihi</label>
                                        <input type="date" class="form-control" id="departure_date" name="departure_date" value="<?php echo $route['departure_date']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="departure_time" class="form-label">Kalkış Saati</label>
                                        <input type="time" class="form-control" id="departure_time" name="departure_time" value="<?php echo $route['departure_time']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="arrival_date" class="form-label">Varış Tarihi</label>
                                        <input type="date" class="form-control" id="arrival_date" name="arrival_date" value="<?php echo $route['arrival_date']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="arrival_time" class="form-label">Varış Saati</label>
                                        <input type="time" class="form-control" id="arrival_time" name="arrival_time" value="<?php echo $route['arrival_time']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Fiyat (₺)</label>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $route['price']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="total_seats" class="form-label">Toplam Koltuk Sayısı</label>
                                        <input type="number" class="form-control" id="total_seats" name="total_seats" min="1" value="<?php echo $route['total_seats']; ?>" required>
                                        <?php
                                        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE route_id = ? AND status = 'active'");
                                        $stmt->execute([$routeId]);
                                        $bookedSeats = $stmt->fetchColumn();
                                        ?>
                                        <small class="text-muted">Satılmış koltuk sayısı: <?php echo $bookedSeats; ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary me-md-2">İptal</a>
                                <button type="submit" class="btn btn-primary">Güncelle</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
