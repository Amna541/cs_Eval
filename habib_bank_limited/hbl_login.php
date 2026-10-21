<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hbl_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = md5($_POST['password']);

$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $_SESSION['user'] = $username;
    header("Location: hbl_dashboard.php");
} else {
    echo "<script>alert('Invalid username or password'); window.location.href='hbl_i.php';</script>";
}

$conn->close();
?>
