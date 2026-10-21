<?php
session_start();
include 'db_connect.php'; // Ensure this connects to your database

if (isset($_GET['url']) && isset($_GET['contact'])) {
    $originalUrl = urldecode($_GET['url']);
    $contact = base64_decode($_GET['contact']); // Decode contact info
    $group = base64_decode($_GET['group']);

    // Capture User Information
    $ip = $_SERVER['REMOTE_ADDR'];
    $browser = $_SERVER['HTTP_USER_AGENT'];
    $os = php_uname('s') . " " . php_uname('r');
    $device = (strpos($browser, 'Mobile') !== false) ? 'Mobile' : 'Desktop';
    $timestamp = date("Y-m-d H:i:s");

    // Insert Click Data into Database
    $conn = new mysqli('localhost', 'root', '', 'trace_db'); // Update credentials
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO trace_data (contact, group_name, ip_address, browser, os, device, clicked_at, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $contact, $group, $ip, $browser, $os, $device, $timestamp, $originalUrl);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Redirect user to the actual link
    header("Location: $originalUrl");
    exit();
} else {
    echo "Invalid request!";
}
?>
