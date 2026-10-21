<?php
// Direct connection to MySQL Database using MySQLi
$host = 'localhost';       // Host
$user = 'root';            // Username
$password = '';            // Password (for local development, it's usually empty)
$dbname = 'track_db';      // Database name

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the count of clicks for each link
$sql = "SELECT link_name, COUNT(*) AS clicks FROM tracking_data GROUP BY link_name";
$result = $conn->query($sql);

$click_data = [];
while ($row = $result->fetch_assoc()) {
    $click_data[] = $row;
}

// Return data as JSON
echo json_encode($click_data);

$conn->close(); // Close the connection
?>
