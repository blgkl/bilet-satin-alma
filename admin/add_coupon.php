<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$error = '';
$success = '';

if ($_POST) {
    $code = $_POST['code'] ?? '';
    $discountType = $_POST['discount_type'] ?? '';
    $discountValue = floatval($_POST['discount_value'] ?? 0);
    $usageLimit = $_POST['usage_limit'] ? intval($_POST['usage_limit']) : null;
    $expiryDate = $_POST['expiry_date'] ?: null;
    
    if (empty($code) || empty($discountType) || $discountValue <= 0) {
        $error = 'Tüm alanları doldurun ve geçerli değerler girin!';
    } else {
        // Check if code already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM coupons WHERE code = ?");
        $stmt->execute([$code]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = 'Bu kupon kodu zaten kullanılıyor!';
        } else {
            $stmt = $db->prepare("
                INSERT INTO coupons (code, discount_type, discount_value, usage_limit, expiry_date) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$code, $discountType, $discountValue, $usageLimit, $expiryDate])) {
                $success = 'Kupon başarıyla eklendi!';
            } else {
                $error = 'Kupon eklenirken bir hata oluştu!';
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
    <title>Yeni Kupon Ekle - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Bilet Platformu</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Admin Panel</a>
                <a class="nav-link" href="../auth/logout.php">Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Yeni Kupon Ekle</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="code" class="form-label">Kupon Kodu</label>
                                <input type="text" class="form-control" id="code" name="code" required>
                            </div>
                            <div class="mb-3">
                                <label for="discount_type" class="form-label">İndirim Tipi</label>
                                <select class="form-control" id="discount_type" name="discount_type" required>
                                    <option value="">Seçiniz</option>
                                    <option value="percentage">Yüzde (%)</option>
                                    <option value="fixed">Sabit Tutar (₺)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="discount_value" class="form-label">İndirim Değeri</label>
                                <input type="number" class="form-control" id="discount_value" name="discount_value" step="0.01" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="usage_limit" class="form-label">Kullanım Limiti (Boş bırakırsanız sınırsız)</label>
                                <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1">
                            </div>
                            <div class="mb-3">
                                <label for="expiry_date" class="form-label">Son Kullanma Tarihi (Boş bırakırsanız sınırsız)</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary me-md-2">İptal</a>
                                <button type="submit" class="btn btn-primary">Kupon Ekle</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
