<?php
// Database connection
$servername = "localhost"; // Change if using a remote database
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "csd_registration"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $cnic = $_POST['cnic'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $address = $_POST['address'];

    // Validate passwords
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match');</script>";
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, phone, cnic, email, password, state, city, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $first_name, $last_name, $phone, $cnic, $email, $password, $state, $city, $address);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='https://www.csd.gov.pk/';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="icon" href="carts.png" type="image/png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Add CSD Logo -->
    <div class="logo-container">
        <img src="csd.png" alt="CSD Logo" class="csd-logo">
    </div>
    <div class="container">
        <h2>Registration</h2>
        <div class="form-scrollable">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <div class="input-container">
                        <i class="fas fa-user"></i>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter First Name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <div class="input-container">
                        <i class="fas fa-user"></i>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter Last Name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <div class="input-container">
                        <i class="fas fa-phone"></i>
                        <input type="text" id="phone" name="phone" placeholder="Enter Phone Number" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cnic">CNIC (without dashes):</label>
                    <div class="input-container">
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="cnic" name="cnic" placeholder="Enter CNIC" required>
                    </div>
                </div>

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
                    <label for="confirm_password">Confirm Password:</label>
                    <div class="input-container">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="state">State:</label>
                    <div class="input-container">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="state" name="state" placeholder="Enter State" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="city">City:</label>
                    <div class="input-container">
                        <i class="fas fa-city"></i>
                        <input type="text" id="city" name="city" placeholder="Enter City" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <div class="input-container">
                        <i class="fas fa-address-card"></i>
                        <textarea id="address" name="address" placeholder="Enter Address" required></textarea>
                    </div>
                </div>

                <button type="submit" class="btn">Register</button>
            </form>
        </div>
        
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>

<!-- Add CSS to style the page -->
<style>
 body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    background-color: #f5f5f5; /* Solid light gray background */
    background-image: url('csd1.png'); /* Replace 'background.jpg' with your image URL */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    height: 100vh; /* Full viewport height */
}

/* Logo Container */
.logo-container {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 10;
}

.csd-logo {
    width: 150px;
    animation: fadeIn 2s ease-out; /* Fade-in animation */
}

/* Container Styling */
.container {
    width: 450px;
    margin: 50px auto;
    padding: 30px;
    background-color: rgba(255, 255, 255, 0.9); /* Slight transparency to make text readable */
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 2px solid #e1e1e1;
    animation: slideUp 1s ease-out;
}

h2 {
    text-align: center;
    color: #e60000;
    margin-bottom: 20px;
}

.form-scrollable {
    max-height: 400px; /* Set a fixed height for the scrollable container */
    overflow-y: scroll; /* Enable vertical scrolling */
    padding-right: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: bold;
    display: block;
    margin-bottom: 8px;
    color: #333;
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
    font-size: 18px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 10px 12px 40px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 16px;
    box-sizing: border-box;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #e60000;
    box-shadow: 0 0 5px rgba(230, 0, 0, 0.5);
}

.form-group textarea {
    resize: vertical;
    height: 120px;
}

button {
    width: 100%;
    padding: 12px;
    background-color: #e60000;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    box-sizing: border-box;
    transition: all 0.3s ease;
}

button:hover {
    background-color: #cc0000;
    transform: scale(1.05);
}

p {
    text-align: center;
    margin-top: 20px;
    color: #333;
}

a {
    text-decoration: none;
    color: #e60000;
    font-weight: bold;
    transition: color 0.3s ease;
}

a:hover {
    color: #cc0000;
}

/* Fade-in Animation for Logo */
@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(-30px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Slide-up Animation for Container */
@keyframes slideUp {
    0% {
        opacity: 0;
        transform: translateY(30px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
