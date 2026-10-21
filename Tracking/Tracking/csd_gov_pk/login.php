<?php
date_default_timezone_set('Asia/Karachi');
session_start();

// Database connection for tracking data (trace_db)
$trace_servername = "localhost";
$trace_username = "root";
$trace_password = "";
$trace_dbname = "trace_db";

$trace_conn = new mysqli($trace_servername, $trace_username, $trace_password, $trace_dbname);

// Check connection
if ($trace_conn->connect_error) {
    die("Trace DB Connection failed: " . $trace_conn->connect_error);
}

// Database connection for login data (csd_registration)
$csd_servername = "localhost";
$csd_username = "root";
$csd_password = "";
$csd_dbname = "csd_registration";

$csd_conn = new mysqli($csd_servername, $csd_username, $csd_password, $csd_dbname);

// Check connection
if ($csd_conn->connect_error) {
    die("CSD DB Connection failed: " . $csd_conn->connect_error);
}

// Include config file if it exists
@include_once 'F:\xampp\htdocs\config.php';

// Function definitions
function encodeNumber($number) {
    return base64_encode($number);
}

function decodeNumber($encoded) {
    return base64_decode($encoded);
}

function getOS($userAgent) {
    $osArray = [
        '/windows nt 10/i'     => 'Windows 10',
        '/windows nt 6.3/i'    => 'Windows 8.1',
        '/windows nt 6.2/i'    => 'Windows 8',
        '/windows nt 6.1/i'    => 'Windows 7',
        '/macintosh|mac os x/i'=> 'Mac OS X',
        '/linux/i'             => 'Linux',
        '/iphone/i'            => 'iPhone',
        '/ipad/i'              => 'iPad',
        '/android/i'           => 'Android',
    ];
    foreach ($osArray as $regex => $value) {
        if (preg_match($regex, $userAgent)) {
            return $value;
        }
    }
    return 'Unknown OS';
}

function getBrowser($userAgent) {
    $browserArray = [
        '/msie/i'      => 'Internet Explorer',
        '/firefox/i'   => 'Firefox',
        '/safari/i'    => 'Safari',
        '/chrome/i'    => 'Chrome',
        '/edge/i'      => 'Edge',
        '/opera/i'     => 'Opera',
        '/netscape/i'  => 'Netscape',
        '/maxthon/i'   => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i'    => 'Mobile Browser'
    ];
    foreach ($browserArray as $regex => $value) {
        if (preg_match($regex, $userAgent)) {
            return $value;
        }
    }
    return 'Unknown Browser';
}

function isMobile($userAgent) {
    return preg_match('/mobile|android|iphone|ipad/i', $userAgent) ? "YES" : "NO";
}

