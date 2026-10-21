<?php
include 'db_connect.php';

// Create users table first
$createUsersTable = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Then create notifications table
$createNotificationsTable = "
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('alert', 'update', 'info') NOT NULL DEFAULT 'info',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";



// Create user_settings table
$createSettingsTable = "
CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT PRIMARY KEY,
    timezone VARCHAR(50) DEFAULT 'UTC',
    date_format VARCHAR(20) DEFAULT 'Y-m-d',
    records_per_page INT DEFAULT 25,
    email_notifications TINYINT(1) DEFAULT 1,
    browser_notifications TINYINT(1) DEFAULT 1,
    alert_notifications TINYINT(1) DEFAULT 1,
    update_notifications TINYINT(1) DEFAULT 1,
    report_notifications TINYINT(1) DEFAULT 1,
    notification_frequency ENUM('instant', 'hourly', 'daily') DEFAULT 'instant',
    two_factor_auth TINYINT(1) DEFAULT 0,
    ga_integration TINYINT(1) DEFAULT 0,
    slack_integration TINYINT(1) DEFAULT 0,
    mailchimp_integration TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    // Execute in the correct order
    $conn->query($createUsersTable);
    $conn->query($createNotificationsTable);
    $conn->query($createSettingsTable);
    
    // Add at least one admin user if users table is empty
    $checkUsers = $conn->query("SELECT COUNT(*) as count FROM users");
    $userCount = $checkUsers->fetch_assoc()['count'];
    
    if ($userCount == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (username, email, password_hash) VALUES ('admin', 'admin@example.com', '$hashedPassword')");
        
        // Get the inserted user ID
        $adminId = $conn->insert_id;
        
        // Create default settings for admin
        $conn->query("INSERT INTO user_settings (user_id) VALUES ($adminId)");
    }
    
    echo "Tables created successfully!<br>";
    echo "Default admin user created with username: admin, password: admin123";
} catch (mysqli_sql_exception $e) {
    die("Error creating tables: " . $e->getMessage());
}