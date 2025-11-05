<?php
class Database {
    private $db;
    
    public function __construct() {
        $this->db = new PDO('sqlite:database.sqlite');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTables();
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    private function createTables() {
        // Users table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                role VARCHAR(20) DEFAULT 'user',
                company_id INTEGER,
                credit DECIMAL(10,2) DEFAULT 0.00,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id)
            )
        ");
        
        // Companies table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS companies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                contact_info TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Routes table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS routes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                company_id INTEGER NOT NULL,
                departure_city VARCHAR(50) NOT NULL,
                arrival_city VARCHAR(50) NOT NULL,
                departure_date DATE NOT NULL,
                departure_time TIME NOT NULL,
                arrival_date DATE NOT NULL,
                arrival_time TIME NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                total_seats INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id)
            )
        ");
        
        // Tickets table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS tickets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                route_id INTEGER NOT NULL,
                seat_number INTEGER NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                discount_amount DECIMAL(10,2) DEFAULT 0.00,
                coupon_code VARCHAR(50),
                status VARCHAR(20) DEFAULT 'active',
                purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (route_id) REFERENCES routes(id)
            )
        ");
        
        // Coupons table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS coupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(50) UNIQUE NOT NULL,
                discount_type VARCHAR(20) NOT NULL,
                discount_value DECIMAL(10,2) NOT NULL,
                usage_limit INTEGER,
                used_count INTEGER DEFAULT 0,
                expiry_date DATE,
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default admin user
        $this->createDefaultAdmin();
        
        // Insert sample companies
        $this->createSampleData();
    }
    
    private function createDefaultAdmin() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, full_name, role, credit) 
                VALUES (?, ?, ?, ?, 'admin', 10000.00)
            ");
            $stmt->execute([
                'admin',
                'admin@bilet.com',
                password_hash('admin123', PASSWORD_DEFAULT),
                'Sistem Yöneticisi'
            ]);
        }
    }
    
    private function createSampleData() {
        // Insert sample companies
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM companies");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            $companies = [
                ['Metro Turizm', 'Güvenilir seyahat deneyimi', '0212 123 45 67'],
                ['Ulusoy', 'Konforlu yolculuk', '0212 234 56 78'],
                ['Kamil Koç', 'Kaliteli hizmet', '0212 345 67 89']
            ];
            
            $stmt = $this->db->prepare("
                INSERT INTO companies (name, description, contact_info) 
                VALUES (?, ?, ?)
            ");
            
            foreach ($companies as $company) {
                $stmt->execute($company);
            }
            
            // Create company admin users
            $companyAdmins = [
                ['metro_admin', 'metro@bilet.com', 'Metro Admin', 1],
                ['ulusoy_admin', 'ulusoy@bilet.com', 'Ulusoy Admin', 2],
                ['kamilkoc_admin', 'kamilkoc@bilet.com', 'Kamil Koç Admin', 3]
            ];
            
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, full_name, role, company_id, credit) 
                VALUES (?, ?, ?, ?, 'company_admin', ?, 5000.00)
            ");
            
            foreach ($companyAdmins as $admin) {
                $stmt->execute([
                    $admin[0],
                    $admin[1],
                    password_hash('admin123', PASSWORD_DEFAULT),
                    $admin[2],
                    $admin[3]
                ]);
            }
            
            // Insert sample routes
            $routes = [
                [1, 'İstanbul', 'Ankara', '2025-11-01', '08:00', '2025-11-01', '14:00', 150.00, 50],
                [1, 'İstanbul', 'İzmir', '2025-11-01', '10:00', '2025-11-01', '18:00', 200.00, 50],
                [2, 'Ankara', 'İstanbul', '2025-11-01', '09:00', '2025-11-01', '15:00', 160.00, 45],
                [2, 'Ankara', 'Antalya', '2025-11-01', '11:00', '2025-11-01', '20:00', 180.00, 45],
                [3, 'İzmir', 'İstanbul', '2025-11-01', '07:00', '2025-11-01', '15:00', 190.00, 40],
                [3, 'İzmir', 'Ankara', '2025-11-01', '13:00', '2025-11-01', '22:00', 170.00, 40]
            ];
            
            $stmt = $this->db->prepare("
                INSERT INTO routes (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_date, arrival_time, price, total_seats) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($routes as $route) {
                $stmt->execute($route);
            }
            
            // Insert sample coupons
            $coupons = [
                ['WELCOME10', 'percentage', 10.00, 100, '2025-12-31'],
                ['SAVE50', 'fixed', 50.00, 50, '2025-12-31'],
                ['STUDENT20', 'percentage', 20.00, 200, '2025-12-31']
            ];
            
            $stmt = $this->db->prepare("
                INSERT INTO coupons (code, discount_type, discount_value, usage_limit, expiry_date) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($coupons as $coupon) {
                $stmt->execute($coupon);
            }
        }
    }
}

// Initialize database
$database = new Database();
$db = $database->getConnection();
?>
