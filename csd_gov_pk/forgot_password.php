<?php
session_start();
require 'config.php'; // Database connection file
require 'mailer.php'; // File to send emails

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists in the database
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        // Send OTP via email
        sendOTP($email, $otp);

        echo "<script>alert('OTP sent to your email'); window.location.href='verify_otp.php';</script>";
    } else {
        echo "<script>alert('Email not found');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="container">
        <h2>Forgot Password</h2>
        <form method="POST">
            <div class="form-group">
                <label for="email">Enter Your Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn">Send OTP</button>
        </form>
    </div>

</body>
</html>

<style>
/* Style same as login page */
</style>
