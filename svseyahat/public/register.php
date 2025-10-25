<?php
require_once __DIR__ . '/../src/config.php';
$db = get_db();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $u = $_POST['username']; $p = $_POST['password'];
    $hash = password_hash($p, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (username,password,role,credit) VALUES (?, ?, "user", 0)');
    try{ $stmt->execute([$u,$hash]); flash('Kayıt başarılı'); header('Location: /login.php'); exit; }catch(Exception $e){ flash('Hata: '. $e->getMessage()); }
}
?>
<!doctype html><html><body>
<h1>Kayıt</h1>
<?php if($m=flash()) echo '<p>'.$m.'</p>';?>
<form method="post">
<input name="username"> <input name="password" type="password"> <button>Kayıt</button>
</form>
</body></html>