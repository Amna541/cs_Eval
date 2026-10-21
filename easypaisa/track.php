<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tracking_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the incoming JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Extract the data
$ip_address = $conn->real_escape_string($data['ip_address']);
$phone = $conn->real_escape_string($data['phone']);
$email = $conn->real_escape_string($data['email']);
$browser = $conn->real_escape_string($data['browser']);
$os = $conn->real_escape_string($data['os']);
$device = $conn->real_escape_string($data['device']);
$user_agent = $conn->real_escape_string($data['user_agent']);
$clicked_at = $conn->real_escape_string($data['clicked_at']);

// SQL query to insert data into tracking_data table
$sql = "INSERT INTO tracking (ip_address, phone, email, browser, os, device, user_agent, clicked_at)
        VALUES ('$ip_address', '$phone', '$email', '$browser', '$os', '$device', '$user_agent', '$clicked_at')";

// Execute the query and check if the data is inserted successfully
if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

// Close the database connection
$conn->close();
?>
