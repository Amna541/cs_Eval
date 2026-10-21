<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'admin_dashboard';
    private $username = 'dashboard_admin';
    private $password = 'SecurePassword123!';
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            // Test connection
            $this->conn->query("SELECT 1");
            
        } catch(PDOException $e) {
            // More detailed error message
            $error_msg = "Database Connection Failed:\n";
            $error_msg .= "Error: " . $e->getMessage() . "\n";
            $error_msg .= "Trying to connect to: mysql:host={$this->host};dbname={$this->db_name}\n";
            $error_msg .= "Username: {$this->username}";
            
            error_log($error_msg);
            die("Database connection failed. Please check your configuration.");
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function createTables() {
        try {
            // Users table
            $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");

            // User settings table
            $this->conn->exec("CREATE TABLE IF NOT EXISTS user_settings (
                user_id INT PRIMARY KEY,
                timezone VARCHAR(50) DEFAULT 'UTC',
                date_format VARCHAR(20) DEFAULT 'Y-m-d',
                records_per_page INT DEFAULT 25,
                email_notifications BOOLEAN DEFAULT TRUE,
                browser_notifications BOOLEAN DEFAULT TRUE,
                two_factor_auth BOOLEAN DEFAULT FALSE,
                data_collection BOOLEAN DEFAULT TRUE,
                api_key VARCHAR(64),
                theme ENUM('light', 'dark') DEFAULT 'light',
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");

            // Create admin user if not exists
            $this->createAdminUser();

            return true;
        } catch(PDOException $e) {
            error_log("Table creation failed: " . $e->getMessage());
            return false;
        }
    }

    private function createAdminUser() {
        $check = $this->conn->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
        
        if ($check->rowCount() === 0) {
            $hashedPassword = password_hash('Admin@123', PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare("
                INSERT INTO users (username, email, password, role, is_active)
                VALUES ('admin', 'admin@example.com', ?, 'admin', TRUE)
            ");
            $stmt->execute([$hashedPassword]);
            
            $adminId = $this->conn->lastInsertId();
            
            // Initialize admin settings
            $this->conn->exec("
                INSERT INTO user_settings (user_id)
                VALUES ($adminId)
            ");
        }
    }
}

// Usage:
$database = new Database();
$conn = $database->getConnection();

// Create tables if they don't exist
if (!$database->createTables()) {
    die("Failed to initialize database tables.");
}
?>