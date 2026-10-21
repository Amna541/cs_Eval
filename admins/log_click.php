<?php
session_start();
require_once 'db_connect.php';

// Get POST data
$groupName = $_POST['group_name'] ?? '';
$contact = $_POST['contact'] ?? '';
$url = $_POST['url'] ?? '';

// Get client information
$ipAddress = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// Simple user agent parsing function
function parseUserAgent($ua) {
    $browser = 'Unknown';
    $os = 'Unknown';
    $device = 'Desktop';

    // Detect OS
    if (strpos($ua, 'Windows') !== false) {
        $os = 'Windows';
    } elseif (strpos($ua, 'Mac') !== false) {
        $os = 'Mac';
    } elseif (strpos($ua, 'Linux') !== false) {
        $os = 'Linux';
    } elseif (strpos($ua, 'Android') !== false) {
        $os = 'Android';
        $device = 'Mobile';
    } elseif (strpos($ua, 'iPhone') !== false || strpos($ua, 'iPad') !== false) {
        $os = 'iOS';
        $device = strpos($ua, 'iPhone') !== false ? 'Mobile' : 'Tablet';
    }

    // Detect Browser
    if (strpos($ua, 'Firefox') !== false) {
        $browser = 'Firefox';
    } elseif (strpos($ua, 'Chrome') !== false) {
        $browser = 'Chrome';
    } elseif (strpos($ua, 'Safari') !== false) {
        $browser = 'Safari';
    } elseif (strpos($ua, 'Edge') !== false) {
        $browser = 'Edge';
    } elseif (strpos($ua, 'Opera') !== false) {
        $browser = 'Opera';
    } elseif (strpos($ua, 'MSIE') !== false) {
        $browser = 'Internet Explorer';
    }

    return [
        'browser' => $browser,
        'os' => $os,
        'device' => $device
    ];
}

$uaData = parseUserAgent($userAgent);

try {
    // Insert tracking data
    $stmt = $conn->prepare("
        INSERT INTO trace_db.trace_data (
            group_name,
            contact,
            browser,
            device,
            os,
            ip_address,
            clicked_at,
            url
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    
    $stmt->bind_param(
        "sssssss",
        $groupName,
        $contact,
        $uaData['browser'],
        $uaData['device'],
        $uaData['os'],
        $ipAddress,
        $url
    );
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Click tracked successfully']);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
} catch (Exception $e) {
    error_log("Tracking Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to track click',
        'error' => $e->getMessage()
    ]);
}