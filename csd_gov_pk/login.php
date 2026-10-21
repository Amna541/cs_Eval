<?php
session_start();
$servername = "localhost"; // Change if needed
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "csd_registration"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user data from database
    $stmt = $conn->prepare("SELECT id, first_name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $first_name, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Store user details in session
            $_SESSION['user_id'] = $id;
            $_SESSION['first_name'] = $first_name;

            // Redirect to CSD website after successful login
            header("Location: https://www.csd.gov.pk/");
            exit();
        } else {
            echo "<script>alert('Invalid email or password');</script>";
        }
    } else {
        echo "<script>alert('User not found');</script>";
    }

    $stmt->close();
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <div class="input-container">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Enter Email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <div class="input-container">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter Password" required>
                </div>
            </div>

            <div class="form-group">
                <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <p>Don't have an account? <a href="csd_register.php">Create New Account</a></p>
    </div>

</body>
</html>

<style>
/* Background Styling */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: url('csd1.png') no-repeat center center fixed; /* Replace 'background.jpg' with your image */
    background-size: cover;
    
}

/* Login Container */
.container {
    width: 380px;
    padding: 30px;
    background-color: rgba(255, 255, 255, 0.9); /* Slight transparency */
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    text-align: center;
    animation: fadeIn 1s ease-out;
}

h2 {
    color: #e60000;
    margin-bottom: 20px;
}

/* Form Input Fields */
.form-group {
    margin-bottom: 15px;
    text-align: left;
    margin-right: 60px;
}

.form-group label {
    font-weight: bold;
    color: #333;
    display: block;
    margin-bottom: 5px;
}

.input-container {
    position: relative;
    width: 100%;
}

.input-container i {
    position: absolute;
    top: 12px;
    left: 12px;
    color: #e60000;
}

.form-group input {
    width: 100%;
    padding: 12px 10px 12px 40px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 16px;
}

.form-group input:focus {
    border-color: #e60000;
    outline: none;
    box-shadow: 0 0 5px rgba(230, 0, 0, 0.5);
}

/* Forgot Password */
.forgot-password {
    display: block;
    text-align: right;
    color: #e60000;
    text-decoration: none;
    font-size: 14px;
    margin-bottom: 15px;
    margin-left: 200px;
    
}

.forgot-password:hover {
    color: #cc0000;
}

/* Login Button */
.btn {
    width: 100%;
    padding: 12px;
    background-color: #e60000;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn:hover {
    background-color: #cc0000;
    transform: scale(1.05);
}

/* Create New Account */
p {
    margin-top: 15px;
    font-size: 14px;
}

p a {
    text-decoration: none;
    color: #e60000;
    font-weight: bold;
}

p a:hover {
    color: #cc0000;
}

/* Fade-in Animation */
@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
