<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['user_role'] : 'visitor';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Alma Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Bilet Platformu</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <?php if ($userRole === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Admin Panel</a>
                            </li>
                        <?php elseif ($userRole === 'company_admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="company/dashboard.php">Firma Panel</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user/dashboard.php">Hesabım</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/logout.php">Çıkış</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Giriş Yap</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/register.php">Kayıt Ol</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Sefer Arama</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="search.php">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="from" class="form-label">Kalkış Noktası</label>
                                    <input type="text" class="form-control" id="from" name="from" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="to" class="form-label">Varış Noktası</label>
                                    <input type="text" class="form-control" id="to" name="to" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="date" class="form-label">Tarih</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block w-100">Sefer Ara</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <?php if (isset($_GET['from']) && isset($_GET['to']) && isset($_GET['date'])): ?>
            <?php
            $from = $_GET['from'];
            $to = $_GET['to'];
            $date = $_GET['date'];
            
            $routes = searchRoutes($from, $to, $date);
            ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h4>Sefer Sonuçları</h4>
                    <?php if (empty($routes)): ?>
                        <div class="alert alert-info">
                            Aradığınız kriterlere uygun sefer bulunamadı.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($routes as $route): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($route['company_name']); ?></h5>
                                            <p class="card-text">
                                                <strong>Kalkış:</strong> <?php echo htmlspecialchars($route['departure_city']); ?><br>
                                                <strong>Varış:</strong> <?php echo htmlspecialchars($route['arrival_city']); ?><br>
                                                <strong>Tarih:</strong> <?php echo date('d.m.Y', strtotime($route['departure_date'])); ?><br>
                                                <strong>Saat:</strong> <?php echo date('H:i', strtotime($route['departure_time'])); ?><br>
                                                <strong>Fiyat:</strong> <?php echo number_format($route['price'], 2); ?> ₺<br>
                                                <strong>Koltuk Sayısı:</strong> <?php echo $route['total_seats'] - $route['booked_seats']; ?> / <?php echo $route['total_seats']; ?>
                                            </p>
                                            <a href="route_details.php?id=<?php echo $route['id']; ?>" class="btn btn-primary">Detayları Gör</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>