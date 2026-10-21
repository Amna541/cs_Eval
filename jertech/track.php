<?php
// Database connection
$host = "localhost";
$dbname = "track_db";  
$username = "root";  
$password = "";  

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Function to decrypt data
function decryptData($data) {
    $key = 'secretkey';
    $iv = '1234567891011121';
    return openssl_decrypt(hex2bin($data), 'AES-128-CTR', $key, 0, $iv);
}

// Get tracking data
$data = isset($_GET['data']) ? $_GET['data'] : '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Capture device and IP information
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Extract OS and device details
function getDeviceInfo($userAgent) {
    if (strpos($userAgent, 'Windows') !== false) return ['Windows', 'PC'];
    if (strpos($userAgent, 'Macintosh') !== false) return ['MacOS', 'PC'];
    if (strpos($userAgent, 'iPhone') !== false) return ['iOS', 'Mobile'];
    if (strpos($userAgent, 'Android') !== false) return ['Android', 'Mobile'];
    return ['Unknown', 'Unknown'];
}

list($os, $device) = getDeviceInfo($userAgent);

// Extract browser details
function getBrowserInfo($userAgent) {
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'Edge') !== false) return 'Edge';
    return 'Unknown';
}

$browser = getBrowserInfo($userAgent);

// Decrypt email/phone
$emailPhone = decryptData($data);

// Insert into database
if ($emailPhone) {
    $stmt = $conn->prepare("INSERT INTO tracking_data (email_phone, link_clicked, os, device, browser, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $emailPhone, $redirect, $os, $device, $browser, $ipAddress);
    $stmt->execute();
    $stmt->close();
}

// Redirect to the original page
header("Location: $redirect");
exit();
?>