function getRealIP() {
    // Check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validateIP($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // Check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Check if multiple IP addresses exist in var
        $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($iplist as $ip) {
            if (validateIP($ip)) {
                return $ip;
            }
        }
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED']) && validateIP($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    }
    
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validateIP($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    }
    
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validateIP($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    }
    
    if (!empty($_SERVER['HTTP_FORWARDED']) && validateIP($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    }

    // Return unreliable IP address since all else failed
    return $_SERVER['REMOTE_ADDR'];
}

function validateIP($ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    return true;
}

function getLocationDetails($ip) {
    // Check if IP is local or invalid
    if ($ip === '::1' || $ip === '127.0.0.1' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return [
            'country' => 'Local',
            'region' => 'Local',
            'city' => 'Local',
            'org' => 'Local Network'
        ];
    }

    // Try multiple IP geolocation services for better reliability
    $services = [
        "https://ipapi.co/{$ip}/json/",
        "https://ipinfo.io/{$ip}/json",
        "http://ip-api.com/json/{$ip}"
    ];
    
    foreach ($services as $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            
            // Format data based on service
            if (strpos($url, 'ipapi.co') !== false && !isset($data['error'])) {
                return [
                    'country' => $data['country_name'] ?? 'Unknown',
                    'region' => $data['region'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'org' => $data['org'] ?? 'Unknown'
                ];
            } elseif (strpos($url, 'ipinfo.io') !== false && !isset($data['error'])) {
                return [
                    'country' => $data['country'] ?? 'Unknown',
                    'region' => $data['region'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'org' => $data['org'] ?? 'Unknown'
                ];
            } elseif (strpos($url, 'ip-api.com') !== false && isset($data['status']) && $data['status'] === 'success') {
                return [
                    'country' => $data['country'] ?? 'Unknown',
                    'region' => $data['regionName'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'org' => $data['org'] ?? $data['isp'] ?? 'Unknown'
                ];
            }
        }
        
        // Small delay between requests
        usleep(100000);
    }
    
    // Fallback if all services fail
    return [
        'country' => 'Unknown',
        'region' => 'Unknown',
        'city' => 'Unknown',
        'org' => 'Unknown'
    ];
}

// Handle tracking functionality
if (isset($_GET['number']) && isset($_GET['width']) && isset($_GET['height'])) {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // prevent bots from faking clicks
    if (stripos($ua, 'WhatsApp') !== false || stripos($ua, 'facebookexternalhit') !== false) {
        exit();
    }

    $encodedNumber = $_GET['number'];
    $actualNumber = decodeNumber($encodedNumber);
    
    // Ensure we have a valid number after decoding
    if (empty($actualNumber)) {
        die("Invalid contact number");
    }

    $group = $_GET['group_name'] ?? 'Unknown';
    
    // Get the website name from the URL path
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $url = 'Unknown Website';

    // Check if $websites array exists (from config.php)
    if (isset($websites) && is_array($websites)) {
        foreach ($websites as $name => $websiteUrl) {
            $websitePath = parse_url($websiteUrl, PHP_URL_PATH);
            $currentPath = rtrim($currentPath, '/');
            $websitePath = rtrim($websitePath, '/');
            
            if ($currentPath === $websitePath) {
                $url = $name;
                break;
            }
        }
    }
    
    $clicked = date("Y-m-d H:i:s");
    $ip = getRealIP();
    $device = $ua;
    $os = getOS($device);
    $browser = getBrowser($device);
    $is_mobile = isMobile($ua);
    

    // unique token
    $token = bin2hex(random_bytes(16));

    // extra details from query (set by JS)
    $screen_resolution = ($_GET['width'] ?? '') . "x" . ($_GET['height'] ?? '');
    $color_depth       = $_GET['color_depth'] ?? '';
    $timezone          = $_GET['timezone'] ?? '';
    $webgl             = $_GET['webgl'] ?? '';
    $canvas            = $_GET['canvas'] ?? '';
    $fingerprint       = "Resolution:$screen_resolution|ColorDepth:$color_depth|Timezone:$timezone|WebGL:$webgl|Canvas:$canvas";

    // location - with improved error handling
    $location = getLocationDetails($ip);
    $country = $location['country'] ?? 'Unknown';
    $region  = $location['region'] ?? 'Unknown';
    $city    = $location['city'] ?? 'Unknown';
    $isp     = $location['org'] ?? 'Unknown';

    // language & referrer
    $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown', 0, 20);
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';

    // UTM parameters
    $utm_source   = $_GET['utm_source']   ?? 'N/A';
    $utm_medium   = $_GET['utm_medium']   ?? 'N/A';
    $utm_campaign = $_GET['utm_campaign'] ?? 'N/A';

    // Insert into trace_db database
    $stmt = $trace_conn->prepare("INSERT INTO trace_data 
        (contact, url, clicked_at, ip_address, device, os, group_name, browser, 
    fingerprint, country, region, city, isp, language, referrer, 
    utm_source, utm_medium, utm_campaign, token, screen_resolution, is_mobile) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssssssssssssssssssi", 
            $actualNumber,
            $url, 
            $clicked, 
            $ip, 
            $device, 
            $os, 
            $group, 
            $browser, 
            $fingerprint, 
            $country, 
            $region, 
            $city, 
            $isp, 
            $language, 
            $referrer, 
            $utm_source, 
            $utm_medium, 
            $utm_campaign, 
            $token, 
            $screen_resolution, 
            $is_mobile);
        
        if (!$stmt->execute()) {
            error_log("Trace DB error: " . $stmt->error);
            // Log the location data that failed
            error_log("Failed location data: " . print_r($location, true));
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare trace statement: " . $trace_conn->error);
    }
    
    // Exit after processing tracking to prevent rendering login page
    exit();
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user data from csd_registration database
    $stmt = $csd_conn->prepare("SELECT id, first_name, password FROM users WHERE email = ?");
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
            $login_error = "Invalid email or password";
        }
    } else {
        $login_error = "User not found";
    }

    $stmt->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSD Pakistan - CSD The Caring Store</title>
    <link rel="icon" href="carts.png" type="image/png">
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
