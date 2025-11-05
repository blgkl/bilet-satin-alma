<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$routeId = $_GET['id'] ?? 0;
$route = getRouteById($routeId);

if (!$route) {
    header('Location: index.php');
    exit();
}

$bookedSeats = getBookedSeats($routeId);
$isLoggedIn = isLoggedIn();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Detayları - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .seat {
            width: 40px;
            height: 40px;
            margin: 2px;
            display: inline-block;
            text-align: center;
            line-height: 36px;
            border: 1px solid #ccc;
            cursor: pointer;
            border-radius: 4px;
        }
        .seat.available {
            background-color: #28a745;
            color: white;
        }
        .seat.occupied {
            background-color: #dc3545;
            color: white;
            cursor: not-allowed;
        }
        .seat.selected {
            background-color: #007bff;
            color: white;
        }
    </style>
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
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Sefer Detayları</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><?php echo htmlspecialchars($route['company_name']); ?></h5>
                                <p><strong>Kalkış:</strong> <?php echo htmlspecialchars($route['departure_city']); ?></p>
                                <p><strong>Varış:</strong> <?php echo htmlspecialchars($route['arrival_city']); ?></p>
                                <p><strong>Tarih:</strong> <?php echo date('d.m.Y', strtotime($route['departure_date'])); ?></p>
                                <p><strong>Kalkış Saati:</strong> <?php echo date('H:i', strtotime($route['departure_time'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Varış Tarihi:</strong> <?php echo date('d.m.Y', strtotime($route['arrival_date'])); ?></p>
                                <p><strong>Varış Saati:</strong> <?php echo date('H:i', strtotime($route['arrival_time'])); ?></p>
                                <p><strong>Fiyat:</strong> <?php echo number_format($route['price'], 2); ?> ₺</p>
                                <p><strong>Koltuk Sayısı:</strong> <?php echo $route['total_seats']; ?></p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h5>Koltuk Seçimi</h5>
                        <div class="seat-map">
                            <?php for ($i = 1; $i <= $route['total_seats']; $i++): ?>
                                <?php if ($i % 4 == 1): ?>
                                    <div class="row mb-2">
                                <?php endif; ?>
                                
                                <div class="col-3">
                                    <div class="seat <?php echo in_array($i, $bookedSeats) ? 'occupied' : 'available'; ?>" 
                                         data-seat="<?php echo $i; ?>"
                                         <?php echo in_array($i, $bookedSeats) ? 'onclick="alert(\'Bu koltuk dolu!\')"' : 'onclick="selectSeat(' . $i . ')"'; ?>>
                                        <?php echo $i; ?>
                                    </div>
                                </div>
                                
                                <?php if ($i % 4 == 0): ?>
                                    </div>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <span class="seat available" style="display: inline-block; width: 20px; height: 20px; margin-right: 10px;"></span> Müsait
                                <span class="seat occupied" style="display: inline-block; width: 20px; height: 20px; margin: 0 10px 0 20px;"></span> Dolu
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Bilet Satın Al</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$isLoggedIn): ?>
                            <div class="alert alert-warning">
                                Bilet satın almak için giriş yapmanız gerekiyor.
                            </div>
                            <a href="auth/login.php" class="btn btn-primary w-100">Giriş Yap</a>
                        <?php else: ?>
                            <form id="purchaseForm">
                                <input type="hidden" id="routeId" value="<?php echo $routeId; ?>">
                                <input type="hidden" id="selectedSeat" name="seat">
                                <input type="hidden" id="price" value="<?php echo $route['price']; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Seçilen Koltuk</label>
                                    <input type="text" class="form-control" id="selectedSeatDisplay" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="couponCode" class="form-label">Kupon Kodu (İsteğe bağlı)</label>
                                    <input type="text" class="form-control" id="couponCode" name="coupon_code">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Toplam Fiyat</label>
                                    <input type="text" class="form-control" id="totalPrice" value="<?php echo number_format($route['price'], 2); ?> ₺" readonly>
                                </div>
                                
                                <button type="submit" class="btn btn-success w-100" id="purchaseBtn" disabled>Bilet Satın Al</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedSeat = null;
        
        function selectSeat(seatNumber) {
            // Remove previous selection
            document.querySelectorAll('.seat.selected').forEach(seat => {
                seat.classList.remove('selected');
                seat.classList.add('available');
            });
            
            // Select new seat
            const seat = document.querySelector(`[data-seat="${seatNumber}"]`);
            seat.classList.remove('available');
            seat.classList.add('selected');
            
            selectedSeat = seatNumber;
            document.getElementById('selectedSeat').value = seatNumber;
            document.getElementById('selectedSeatDisplay').value = seatNumber;
            document.getElementById('purchaseBtn').disabled = false;
        }
        
        document.getElementById('purchaseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedSeat) {
                alert('Lütfen bir koltuk seçin!');
                return;
            }
            
            const formData = new FormData();
            formData.append('route_id', document.getElementById('routeId').value);
            formData.append('seat_number', selectedSeat);
            formData.append('coupon_code', document.getElementById('couponCode').value);
            
            fetch('purchase_ticket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Bilet başarıyla satın alındı!');
                    window.location.href = 'user/dashboard.php';
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Bir hata oluştu!');
                console.error('Error:', error);
            });
        });
        
        // Coupon code validation
        document.getElementById('couponCode').addEventListener('blur', function() {
            const couponCode = this.value;
            if (couponCode) {
                fetch('validate_coupon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'coupon_code=' + encodeURIComponent(couponCode) + '&price=' + document.getElementById('price').value
                })
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        document.getElementById('totalPrice').value = data.final_price + ' ₺';
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                });
            } else {
                document.getElementById('totalPrice').value = document.getElementById('price').value + ' ₺';
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    </script>
</body>
</html>
