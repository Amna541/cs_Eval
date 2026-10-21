<?php
header("Content-Type: text/plain"); // Response type for debugging

// ✅ Database Connection
$conn = new mysqli("localhost", "root", "", "admin_db");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// ✅ Check for POST Data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $group = isset($_POST["group"]) ? $_POST["group"] : "No group";
    $data = isset($_POST["data"]) ? $_POST["data"] : "No data";

    // Extract user info
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // ✅ Insert into Database
    $stmt = $conn->prepare("INSERT INTO tracking_ (group_id, data, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $group, $data, $ip_address, $user_agent);

    if ($stmt->execute()) {
        echo "Tracking data stored successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request!";
}

$conn->close();
?>
