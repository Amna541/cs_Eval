<?php
// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "easypaisa"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function getLocationByIP($ip) {
    $access_key = 'YOUR_API_KEY';
    $api_url = "http://ipinfo.io/{$ip}/json?token={$access_key}";

    $response = file_get_contents($api_url);
    $data = json_decode($response, true);

    if ($data && isset($data['city']) && isset($data['region']) && isset($data['country'])) {
        return $data['city'] . ", " . $data['region'] . ", " . $data['country'];
    }
    return "Unknown Location";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $cnic = trim($_POST['cnic']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $ip_address = getUserIP();
    $location = getLocationByIP($ip_address);

    if (empty($username) || empty($_POST['password']) || empty($cnic) || empty($phone)) {
        $error_message = "All fields except email are mandatory.";
    } else if (!preg_match('/^\d{13}$/', $cnic)) {
        $error_message = "CNIC must be exactly 13 numeric digits.";
    } else if (!preg_match('/^\d{11}$/', $phone)) {
        $error_message = "Phone number must be exactly 11 numeric digits.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, cnic, email, phone_number, location, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $password, $cnic, $email, $phone, $location, $ip_address);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: special_offer_popup.html"); // Redirect to special offer page
            exit();
        } else {
            echo "Error: " . $stmt->error;
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
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #8dc63f, #006400); height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .container { background: #fff; padding: 50px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); width: 400px; text-align: center; }
        h3 { color: #8dc63f; font-size: 28px; font-weight: 600; margin-bottom: 25px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
        .input-group input { width: 100%; padding: 14px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; transition: all 0.3s ease; }
        .input-group input:focus { border-color: #8dc63f; box-shadow: 0 0 8px rgba(141, 198, 63, 0.5); }
        .btn { background-color: #8dc63f; color: #fff; padding: 14px; border: none; border-radius: 8px; width: 100%; cursor: pointer; font-size: 18px; font-weight: 600; transition: all 0.3s ease; }
        .btn:hover { background-color: #6fa52e; }
        .error { color: red; margin-bottom: 15px; }
        .footer { margin-top: 20px; font-size: 14px; }
        .footer a { color: #8dc63f; text-decoration: none; font-weight: 600; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h3>Create Your Account</h3>
    <?php if (!empty($error_message)): ?>
        <div class="error"><?= $error_message; ?></div>
    <?php endif; ?>
    <form action="signup.php" method="post">
        <div class="input-group">
            <label>Username*</label>
            <input type="text" name="username" required placeholder="Enter your username">
        </div>
        <div class="input-group">
            <label>Password*</label>
            <input type="password" name="password" required placeholder="Enter your password">
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
        <button type="submit" class="btn">Sign Up</button>
    </form>
    <div class="footer">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>
