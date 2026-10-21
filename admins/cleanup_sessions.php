<?php
include 'db_connect.php';

// Remove sessions older than 5 minutes
$conn->query("DELETE FROM active_browsing_sessions 
              WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
?>