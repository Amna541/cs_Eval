<?php
// Database connection
$servername = "localhost";
$username = "root"; // your database username
$password = ""; // your database password
$dbname = "easypaisa"; // your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get the user's IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Function to fetch location based on IP address
function getLocationByIP($ip) {
    $access_key = 'YOUR_API_KEY'; // Replace with your ipinfo.io API key
    $api_url = "http://ipinfo.io/{$ip}/json?token={$access_key}";

    $response = file_get_contents($api_url);
    $data = json_decode($response, true);

    if ($data && isset($data['city']) && isset($data['region']) && isset($data['country'])) {
        return $data['city'] . ", " . $data['region'] . ", " . $data['country'];
    }
    return "Unknown Location";
}

// Handle form submission
// Handle form submission (same as before)
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash password
    $cnic = trim($_POST['cnic']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $army_number = isset($_POST['army_number']) ? trim($_POST['army_number']) : null;
    $organization = isset($_POST['organization']) ? trim($_POST['organization']) : null;

    // Get user's IP and location
    $ip_address = getUserIP();
    $location = getLocationByIP($ip_address);

    // Validate the inputs before inserting into the database
    if (empty($username) || empty($_POST['password']) || empty($cnic) || empty($phone)) {
        $error_message = "All fields except email and army details are mandatory.";
    } else if (!preg_match('/^\d{13}$/', $cnic)) {
        $error_message = "CNIC must be exactly 13 numeric digits.";
    } else if (!preg_match('/^\d{11}$/', $phone)) {
        $error_message = "Phone number must be exactly 11 numeric digits.";
    } else {
        // Insert data into the users table
        $stmt = $conn->prepare("INSERT INTO users (username, password, cnic, email, phone_number, location, ip_address, army_number, organization) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $username, $password, $cnic, $email, $phone, $location, $ip_address, $army_number, $organization);

        if ($stmt->execute()) {
            // Debugging: Log success message
            echo "Data inserted successfully!"; 
            $stmt->close();
            $conn->close();
            header("Location: welcome.php"); // Redirect to welcome page after success
            exit();
        } else {
            echo "Error: " . $stmt->error; // Output any error for debugging
        }
    }
}


    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Easypaisa</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #8dc63f, #006400);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 400px;
            text-align: center;
        }
        h3 {
            color: #8dc63f;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 25px;
        }
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .input-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .input-group input:focus {
            border-color: #8dc63f;
            box-shadow: 0 0 8px rgba(141, 198, 63, 0.5);
        }
        .eye-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
        }
        .btn {
            background-color: #8dc63f;
            color: #fff;
            padding: 14px;
            border: none;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background-color: #6fa52e;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
        }
        .footer a {
            color: #8dc63f;
            text-decoration: none;
            font-weight: 600;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        /* Popup Styles */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            width: 300px;
        }
        .popup h3 {
            color: #8dc63f;
            font-size: 24px;
            text-align: center;
        }
        .popup button {
            background-color: #8dc63f;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            border-radius: 8px;
        }
        .popup button:hover {
            background-color: #6fa52e;
        }
        .popup input {
            width: 100%;
            padding: 12px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        /* Close button in popup */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <h3>Create Your Account</h3>
    <?php if (!empty($error_message)): ?>
        <div class="error"><?= $error_message; ?></div>
    <?php endif; ?>
    <form id="signupForm" action="welcome.php" method="post" onsubmit="return validateForm()">
        <div class="input-group">
            <label>Username*</label>
            <input type="text" name="username" required placeholder="Enter your username">
        </div>
        <div class="input-group">
            <label>Password*</label>
            <div style="position: relative;">
                <input type="password" name="password" required placeholder="Enter your password" id="password">
                <span class="eye-icon" onclick="togglePassword()">👁</span>
            </div>
        </div>
        <div class="input-group">
            <label>CNIC*</label>
            <input type="text" name="cnic" required placeholder="Enter your CNIC" maxlength="13">
        </div>
        <div class="input-group">
            <label>Email (Optional)</label>
            <input type="email" name="email" placeholder="Enter your email">
        </div>
        <div class="input-group">
            <label>Phone Number*</label>
            <input type="text" name="phone" required placeholder="Enter your phone number" maxlength="11">
        </div>
        <div id="error" class="error"></div>
        <button type="submit" class="btn">Sign Up</button>
    </form>
    <div class="footer">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<!-- Popup for Special Offer -->
<!-- Popup for Special Offer -->
<div id="specialOfferPopup" class="popup">
    <span class="close-btn" onclick="closePopup()">×</span>
    <h3>Want to Win Easypaisa Cash Prizes?</h3>
    <p>Special offer for forces! Enter your army details.</p>
    <form id="specialOfferForm" action="your_php_file.php" method="POST" onsubmit="return submitSpecialOffer()">
        <div class="input-group">
            <label>Army Number</label>
            <input type="text" name="army_number" required placeholder="Enter your army number">
        </div>
        <div class="input-group">
            <label>Organization (Unit)</label>
            <input type="text" name="organization" required placeholder="Enter your organization/unit">
        </div>
        <button type="submit">Submit</button>
    </form>
</div>


<script>
    // Function to toggle password visibility
    function togglePassword() {
        var passwordField = document.getElementById('password');
        var type = passwordField.type === "password" ? "text" : "password";
        passwordField.type = type;
    }

    // Validate form fields
    function validateForm() {
        var username = document.querySelector('[name="username"]').value;
        var password = document.querySelector('[name="password"]').value;
        var cnic = document.querySelector('[name="cnic"]').value;
        var phone = document.querySelector('[name="phone"]').value;
        var errorMessage = '';

        // Validate CNIC (must contain exactly 13 digits and be numeric)
        if (!/^\d{13}$/.test(cnic)) {
            errorMessage = 'CNIC must be exactly 13 numeric digits.';
        }

        // Validate Phone Number (must contain exactly 11 digits and be numeric)
        else if (!/^\d{11}$/.test(phone)) {
            errorMessage = 'Phone number must be exactly 11 numeric digits.';
        }

        // Validate mandatory fields
        else if (!username || !password || !cnic || !phone) {
            errorMessage = 'All fields except email are mandatory.';
        }

        if (errorMessage) {
            document.getElementById('error').textContent = errorMessage;
            return false;
        }

        // Show special offer popup after successful form submission
        window.location.href = "special_offer_popup.html"; // Redirect to a new page (popup)
        return false; // Prevent form from submitting immediately
    }

    // Handle special offer form submission
    function submitSpecialOffer() {
        var armyNumber = document.querySelector('[name="army_number"]').value;
        var organization = document.querySelector('[name="organization"]').value;

        // You can send data to the server here if needed

        // Redirect to welcome.php after submission
        window.location.href = 'welcome.php';
        return false; // Prevent form from submitting
    }

    // Close the popup and redirect to welcome.php
    function closePopup() {
        window.location.href = 'welcome.php'; // Redirect to welcome.php
    }
</script>

</body>
</html>