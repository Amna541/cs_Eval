<?php
$conn = new mysqli("localhost", "root", "", "hbl_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = md5($_POST['password']);
$cnic = $_POST['cnic'];
$cnic_image = $_FILES['cnic_image'];

$target_dir = "uploads/";
$target_file = $target_dir . basename($cnic_image["name"]);
move_uploaded_file($cnic_image["tmp_name"], $target_file);

$sql = "INSERT INTO user (username, password, cnic, cnic_image) VALUES ('$username', '$password', '$cnic', '$target_file')";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Signup Successful!'); window.location.href='index.php';</script>";
} else {
    echo "<script>alert('Error: " . $conn->error . "'); window.location.href='signup.php';</script>";
}

$conn->close();
?>
