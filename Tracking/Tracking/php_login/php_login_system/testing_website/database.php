<?php
$host = "localhost";  // Change this if necessary
$user = "root";       // Change this to your database username
$pass = "";           // Change this to your database password
$dbname = "easypaisa"; // Change this to your database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
