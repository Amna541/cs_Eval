<?php
// Database connection
$host = "localhost"; 
$username = "root";  
$password = "";      
$database = "tracking_db"; 

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get real IP address
function getUserIP() {
  $ip = '';

  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
      $ip = $_SERVER['REMOTE_ADDR'];
  }

  // Convert "::1" (IPv6 localhost) to "127.0.0.1" (IPv4)
  if ($ip == '::1') {
      $ip = '127.0.0.1';
  }

  return $ip;
}


// Function to detect OS
function getOS() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $os_array = [
        'Windows' => 'Windows',
        'Mac' => 'Mac OS',
        'Linux' => 'Linux',
        'Ubuntu' => 'Ubuntu',
        'iPhone' => 'iOS',
        'iPad' => 'iOS',
        'Android' => 'Android'
    ];
    foreach ($os_array as $key => $value) {
        if (stripos($user_agent, $key) !== false) {
            return $value;
        }
    }
    return "Unknown";
}

// Function to detect browser
function getBrowser() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $browser_array = [
        'Edge' => 'Edge',
        'Chrome' => 'Chrome',
        'Firefox' => 'Firefox',
        'Safari' => 'Safari',
        'Opera' => 'Opera',
        'MSIE' => 'Internet Explorer'
    ];
    foreach ($browser_array as $key => $value) {
        if (stripos($user_agent, $key) !== false) {
            return $value;
        }
    }
    return "Unknown";
}

// Function to detect device type
function getDevice() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/Mobile|Android|iPhone|iPad/i', $user_agent)) {
        return "Mobile";
    } else {
        return "PC";
    }
}

// Get user details
$ip_address = getUserIP();
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$device = getDevice();
$browser = getBrowser();
$os = getOS();

// Get phone number from URL
if (isset($_GET['phone'])) {
  $phone = trim($_GET['phone']); // Trim spaces to avoid issues

  // Store information in database
  $stmt = $conn->prepare("INSERT INTO tracking (phone, ip_address, user_agent, device, browser, os) 
                          VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssss", $phone, $ip_address, $user_agent, $device, $browser, $os);
  
  if ($stmt->execute()) {
      echo "Tracking information stored successfully!";
  } else {
      echo "Error: " . $stmt->error;
  }

  $stmt->close();
}


$conn->close();
?>
