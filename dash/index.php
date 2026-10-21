<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "admin_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getOS($user_agent) {
    $os_array = [
        'Windows' => 'Windows NT',
        'Mac' => 'Macintosh',
        'iOS' => 'iPhone',
        'Android' => 'Android',
        'Linux' => 'Linux'
    ];
    foreach ($os_array as $os => $match) {
        if (strpos($user_agent, $match) !== false) return $os;
    }
    return "Unknown";
}

function getBrowser($user_agent) {
    $browser_array = [
        'Chrome' => 'Chrome',
        'Firefox' => 'Firefox',
        'Edge' => 'Edg',
        'Safari' => 'Safari',
        'Opera' => 'Opera',
        'IE' => 'MSIE'
    ];
    foreach ($browser_array as $browser => $match) {
        if (strpos($user_agent, $match) !== false) return $browser;
    }
    return "Unknown";
}

if (isset($_GET['data']) && isset($_GET['group'])) {
    $decryptedData = openssl_decrypt(urldecode($_GET['data']), 'AES-128-ECB', 'secret_key');
    $groupNumber = $_GET['group'];
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $os = getOS($user_agent);
    $browser = getBrowser($user_agent);
    $device = (strpos($user_agent, "Mobile") !== false) ? "Mobile" : "Desktop";
    $timestamp = date("Y-m-d H:i:s");
    
    $stmt = $conn->prepare("INSERT INTO tracking_clicks (group_id, email_or_phone, os, browser, device, ip_address, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $groupNumber, $decryptedData, $os, $browser, $device, $ip_address, $timestamp);
    $stmt->execute();
    $stmt->close();
    
    header("Location: easypaisa.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <style>
        body { background: linear-gradient(to right, #141e30, #243b55); color: white; }
        .container { margin-top: 50px; }
        .card { background: rgba(255, 255, 255, 0.1); padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        .btn-custom { background: #00c6ff; color: white; font-weight: bold; padding: 10px; }
    </style>
    <script>
        function generateTrackingLink(inputId, displayId) {
            let value = document.getElementById(inputId).value.trim();
            let groupNumber = document.getElementById('groupNumber').value.trim();
            if (value === '' || groupNumber === '') {
                alert('Please enter all required fields.');
                return;
            }
            let encryptedValue = CryptoJS.AES.encrypt(value, 'secret_key').toString();
            let encodedValue = encodeURIComponent(encryptedValue);
            let trackingLink = `index.php?data=${encodedValue}&group=${encodeURIComponent(groupNumber)}`;
            let linkElement = document.getElementById(displayId);
            linkElement.innerHTML = `<strong>Generated Link:</strong> <a href="${trackingLink}" target="_blank">${trackingLink}</a>`;
        }
    </script>
</head>
<body>
    <div class="container text-center">
        <h2>Dashboard</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <h4>Enter Group Number</h4>
                    <input type="text" id="groupNumber" class="form-control mb-3" placeholder="Enter Group Number">
                    <button class="btn btn-custom w-100">Submit</button>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-md-4">
                <div class="card">
                    <h4>Enter Email</h4>
                    <input type="email" id="emailInput" class="form-control mb-2" placeholder="Enter Email">
                    <button class="btn btn-custom w-100" onclick="generateTrackingLink('emailInput', 'emailLink')">Submit</button>
                    <div id="emailLink" class="tracking-link"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h4>Enter Phone</h4>
                    <input type="tel" id="phoneInput" class="form-control mb-2" placeholder="Enter Phone Number">
                    <button class="btn btn-custom w-100" onclick="generateTrackingLink('phoneInput', 'phoneLink')">Submit</button>
                    <div id="phoneLink" class="tracking-link"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
