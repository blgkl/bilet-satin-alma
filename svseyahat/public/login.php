<?php
require_once __DIR__ . '/../src/config.php';
$db = get_db();
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user = $_POST['username']; $pass = $_POST['password'];
    $stmt = $db->prepare('SELECT id, password FROM users WHERE username = ?');
    $stmt->execute([$user]); $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if($u && password_verify($pass, $u['password'])){
        $_SESSION['user_id'] = $u['id'];
        header('Location: /index.php'); exit;
    }else{ flash('Giriş başarısız'); }
}
?>
<!doctype html><html><body>
<h1>Giriş</h1>
<?php if($m = flash()) echo '<p>'.$m.'</p>'; ?>
<form method="post">
<input name="username" placeholder="Kullanıcı"> <input name="password" type="password" placeholder="Şifre"> <button>Giriş</button>
</form>
</body></html>