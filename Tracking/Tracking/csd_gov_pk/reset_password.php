<?php
session_start();
require 'config.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['password'];
    $email = $_SESSION['email'];

    // Update password in database
    $sql = "UPDATE users SET password='$new_password' WHERE email='$email'";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Password Reset Successful! Login now.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Error updating password');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="container">
        <h2>Reset Password</h2>
        <form method="POST">
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter new password" required>
            </div>
            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>

</body>
</html>
