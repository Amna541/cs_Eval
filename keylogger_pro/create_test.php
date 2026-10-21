<?php
// create_test.php - Create test victim
require_once 'config.php';

$test_session = 'test_' . time() . '_' . rand(1000, 9999);

$conn->query("INSERT INTO victims (session_id, ip_address, user_agent, last_active) 
              VALUES ('$test_session', '192.168.1.100', 'Test Browser', NOW())");

echo "Test victim created: <a href='view.php?session=$test_session'>View</a>";
?>