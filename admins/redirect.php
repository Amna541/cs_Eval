<?php
session_start();
require_once 'db_connect.php';

// Encryption functions
function decryptData($data) {
    $key = 'CyberDiv101SecretKey32BytesLong!!';
    $cipher = "aes-256-cbc";
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, $cipher, $key, 0, $iv);
}

// Get short code from URL
$shortCode = $_GET['c'] ?? '';

if (empty($shortCode)) {
    die('Invalid short link');
}

// Check if short URL exists in session
if (!isset($_SESSION['short_urls'][$shortCode])) {
    die('Short link not found or expired');
}

$urlData = $_SESSION['short_urls'][$shortCode];

try {
    // Decrypt the tracking data
    $decrypted = decryptData($urlData['encrypted_data']);
    $data = json_decode($decrypted, true);
    
    if (!$data) {
        die('Invalid link data');
    }
    
    // Get visitor information
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // Parse user agent for device info
    $os = 'Unknown';
    $device = 'Unknown';
    
    if (strpos($userAgent, 'Windows') !== false) $os = 'Windows';
    elseif (strpos($userAgent, 'Mac') !== false) $os = 'MacOS';
    elseif (strpos($userAgent, 'Linux') !== false) $os = 'Linux';
    elseif (strpos($userAgent, 'Android') !== false) { $os = 'Android'; $device = 'Mobile'; }
    elseif (strpos($userAgent, 'iPhone') !== false) { $os = 'iOS'; $device = 'Mobile'; }
    elseif (strpos($userAgent, 'iPad') !== false) { $os = 'iOS'; $device = 'Tablet'; }
    
    // Get screen dimensions if available
    $width = $_GET['width'] ?? 'Unknown';
    $height = $_GET['height'] ?? 'Unknown';
    
    // Get location from IP (simplified version)
    function getLocationFromIP($ip) {
        $ipParts = explode('.', $ip);
        $first = $ipParts[0] ?? 0;
        $second = $ipParts[1] ?? 0;
        
        // Generate coordinates based on IP
        $lat = 30.3753 + (($first % 10) - 5);
        $lng = 69.3451 + (($second % 10) - 5);
        
        $cities = ['Islamabad', 'Karachi', 'Lahore', 'Rawalpindi', 'Peshawar', 'Quetta', 'Multan', 'Faisalabad'];
        $city = $cities[$first % count($cities)];
        
        return [
            'latitude' => $lat,
            'longitude' => $lng,
            'city' => $city,
            'region' => 'Pakistan',
            'country' => 'Pakistan'
        ];
    }
    
    $location = getLocationFromIP($ip);
    
    // Log the click in trace_data table
    $stmt = $conn->prepare("INSERT INTO trace_data 
        (url, contact, ip_address, user_agent, os, device, screen_width, screen_height, 
         latitude, longitude, city, region, country, group_name, clicked_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $phone = $data['phone'];
    $group = $data['group'] ?? 'Unknown';
    
    $stmt->bind_param(
        "ssssssiidddsss",
        $urlData['original_url'],
        $phone,
        $ip,
        $userAgent,
        $os,
        $device,
        $width,
        $height,
        $location['latitude'],
        $location['longitude'],
        $location['city'],
        $location['region'],
        $location['country'],
        $group
    );
    
    $stmt->execute();
    $stmt->close();
    
    // Build final URL with base64 encoded number
    $encodedNumber = base64_encode($phone);
    $finalUrl = str_replace('{number}', $encodedNumber, $urlData['original_url']);
    $finalUrl .= '&group_name=' . urlencode($group);
    
    // Add screen dimensions if available
    if ($width !== 'Unknown') {
        $finalUrl .= '&width=' . $width;
    }
    if ($height !== 'Unknown') {
        $finalUrl .= '&height=' . $height;
    }
    
    // Redirect to final URL
    header("Location: " . $finalUrl);
    exit;
    
} catch (Exception $e) {
    die('Error processing link: ' . $e->getMessage());
}
?>