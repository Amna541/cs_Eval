<?php
// track_click.php

// Capture the incoming POST data
$data = json_decode(file_get_contents('php://input'), true);

// Extract the details
$phone = $data['phone'];
$userAgent = $data['userAgent'];
$device = $data['device'];
$os = $data['os'];
$clickedAt = $data['clickedAt'];
$linkType = $data['linkType'];

// Store in database
$servername = "localhost"; // Your server
$username = "username"; // Your database username
$password = "password"; // Your database password
$dbname = "link_tracking_db"; // Your chosen database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert into the database
$stmt = $conn->prepare("INSERT INTO link_clicks (phone, user_agent, device, os, clicked_at, link_type) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $phone, $userAgent, $device, $os, $clickedAt, $linkType);

// Execute the query
$stmt->execute();

// Close the connection
$stmt->close();
$conn->close();
?>
