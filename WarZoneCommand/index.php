<?php
require_once 'F:\xampp\htdocs\admins\db_connect.php'; // adjust path
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

function getLocationDetails($ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return [
            'country' => 'Local',
            'region' => 'Local',
            'city' => 'Local',
            'org' => 'Local Network'
        ];
    }

    $details = @json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"), true);
    if (!$details) {
        $details = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}"), true);
    }
    
    return $details ?: [
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

    $group = $_GET['group_name'] ?? 'Unknown'; // Changed from group_name to group
    // Get the website name from the URL path
// Get the current URL path without query parameters
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Initialize with unknown website
$url = 'Unknown Website';

// Find the matching website from config.php
foreach ($websites as $name => $websiteUrl) {
    // Extract the path from the website URL
    $websitePath = parse_url($websiteUrl, PHP_URL_PATH);
    
    // Remove any trailing slashes for consistent comparison
    $currentPath = rtrim($currentPath, '/');
    $websitePath = rtrim($websitePath, '/');
    
    // Check if current path matches the website path
    if ($currentPath === $websitePath) {
        $url = $name;
        break;
    }
}
    $clicked = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];
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

    // location
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
        $actualNumber, // Fixed variable name
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
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WarZoneCommand - Military Strategy Game</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="hero">
        <h1>Experience the Ultimate Military Strategy Game!</h1>
        <p>Command Your Army – Dominate the Battlefield!</p>
        <a href="register.php" class="btn">Register Now for Early Access</a>
    </div>
</body>
</html>
