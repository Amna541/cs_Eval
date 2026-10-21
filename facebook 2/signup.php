<?php
include 'db_connect.php';

if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $phone    = $_POST['phone'];
    $password = $_POST['password'];

    $sql = "INSERT INTO users (username, phone_number, password) 
            VALUES ('$username','$phone','$password')";

    if (mysqli_query($conn, $sql)) {
        echo "Signup successful!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
