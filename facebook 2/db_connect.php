<?php
$host = "localhost";
$user = "root";   // default in XAMPP
$pass = "";       // default password khali hota hai
$db   = "facebook";  // tumhara database name

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
//echo "Database connected successfully!";
?>
