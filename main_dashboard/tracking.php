<?php
session_start();
include 'db_connect.php'; // Ensure this file contains DB connection details

// Get data from URL parameters
$group = isset($_GET['group']) ? $_GET['group'] : 'Unknown';
$contact = isset($_GET['contact']) ? base64_decode($_GET['contact']) : 'Unknown';
$url = isset($_GET['url']) ? $_GET['url'] : '';

if (empty($url)) {
    die("Invalid URL");
}

// Capture user details
$ip = $_SERVER['REMOTE_ADDR'];
$browser = $_SERVER['HTTP_USER_AGENT'];
$os = php_uname('s') . " " . php_uname('r');
$device = (strpos($browser, 'Mobile') !== false) ? 'Mobile' : 'Desktop';
$timestamp = date("Y-m-d H:i:s");

// Store click data in the database
$conn = new mysqli('localhost', 'root', '', 'trace_db'); // Update with your database credentials

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("INSERT INTO trace_data (group_name, contact, ip_address, browser, os, device, clicked_at, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $group, $contact, $ip, $browser, $os, $device, $timestamp, $url);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    // Redirect user to the original URL
    header("Location: " . $url);
    exit();
} else {
    echo "Error logging click.";
}

?>
