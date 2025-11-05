<?php
// Test database connection and setup
require_once 'config/database.php';

echo "<h2>Database Test</h2>";

try {
    // Test connection
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tables created:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Test data
    echo "<h3>Sample Data:</h3>";
    
    // Users
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "<p>Users: $userCount</p>";
    
    // Companies
    $stmt = $db->query("SELECT COUNT(*) FROM companies");
    $companyCount = $stmt->fetchColumn();
    echo "<p>Companies: $companyCount</p>";
    
    // Routes
    $stmt = $db->query("SELECT COUNT(*) FROM routes");
    $routeCount = $stmt->fetchColumn();
    echo "<p>Routes: $routeCount</p>";
    
    // Coupons
    $stmt = $db->query("SELECT COUNT(*) FROM coupons");
    $couponCount = $stmt->fetchColumn();
    echo "<p>Coupons: $couponCount</p>";
    
    echo "<h3>Test Accounts:</h3>";
    $stmt = $db->query("SELECT username, role FROM users ORDER BY role");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Username</th><th>Role</th><th>Password</th></tr>";
    foreach ($users as $user) {
        $password = ($user['role'] === 'admin' || $user['role'] === 'company_admin') ? 'admin123' : 'Register to create';
        echo "<tr><td>{$user['username']}</td><td>{$user['role']}</td><td>$password</td></tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Database setup successful!</h3>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Database error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
