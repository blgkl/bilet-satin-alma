<?php
require_once __DIR__ . '/../src/config.php';
require_role('admin');
$db = get_db();
// admin panel: firm ekle, kupon ekle örnek
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['new_firm'])){
    $db->prepare('INSERT INTO firms (name) VALUES (?)')->execute([$_POST['firm_name']]); flash('Firma eklendi'); header('Location: /admin_panel.php'); exit;
}
$firms = $db->query('SELECT * FROM firms')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html><body>
<h1>Admin Panel</h1>
<form method="post"><input name="firm_name"><button name="new_firm">Firma Ekle</button></form>
<ul><?php foreach($firms as $f) echo "<li>".htmlspecialchars($f['name'])."</li>"; ?></ul>
</body></html>