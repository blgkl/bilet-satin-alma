<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$date = $_GET['date'] ?? '';

$routes = [];
if ($from && $to && $date) {
    $routes = searchRoutes($from, $to, $date);
}

$isLoggedIn = isLoggedIn();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Arama Sonuçları - Bilet Platformu</title>
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

    <div class="container mt-4">
        <!-- Search Form -->
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
                                    <input type="text" class="form-control" id="from" name="from" value="<?php echo htmlspecialchars($from); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="to" class="form-label">Varış Noktası</label>
                                    <input type="text" class="form-control" id="to" name="to" value="<?php echo htmlspecialchars($to); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="date" class="form-label">Tarih</label>
                                    <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>
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
        <?php if ($from && $to && $date): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h4>Sefer Sonuçları</h4>
                    <p class="text-muted">
                        <strong><?php echo htmlspecialchars($from); ?></strong> → 
                        <strong><?php echo htmlspecialchars($to); ?></strong> | 
                        <strong><?php echo date('d.m.Y', strtotime($date)); ?></strong>
                    </p>
                    
                    <?php if (empty($routes)): ?>
                        <div class="alert alert-info">
                            <h5>Sefer Bulunamadı</h5>
                            <p>Aradığınız kriterlere uygun sefer bulunamadı. Lütfen farklı tarih veya güzergah deneyin.</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($routes as $route): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title"><?php echo htmlspecialchars($route['company_name']); ?></h5>
                                                <span class="badge bg-primary"><?php echo number_format($route['price'], 2); ?> ₺</span>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <div class="text-center">
                                                        <h6 class="text-muted">Kalkış</h6>
                                                        <p class="mb-1"><strong><?php echo htmlspecialchars($route['departure_city']); ?></strong></p>
                                                        <p class="mb-0"><?php echo date('H:i', strtotime($route['departure_time'])); ?></p>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-center">
                                                        <h6 class="text-muted">Varış</h6>
                                                        <p class="mb-1"><strong><?php echo htmlspecialchars($route['arrival_city']); ?></strong></p>
                                                        <p class="mb-0"><?php echo date('H:i', strtotime($route['arrival_time'])); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar"></i> 
                                                        <?php echo date('d.m.Y', strtotime($route['departure_date'])); ?>
                                                    </small>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">
                                                        <i class="bi bi-people"></i> 
                                                        <?php echo $route['total_seats'] - $route['booked_seats']; ?> / <?php echo $route['total_seats']; ?> koltuk
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="d-grid">
                                                <a href="route_details.php?id=<?php echo $route['id']; ?>" class="btn btn-primary">
                                                    Detayları Gör ve Bilet Al
                                                </a>
                                            </div>
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
