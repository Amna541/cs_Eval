<?php
$host = "localhost";
$dbname = "track_db";  
$username = "root";  
$password = "";  

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$query = "SELECT 
            SUM(CASE WHEN link_clicked='easypaisa' THEN 1 ELSE 0 END) AS easypaisa,
            SUM(CASE WHEN link_clicked='askari' THEN 1 ELSE 0 END) AS askari,
            SUM(CASE WHEN link_clicked='csd' THEN 1 ELSE 0 END) AS csd
          FROM `tracking_data`";

$result = $conn->query($query);
$data = $result->fetch_assoc();

$conn->close();

// Return the click data as JSON
echo json_encode($data);
?>
