<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'group_db');

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

function decryptData($data) {
    return base64_decode(urldecode($data));
}

// Capture Contact and Group ID
$contact = isset($_GET['data']) ? decryptData($_GET['data']) : null;
$tracking_link = $_SERVER['HTTP_REFERER'] ?? 'Unknown'; // Get the actual link clicked
$group_id = $_SESSION['group_id'] ?? null;

// Debugging Output
if (!$contact) {
    die("Error: No contact data received.");
}
if (!$group_id) {
    die("Error: No group ID found in session.");
}

// Capture User Details
$browser = $_SERVER['HTTP_USER_AGENT'];
$os = (stripos($browser, 'Windows') !== false) ? 'Windows' : ((stripos($browser, 'Mac') !== false) ? 'Mac' : 'Other');
$device = (stripos($browser, 'Mobile') !== false) ? 'Mobile' : 'Desktop';
$ip_address = $_SERVER['REMOTE_ADDR'];
$timestamp = date('Y-m-d H:i:s');

// Insert into Database
$stmt = $conn->prepare("INSERT INTO tracking_data (contact, tracking_link, os, device, browser, ip_address, timestamp, group_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("sssssssi", $contact, $tracking_link, $os, $device, $browser, $ip_address, $timestamp, $group_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

// Redirect User to Original Link
header("Location: $tracking_link");
exit();
?>
