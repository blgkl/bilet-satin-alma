<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$error = '';
$success = '';

// Get statistics
$stats = [
    'total_companies' => $db->query("SELECT COUNT(*) FROM companies")->fetchColumn(),
    'total_routes' => $db->query("SELECT COUNT(*) FROM routes")->fetchColumn(),
    'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'total_tickets' => $db->query("SELECT COUNT(*) FROM tickets WHERE status = 'active'")->fetchColumn(),
    'total_revenue' => $db->query("SELECT SUM(price - discount_amount) FROM tickets WHERE status = 'active'")->fetchColumn() ?: 0
];

// Get companies
$companies = getCompanies();

// Get company admins
$stmt = $db->prepare("
    SELECT u.*, c.name as company_name 
    FROM users u 
    LEFT JOIN companies c ON u.company_id = c.id 
    WHERE u.role = 'company_admin'
    ORDER BY u.created_at DESC
");
$stmt->execute();
$companyAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get coupons
$stmt = $db->prepare("SELECT * FROM coupons ORDER BY created_at DESC");
$stmt->execute();
$coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Bilet Platformu</title>
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
                        <a class="nav-link" href="dashboard.php">Admin Panel</a>
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
        <h2>Admin Paneli</h2>
        
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $stats['total_companies']; ?></h5>
                        <p class="card-text">Firma</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $stats['total_routes']; ?></h5>
                        <p class="card-text">Sefer</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $stats['total_users']; ?></h5>
                        <p class="card-text">Kullanıcı</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $stats['total_tickets']; ?></h5>
                        <p class="card-text">Aktif Bilet</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo number_format($stats['total_revenue'], 2); ?> ₺</h5>
                        <p class="card-text">Toplam Gelir</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Hızlı İşlemler</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="add_company.php" class="btn btn-primary">Yeni Firma Ekle</a>
                            <a href="add_company_admin.php" class="btn btn-success">Firma Admin Ekle</a>
                            <a href="add_coupon.php" class="btn btn-warning">Kupon Ekle</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Companies -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Firmalar</h5>
                        <a href="add_company.php" class="btn btn-sm btn-primary">Yeni Firma</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Firma Adı</th>
                                        <th>Açıklama</th>
                                        <th>İletişim</th>
                                        <th>Oluşturulma</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($companies as $company): ?>
                                        <tr>
                                            <td><?php echo $company['id']; ?></td>
                                            <td><?php echo htmlspecialchars($company['name']); ?></td>
                                            <td><?php echo htmlspecialchars($company['description'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($company['contact_info'] ?? ''); ?></td>
                                            <td><?php echo date('d.m.Y', strtotime($company['created_at'])); ?></td>
                                            <td>
                                                <a href="edit_company.php?id=<?php echo $company['id']; ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Company Admins -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Firma Adminleri</h5>
                        <a href="add_company_admin.php" class="btn btn-sm btn-success">Yeni Admin</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kullanıcı Adı</th>
                                        <th>Ad Soyad</th>
                                        <th>E-posta</th>
                                        <th>Firma</th>
                                        <th>Oluşturulma</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($companyAdmins as $admin): ?>
                                        <tr>
                                            <td><?php echo $admin['id']; ?></td>
                                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['company_name'] ?? 'Atanmamış'); ?></td>
                                            <td><?php echo date('d.m.Y', strtotime($admin['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Coupons -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Kuponlar</h5>
                        <a href="add_coupon.php" class="btn btn-sm btn-warning">Yeni Kupon</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kod</th>
                                        <th>Tip</th>
                                        <th>Değer</th>
                                        <th>Kullanım</th>
                                        <th>Limit</th>
                                        <th>Son Kullanma</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($coupons as $coupon): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                                            <td><?php echo $coupon['discount_type'] === 'percentage' ? 'Yüzde' : 'Sabit'; ?></td>
                                            <td><?php echo $coupon['discount_value']; ?><?php echo $coupon['discount_type'] === 'percentage' ? '%' : ' ₺'; ?></td>
                                            <td><?php echo $coupon['used_count']; ?></td>
                                            <td><?php echo $coupon['usage_limit'] ?? 'Sınırsız'; ?></td>
                                            <td><?php echo $coupon['expiry_date'] ? date('d.m.Y', strtotime($coupon['expiry_date'])) : 'Sınırsız'; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $coupon['is_active'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $coupon['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
