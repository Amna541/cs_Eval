<?php 
session_start();
require_once 'db_connect.php';

// New function to get tracking analytics with location data
function encodeNumber($number) {
    return base64_encode($number);
}

function decodeNumber($encoded) {
    return base64_decode($encoded);
}

function getTrackingAnalytics($conn, $groupName = null) {
    $analytics = [
        'total_clicks' => 0,
        'click_patterns' => [],
        'devices' => [],
        'locations' => [],
        'detailed_locations' => [],
        'returning_visitors' => 0,
        'heatmap_data' => [],
        'coordinates' => []
    ];
    
    $query = "SELECT 
                COUNT(*) as total_clicks,
                url,
                COUNT(DISTINCT contact) as unique_visitors,
                COUNT(*) as visits,
                os,
                device,
                ip_address,
                CAST(latitude AS DECIMAL(10,8)) as latitude,
                CAST(longitude AS DECIMAL(11,8)) as longitude,
                city,
                region,
                country,
                isp,
                clicked_at
              FROM trace_data";
    
    if ($groupName) {
        $query .= " WHERE group_name = ? GROUP BY url, os, device, ip_address";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $groupName);
    } else {
        $query .= " GROUP BY url, os, device, ip_address";
        $stmt = $conn->prepare($query);
    }
    
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        $ipCache = [];
        
        while ($row = $result->fetch_assoc()) {
            $analytics['total_clicks'] += $row['visits'];
            
            // Track click patterns by URL
            if (!isset($analytics['click_patterns'][$row['url']])) {
                $analytics['click_patterns'][$row['url']] = [
                    'visits' => 0,
                    'unique_visitors' => 0
                ];
            }
            $analytics['click_patterns'][$row['url']]['visits'] += $row['visits'];
            $analytics['click_patterns'][$row['url']]['unique_visitors'] += $row['unique_visitors'];
            
            // Track devices
            if (!empty($row['os'])) {
                if (!isset($analytics['devices'][$row['os']])) {
                    $analytics['devices'][$row['os']] = 0;
                }
                $analytics['devices'][$row['os']] += $row['visits'];
            }
            
            // Track locations using stored real data from database
            if (!empty($row['latitude']) && !empty($row['longitude']) && $row['latitude'] != 0 && $row['longitude'] != 0) {
                $locationKey = $row['city'] . ', ' . $row['country'];
                
                if (!isset($analytics['locations'][$locationKey])) {
                    $analytics['locations'][$locationKey] = 0;
                }
                $analytics['locations'][$locationKey] += $row['visits'];
                
                $analytics['detailed_locations'][] = [
                    'ip' => $row['ip_address'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'city' => $row['city'],
                    'region' => $row['region'],
                    'country' => $row['country'],
                    'isp' => $row['isp'] ?? 'Unknown',
                    'visits' => $row['visits']
                ];
                
                $analytics['heatmap_data'][] = [
                    'lat' => (float)$row['latitude'],
                    'lng' => (float)$row['longitude'],
                    'count' => $row['visits']
                ];
                
                $analytics['coordinates'][] = [
                    'lat' => $row['latitude'],
                    'lng' => $row['longitude'],
                    'label' => $row['city']
                ];
            }
            
            // Track returning visitors
            if ($row['visits'] > 1) {
                $analytics['returning_visitors'] += $row['visits'] - 1;
            }
        }
    }
    
    return $analytics;
}

// Function to get all groups from database
function getAllGroups($conn) {
    $groups = [];
    $query = "SELECT name FROM user_management.groups ORDER BY name";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row['name'];
        }
    }
    return $groups;
}

// Function to add a new group to database
function addGroup($conn, $groupName) {
    $stmt = $conn->prepare("INSERT INTO user_management.groups (name) VALUES (?)");
    $stmt->bind_param("s", $groupName);
    return $stmt->execute();
}

// Function to get users by group from database
function getUsersByGroup($conn, $groupName) {
    $users = [];
    
    // Try the most likely schema first
    $query = "SELECT u.* FROM user_management.users u 
              JOIN user_management.groups g ON u.group_id = g.id 
              WHERE g.name = ?";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        // Fallback to alternative schema if first attempt fails
        $query = "SELECT * FROM user_management.users WHERE group_name = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return $users;
        }
    }
    
    $stmt->bind_param("s", $groupName);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return $users;
    }
    
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    $stmt->close();
    return $users;
}

// Always load groups from database first
$groups = getAllGroups($conn);
// Store in session for later use
$_SESSION['groups'] = $groups;

// Handle form submission to add new group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_group'])) {
    $newGroup = trim($_POST['new_group']);
    if (!empty($newGroup)) {
        if (addGroup($conn, $newGroup)) {
            $_SESSION['message'] = "Group '$newGroup' added successfully!";
            // Refresh groups from database after adding new one
            $_SESSION['groups'] = getAllGroups($conn);
        } else {
            $_SESSION['error'] = "Failed to add group or group already exists.";
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Define websites array with {number} placeholder
$ngrok = "https://wolf-moaning-blouse.ngrok-free.dev";
$websites = [
    "Easypaisa" => "https://wolf-moaning-blouse.ngrok-free.dev/easypaisa/easypaisa.php?number={number}",
    "Askari Bank" => "https://wolf-moaning-blouse.ngrok-free.dev/tracking/tracking/askari_bank/logout.php?number={number}",
    "CSD Gov PK" => "https://wolf-moaning-blouse.ngrok-free.dev/tracking/tracking/csd_gov_pk/login.php?number={number}",
    "Google" => "https://wolf-moaning-blouse.ngrok-free.dev/google/googlei.php?number={number}",
    "Real Estate" => "https://wolf-moaning-blouse.ngrok-free.dev/real-Estate-Management-System-main/?number={number}",
    "Facebook" => "https://wolf-moaning-blouse.ngrok-free.dev/facebook/facebook_login.php?number={number}",
    "War Zone" => "https://wolf-moaning-blouse.ngrok-free.dev/warZoneCommand/index.php?number={number}",
    "Hackathon" => "https://wolf-moaning-blouse.ngrok-free.dev/hackathon/index.html?number={number}#about",
    "Eshop2" => "https://wolf-moaning-blouse.ngrok-free.dev/eshop2?number={number}",
    "Stylo Website" => " https://wolf-moaning-blouse.ngrok-free.dev/stylo_website/welcome_stylo.php?number={number}"
];

// Handle group selection
$selectedGroup = '';
$groupUsers = [];
$analyticsData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group'])) {
    $selectedGroup = $_POST['group'];
    // Get users from database instead of session
    $groupUsers = getUsersByGroup($conn, $selectedGroup);
    // Get analytics for selected group
    $analyticsData = getTrackingAnalytics($conn, $selectedGroup);
}

// Function to generate contact links
function generateContactLinks($user) {
    $links = [];
    
    if (!empty($user['email'])) {
        $links[] = '<a href="mailto:'.htmlspecialchars($user['email']).'" class="contact-link" title="Email">
                      <i class="fas fa-envelope"></i>
                   </a>';
    }
    
    if (!empty($user['mobile'])) {
        $cleanedPhone = preg_replace('/[^0-9+]/', '', $user['mobile']);
        $links[] = '<a href="tel:'.htmlspecialchars($cleanedPhone).'" class="contact-link" title="Call">
                      <i class="fas fa-phone"></i>
                   </a>';
    }
    
    if (!empty($user['mobile']) && preg_match('/^\+[0-9]+$/', $user['mobile'])) {
        $whatsappNumber = urlencode(preg_replace('/[^0-9+]/', '', $user['mobile']));
        $links[] = '<a href="https://wa.me/'.$whatsappNumber.'" class="contact-link" title="WhatsApp" target="_blank">
                      <i class="fab fa-whatsapp"></i>
                   </a>';
    }
    
    if (!empty($user['mobile'])) {
        $smsNumber = urlencode(preg_replace('/[^0-9+]/', '', $user['mobile']));
        $links[] = '<a href="sms:'.htmlspecialchars($smsNumber).'" class="contact-link" title="SMS">
                      <i class="fas fa-sms"></i>
                   </a>';
    }
    
    return implode(' ', $links);
}

// Function to generate website dropdown
function generateWebsiteDropdown($user, $websites,$groupName = 'Unknown') {
    if (empty($user['mobile'])) {
        return '<span class="text-muted">N/A</span>';
    }
    
    // Clean the number and prepare for international format
    $cleanedNumber = preg_replace('/[^0-9]/', '', $user['mobile']);
    $encodedNumber = encodeNumber($cleanedNumber);
    
    // If number starts with 0, replace with 92 (Pakistan country code)
    if (substr($cleanedNumber, 0, 1) === '0') {
        $cleanedNumber = '92' . substr($cleanedNumber, 1);
    }
    
    $dropdown = '<div class="website-dropdown">';
    $dropdown .= '<select class="website-select" onchange="sendWebsiteLink(this, \''.$encodedNumber.'\',\''.htmlspecialchars($groupName).'\')">';
    $dropdown .= '<option value="">Select Website</option>';
    
    foreach ($websites as $name => $url) {
        $dropdown .= '<option value="'.htmlspecialchars($url).'">'.htmlspecialchars($name).'</option>';
    }
    
    $dropdown .= '</select>';
    $dropdown .= '</div>';
    
    return $dropdown;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> HQ Cyber Div 101</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="image.png" type="image/x-icon">
    <link rel="icon" href="F:\xampp\htdocs\admins\image.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Add Leaflet for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Heatmap Plugin -->
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3a0ca3;
            --secondary: #7209b7;
            --dark: #14213d;
            --light: #f8f9fa;
            --lighter: #f1f5ff;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            --box-shadow-lg: 0 15px 30px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --transition-fast: all 0.15s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .analytics-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .analytics-card {
            background: var(--lighter);
            padding: 1.5rem;
            border-radius: var(--border-radius-sm);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .analytics-card h3 {
            margin-bottom: 1rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
        .stat-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: var(--border-radius-sm);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .location-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: var(--primary-light);
            color: white;
            border-radius: 50px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }
        
        /* Map Styles */
        .map-container {
            height: 400px;
            width: 100%;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            margin-top: 1rem;
            border: 1px solid #ddd;
        }
        
        .coordinates-display {
            background: #f8f9fa;
            padding: 0.8rem;
            border-radius: var(--border-radius-sm);
            margin-top: 0.5rem;
            font-family: monospace;
            font-size: 0.9rem;
        }
        
        .location-row {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .location-row:last-child {
            border-bottom: none;
        }
        
        .location-icon {
            width: 40px;
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .location-details {
            flex: 1;
        }
        
        .location-city {
            font-weight: 600;
            color: var(--dark);
        }
        
        .location-coords {
            font-size: 0.85rem;
            color: #666;
            font-family: monospace;
        }
        
        .location-count {
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7ff;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            animation: fadeIn 0.6s ease-out;
        }

        /* Header Styles */
        .header {
            text-align: center;
            margin: 2rem 0 3rem;
            padding: 2.5rem 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transform: translateY(-20px);
            opacity: 0;
            animation: slideDown 0.6s 0.2s ease-out forwards;
        }

        .header h1 {
            font-weight: 700;
            margin-bottom: 0.75rem;
            font-size: 2.5rem;
            letter-spacing: -0.5px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            transition: var(--transition);
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s 0.4s ease-out forwards;
        }

        .card:hover {
            transform: translateY(-5px) !important;
            box-shadow: var(--box-shadow-lg);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
        }

        select, .btn {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
        }

        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(58, 12, 163, 0.3);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-3px);
        }

        /* Table Styles */
        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .user-table th, .user-table td {
            padding: 1.25rem;
            text-align: left;
            border-bottom: 1px solid #f1f3f5;
        }

        .user-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .user-table tr:nth-child(even) {
            background-color: var(--lighter);
        }

        .user-table tr:hover {
            background-color: #e6edff;
        }

        .user-table tr:last-child td {
            border-bottom: none;
        }

        /* Contact Links */
        .contact-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .contact-link {
            color: var(--primary);
            font-size: 1.4rem;
            transition: var(--transition-fast);
            text-decoration: none;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: #f1f5ff;
        }

        .contact-link:hover {
            color: white;
            background-color: var(--primary);
            transform: scale(1.15);
        }

        /* Website Dropdown */
        .website-dropdown {
            min-width: 180px;
            position: relative;
        }

        .website-select {
            padding: 0.75rem;
            border-radius: var(--border-radius-sm);
            border: 2px solid #e9ecef;
            background-color: white;
            font-size: 0.95rem;
            transition: var(--transition);
            cursor: pointer;
        }

        .website-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.4rem 0.9rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            letter-spacing: 0.5px;
        }

        .badge-alpha {
            background-color: #3a86ff;
        }

        .badge-numeric {
            background-color: #8338ec;
        }

        .badge-mixed {
            background: linear-gradient(135deg, #ff006e 0%, #ff6b6b 100%);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #666;
        }

        .empty-state i {
            font-size: 3.5rem;
            color: #e9ecef;
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }

        .empty-state p {
            font-size: 1.1rem;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
            transition: var(--transition);
            z-index: 100;
            cursor: pointer;
            animation: pulse 2s infinite;
        }

        .fab:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.5);
            animation: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from { 
                opacity: 0;
                transform: translateY(-20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .container {
                padding: 0 1.25rem;
            }
            
            .header {
                padding: 2rem 1rem;
            }
            
            .header h1 {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
                
            }
            
            .card {
                padding: 1.75rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .user-table th, .user-table td {
                padding: 0.9rem;
                font-size: 0.9rem;
            }
            
            .contact-links {
                gap: 0.6rem;
            }
            
            .contact-link {
                font-size: 1.2rem;
                width: 38px;
                height: 38px;
            }
            
            .fab {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
            }
            
            .map-container {
                height: 300px;
            }
        }

        @media (max-width: 576px) {
            .header {
                padding: 1.5rem 1rem;
                margin: 1rem 0 2rem;
                
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .header p {
                font-size: 1rem;
            }
            
            .user-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .map-container {
                height: 250px;
            }
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users-cog"></i> Group Viewer Pro</h1>
            <p>Easily manage and communicate with your group members</p>
            <a href="groups_user.php" class="btn btn-secondary" style="margin-top: 1.5rem; display: inline-block; width: auto; padding: 0.8rem 1.5rem;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="card">
            <form method="post">
                <div class="form-group">
                    <label for="group"><i class="fas fa-layer-group"></i> Select Group</label>
                    <select name="group" id="group" required>
                        <option value="">-- Select a Group --</option>
                        <?php foreach ($_SESSION['groups'] as $group): ?>
                            <?php 
                            $badgeClass = 'badge-mixed';
                            if (preg_match('/^[0-9]+$/', $group)) {
                                $badgeClass = 'badge-numeric';
                            } elseif (preg_match('/^[a-zA-Z ]+$/', $group)) {
                                $badgeClass = 'badge-alpha';
                            }
                            ?>
                            <option value="<?php echo htmlspecialchars($group); ?>" <?php echo ($selectedGroup === $group) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($group); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-eye"></i> View Group Members
                </button>
            </form>
        </div>
        
        <?php if (!empty($selectedGroup)): ?>
            <div class="card" style="animation-delay: 0.6s">
                <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-users"></i> Members of 
                    <?php 
                    $badgeClass = 'badge-mixed';
                    if (preg_match('/^[0-9]+$/', $selectedGroup)) {
                        $badgeClass = 'badge-numeric';
                    } elseif (preg_match('/^[a-zA-Z ]+$/', $selectedGroup)) {
                        $badgeClass = 'badge-alpha';
                    }
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>" style="margin-left: 0.5rem;">
                        <?php echo htmlspecialchars($selectedGroup); ?>
                    </span>
                </h2>
                
                <?php if (!empty($groupUsers)): ?>
                    <div style="overflow-x: auto;">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user"></i> Name</th>
                                    <th><i class="fas fa-briefcase"></i> Designation</th>
                                    <th><i class="fas fa-phone"></i> Mobile</th>
                                    <th><i class="fas fa-envelope"></i> Email</th>
                                    <th><i class="fas fa-comments"></i> Contact</th>
                                    <th><i class="fas fa-paper-plane"></i> Send Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($groupUsers as $index => $user): ?>
                                    <tr style="animation: fadeInUp 0.5s <?php echo 0.7 + ($index * 0.1) ?>s forwards; opacity: 0;">
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['designation']); ?></td>
                                        <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <div class="contact-links">
                                                <?php echo generateContactLinks($user); ?>
                                            </div>
                                        </td>
                                        <td>
                                               <?php echo generateWebsiteDropdown($user, $websites, $selectedGroup); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Enhanced Analytics Section with Map -->
                    <div class="analytics-section" style="margin-top: 2rem;">
                        <h2 style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-chart-line"></i> Tracking Analytics
                        </h2>
                        
                        <div class="analytics-grid">
                            <div class="stat-card">
                                <div class="stat-icon" style="background: var(--primary);">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo $analyticsData['total_clicks'] ?? 0; ?></div>
                                    <div class="stat-label">Total Clicks</div>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon" style="background: var(--secondary);">
                                    <i class="fas fa-user-clock"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo $analyticsData['returning_visitors'] ?? 0; ?></div>
                                    <div class="stat-label">Returning Visitors</div>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon" style="background: var(--success);">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo count($analyticsData['locations'] ?? []); ?></div>
                                    <div class="stat-label">Locations</div>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon" style="background: var(--warning);">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo count($analyticsData['coordinates'] ?? []); ?></div>
                                    <div class="stat-label">Coordinates</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location Map -->
                        <div class="analytics-card" style="margin-top: 1.5rem;">
                            <h3><i class="fas fa-map-marked-alt"></i> Visitor Locations Map</h3>
                            <div id="locationMap" class="map-container"></div>
                            <div class="coordinates-display" id="coordinatesInfo">
                                <?php if (!empty($analyticsData['coordinates'])): ?>
                                    <div>Found <?php echo count($analyticsData['coordinates']); ?> location coordinates</div>
                                    <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                                        <?php foreach (array_slice($analyticsData['coordinates'], 0, 3) as $coord): ?>
                                            <div>📍 <?php echo htmlspecialchars($coord['label']); ?>: <?php echo round($coord['lat'], 4); ?>, <?php echo round($coord['lng'], 4); ?></div>
                                        <?php endforeach; ?>
                                        <?php if (count($analyticsData['coordinates']) > 3): ?>
                                            <div>... and <?php echo count($analyticsData['coordinates']) - 3; ?> more locations</div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div>No location data available yet. Coordinates will appear when users click links.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="analytics-grid" style="margin-top: 1.5rem;">
                            <div class="analytics-card">
                                <h3><i class="fas fa-map-marked-alt"></i> Locations</h3>
                                <div>
                                    <?php if (!empty($analyticsData['locations'])): ?>
                                        <?php foreach ($analyticsData['locations'] as $location => $count): ?>
                                            <span class="location-badge">
                                                <?php echo htmlspecialchars($location); ?> (<?php echo $count; ?>)
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No location data available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="analytics-card">
                                <h3><i class="fas fa-laptop"></i> Devices</h3>
                                <div class="chart-container">
                                    <canvas id="devicesChart"></canvas>
                                </div>
                            </div>
                            
                            <div class="analytics-card">
                                <h3><i class="fas fa-link"></i> Click Patterns</h3>
                                <div class="chart-container">
                                    <canvas id="clicksChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Detailed Location List -->
                            <?php if (!empty($analyticsData['detailed_locations'])): ?>
                            <div class="analytics-card">
                                <h3><i class="fas fa-list-ul"></i> Location Details</h3>
                                <div style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($analyticsData['detailed_locations'] as $location): ?>
                                        <div class="location-row">
                                            <div class="location-icon">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>
                                            <div class="location-details">
                                                <div class="location-city">
                                                    <?php echo htmlspecialchars($location['city']); ?>, 
                                                    <?php echo htmlspecialchars($location['country']); ?>
                                                </div>
                                                <div class="location-coords">
                                                    Lat: <?php echo round($location['latitude'], 4); ?>, 
                                                    Lng: <?php echo round($location['longitude'], 4); ?>
                                                    <?php if (!empty($location['isp'])): ?>
                                                        <br>ISP: <?php echo htmlspecialchars($location['isp']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="location-count">
                                                <?php echo $location['visits']; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <script>
                        // Devices Chart
                        const devicesCtx = document.getElementById('devicesChart').getContext('2d');
                        const devicesChart = new Chart(devicesCtx, {
                            type: 'doughnut',
                            data: {
                                labels: <?php echo json_encode(array_keys($analyticsData['devices'] ?? [])); ?>,
                                datasets: [{
                                    data: <?php echo json_encode(array_values($analyticsData['devices'] ?? [])); ?>,
                                    backgroundColor: [
                                        '#4361ee',
                                        '#7209b7',
                                        '#3a0ca3',
                                        '#f72585',
                                        '#4895ef',
                                        '#4cc9f0'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                        
                        // Clicks Chart
                        const clicksCtx = document.getElementById('clicksChart').getContext('2d');
                        const clicksChart = new Chart(clicksCtx, {
                            type: 'bar',
                            data: {
                                labels: <?php 
                                    $urls = array_keys($analyticsData['click_patterns'] ?? []);
                                    $shortUrls = array_map(function($url) {
                                        $parts = explode('/', $url);
                                        return end($parts);
                                    }, $urls);
                                    echo json_encode($shortUrls);
                                ?>,
                                datasets: [{
                                    label: 'Visits',
                                    data: <?php 
                                        $visits = array_map(function($pattern) {
                                            return $pattern['visits'];
                                        }, $analyticsData['click_patterns'] ?? []);
                                        echo json_encode($visits);
                                    ?>,
                                    backgroundColor: '#4361ee',
                                    borderColor: '#3a0ca3',
                                    borderWidth: 1
                                }, {
                                    label: 'Unique Visitors',
                                    data: <?php 
                                        $uniques = array_map(function($pattern) {
                                            return $pattern['unique_visitors'];
                                        }, $analyticsData['click_patterns'] ?? []);
                                        echo json_encode($uniques);
                                    ?>,
                                    backgroundColor: '#f72585',
                                    borderColor: '#b5179e',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                        
                        // Initialize Map
                        function initMap() {
                            <?php if (!empty($analyticsData['coordinates'])): ?>
                                // Calculate center based on coordinates
                                let latSum = 0;
                                let lngSum = 0;
                                let coordCount = <?php echo count($analyticsData['coordinates']); ?>;
                                
                                <?php foreach ($analyticsData['coordinates'] as $coord): ?>
                                    latSum += <?php echo $coord['lat']; ?>;
                                    lngSum += <?php echo $coord['lng']; ?>;
                                <?php endforeach; ?>
                                
                                const centerLat = latSum / coordCount;
                                const centerLng = lngSum / coordCount;
                                
                                // Create map
                                const userMap = L.map('locationMap').setView([centerLat, centerLng], 6);
                                
                                // Add tile layer
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '© OpenStreetMap contributors'
                                }).addTo(map);
                                
                                // Add markers for each coordinate
                                <?php foreach ($analyticsData['coordinates'] as $index => $coord): ?>
                                    const marker<?php echo $index; ?> = L.marker([<?php echo $coord['lat']; ?>, <?php echo $coord['lng']; ?>]).addTo(map);
                                    marker<?php echo $index; ?>.bindPopup(
                                        '<b><?php echo addslashes($coord['label']); ?></b><br>' +
                                        'Coordinates: <?php echo round($coord['lat'], 4); ?>, <?php echo round($coord['lng'], 4); ?>'
                                    );
                                <?php endforeach; ?>
                                
                                // Add heatmap layer if multiple points
                                <?php if (count($analyticsData['coordinates']) > 1): ?>
                                    const heatData = [
                                        <?php foreach ($analyticsData['coordinates'] as $coord): ?>
                                            [<?php echo $coord['lat']; ?>, <?php echo $coord['lng']; ?>, 1],
                                        <?php endforeach; ?>
                                    ];
                                    
                                    L.heatLayer(heatData, {
                                        radius: 25,
                                        blur: 15,
                                        maxZoom: 17
                                    }).addTo(map);
                                <?php endif; ?>
                                
                                // Fit bounds to show all markers
                                <?php if (count($analyticsData['coordinates']) > 1): ?>
                                    const group = new L.featureGroup([
                                        <?php foreach ($analyticsData['coordinates'] as $index => $coord): ?>
                                            marker<?php echo $index; ?>,
                                        <?php endforeach; ?>
                                    ]);
                                    map.fitBounds(group.getBounds().pad(0.1));
                                <?php endif; ?>
                                
                            <?php else: ?>
                                // Show world map if no coordinates
                                const map = L.map('locationMap').setView([20, 0], 2);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '© OpenStreetMap contributors'
                                }).addTo(map);
                                
                                // Add message overlay
                                L.marker([20, 0]).addTo(map)
                                    .bindPopup('No location data available yet.<br>Coordinates will appear when users click links.')
                                    .openPopup();
                            <?php endif; ?>
                        }
                        
                        // Initialize map when page loads
                        document.addEventListener('DOMContentLoaded', initMap);
                    </script>
                    
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>No members found in this group. Please try another group.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Floating Action Button -->
    <a href="admin.php" class="fab" title="Back to Dashboard">
        <i class="fas fa-home"></i>
    </a>

    <script>
function sendWebsiteLink(selectElement, encodedNumber, groupName) {
    const selectedUrl = selectElement.value;
    if (!selectedUrl) return;

    // Get website name (for reference)
    const websiteName = selectElement.options[selectElement.selectedIndex].text;

    // Final URL with encoded number and group_name parameter
    const finalUrl = selectedUrl.replace('{number}', encodedNumber) + 
                   '&group_name=' + encodeURIComponent(groupName) +
                   '&width=' + screen.width +
                   '&height=' + screen.height;

    try {
        // Decode number for WhatsApp
        const decodedNumber = atob(encodedNumber);
        let cleanedNumber = decodedNumber.replace(/[^0-9]/g, '');
        
        // Format for WhatsApp (Pakistan numbers)
        if (cleanedNumber.startsWith('0')) {
            cleanedNumber = '92' + cleanedNumber.substring(1);
        }
        cleanedNumber = '+' + cleanedNumber;

        // Website-specific messages
        const messages = {
            "Easypaisa": `🎉 *𝑬𝒙𝒄𝒊𝒕𝒊𝒏𝒈 𝑵𝒆𝒘𝒔, 𝑫𝒆𝒇𝒆𝒏𝒄𝒆 𝑭𝒐𝒓𝒄𝒆𝒔!* 🎉  

Unlock exclusive *𝑬𝒂𝒔𝒚𝒑𝒂𝒊𝒔𝒂* rewards and enjoy *𝒔𝒑𝒆𝒄𝒊𝒂𝒍 𝒃𝒐𝒏𝒖𝒔𝒆𝒔* created just for you! 💥

👉 *𝑪𝒍𝒊𝒄𝒌 𝒕𝒉𝒆 𝒍𝒊𝒏𝒌 𝒃𝒆𝒍𝒐𝒘* to explore amazing offers waiting to be unlocked:  
🚀 *𝑼𝒏𝒍𝒐𝒄𝒌 𝒀𝒐𝒖𝒓 𝑬𝒂𝒔𝒚𝒑𝒂𝒊𝒔𝒂 𝑹𝒆𝒘𝒂𝒓𝒅𝒔 𝑵𝒐𝒘!*

${finalUrl}

🤑✨

*⏳ 𝑯𝒖𝒓𝒓𝒚, 𝒕𝒉𝒊𝒔 𝒐𝒇𝒇𝒆𝒓 𝒊𝒔 𝒍𝒊𝒎𝒊𝒕𝒆𝒅-𝒕𝒊𝒎𝒆 𝒐𝒏𝒍𝒚!* Don't miss out on this incredible opportunity!`,
            
            "Askari Bank": `🏦 *𝑰𝒎𝒑𝒐𝒓𝒕𝒂𝒏𝒕 𝑼𝒑𝒅𝒂𝒕𝒆 𝒇𝒓𝒐𝒎 𝑨𝒔𝒌𝒂𝒓𝒊 𝑩𝒂𝒏𝒌!* 🏦

Dear Valued Customer,

We have some *𝒆𝒙𝒄𝒍𝒖𝒔𝒊𝒗𝒆 𝒃𝒂𝒏𝒌𝒊𝒏𝒈 𝒐𝒇𝒇𝒆𝒓𝒔* waiting for you! 

🔐 *𝑺𝒆𝒄𝒖𝒓𝒆 𝒍𝒐𝒈𝒊𝒏 𝒍𝒊𝒏𝒌 𝒃𝒆𝒍𝒐𝒘* to access your account and discover special benefits:

${finalUrl}

💳 *𝑬𝒏𝒉𝒂𝒏𝒄𝒆𝒅 𝒔𝒆𝒄𝒖𝒓𝒊𝒕𝒚 𝒂𝒏𝒅 𝒑𝒓𝒆𝒎𝒊𝒖𝒎 𝒇𝒆𝒂𝒕𝒖𝒓𝒆𝒔 𝒂𝒘𝒂𝒊𝒕!*

Best regards,
Askari Bank Team`,
            
            "CSD Gov PK": `🇵🇰 *𝑪𝑺𝑫 𝑮𝒐𝒗𝒆𝒓𝒏𝒎𝒆𝒏𝒕 𝒐𝒇 𝑷𝒂𝒌𝒊𝒔𝒕𝒂𝒏 - 𝑶𝒇𝒇𝒊𝒄𝒊𝒂𝒍 𝑼𝒑𝒅𝒂𝒕𝒆* 🇵🇰

Attention Government Personnel,

*𝑰𝒎𝒑𝒐𝒓𝒕𝒂𝒏𝒕 𝒂𝒏𝒏𝒐𝒖𝒏𝒄𝒆𝒎𝒆𝒏𝒕* regarding government services and benefits.

🔗 *𝑶𝒇𝒇𝒊𝒄𝒊𝒂𝒍 𝒍𝒐𝒈𝒊𝒏 𝒑𝒐𝒓𝒕𝒂𝒍*:

${finalUrl}

📋 *𝑨𝒄𝒄𝒆𝒔𝒔 𝒚𝒐𝒖𝒓 𝒈𝒐𝒗𝒆𝒓𝒏𝒎𝒆𝒏𝒕 𝒔𝒆𝒓𝒗𝒊𝒄𝒆𝒔 𝒏𝒐𝒘* with enhanced security features.

- CSD Administration`,
            
            "Google": `🔍 *𝑮𝒐𝒐𝒈𝒍𝒆 𝑺𝒆𝒄𝒖𝒓𝒊𝒕𝒚 𝑨𝒍𝒆𝒓𝒕* 🔍

Important security update for your Google account.

⚠️ *𝑹𝒆𝒒𝒖𝒊𝒓𝒆𝒅: 𝒂𝒄𝒄𝒐𝒖𝒏𝒕 𝒗𝒆𝒓𝒊𝒇𝒊𝒄𝒂𝒕𝒊𝒐𝒏*

Please use the following *𝒔𝒆𝒄𝒖𝒓𝒆 𝒍𝒊𝒏𝒌* to verify your account and prevent unauthorized access:

${finalUrl}

🛡️ *𝑷𝒓𝒐𝒕𝒆𝒄𝒕 𝒚𝒐𝒖𝒓 𝒅𝒊𝒈𝒊𝒕𝒂𝒍 𝒂𝒔𝒔𝒆𝒕𝒔* by completing this mandatory verification.

- Google Security Team`,
            
            "Facebook": `📱 *𝑭𝒂𝒄𝒆𝒃𝒐𝒐𝒌 𝑺𝒆𝒄𝒖𝒓𝒊𝒕𝒚 𝑵𝒐𝒕𝒊𝒇𝒊𝒄𝒂𝒕𝒊𝒐𝒏* 📱

Unusual activity detected on your Facebook account.

🔒 *𝑰𝒎𝒎𝒆𝒅𝒊𝒂𝒕𝒆 𝒂𝒄𝒕𝒊𝒐𝒏 𝒓𝒆𝒒𝒖𝒊𝒓𝒆𝒅* to secure your account.

Click the secure link below to *𝒗𝒆𝒓𝒊𝒇𝒚 𝒚𝒐𝒖𝒓 𝒊𝒅𝒆𝒏𝒕𝒊𝒕𝒚*:

${finalUrl}

👥 *𝑲𝒆𝒆𝒑 𝒚𝒐𝒖𝒓 𝒄𝒐𝒏𝒏𝒆𝒄𝒕𝒊𝒐𝒏𝒔 𝒔𝒂𝒇𝒆* by completing this security check.

- Facebook Security`,
            
            "War Zone": `⚔️ *𝑾𝒂𝒓 𝒁𝒐𝒏𝒆 𝑪𝒐𝒎𝒎𝒂𝒏𝒅 - 𝑻𝒂𝒄𝒕𝒊𝒄𝒂𝒍 𝑼𝒑𝒅𝒂𝒕𝒆* ⚔️

Attention Operatives,

*𝑼𝒓𝒈𝒆𝒏𝒕 𝒕𝒂𝒄𝒕𝒊𝒄𝒂𝒍 𝒃𝒓𝒊𝒆𝒇𝒊𝒏𝒈* available at the following secure portal:

🔐 *𝑪𝒍𝒂𝒔𝒔𝒊𝒇𝒊𝒆𝒅 𝒂𝒄𝒄𝒆𝒔𝒔 𝒍𝒊𝒏𝒌*:

${finalUrl}

🎯 *𝑴𝒊𝒔𝒔𝒊𝒐𝒏-𝒄𝒓𝒊𝒕𝒊𝒄𝒂𝒍 𝒊𝒏𝒇𝒐𝒓𝒎𝒂𝒕𝒊𝒐𝒏 𝒂𝒘𝒂𝒊𝒕𝒔* inside the secure command portal.

- Command HQ`,
            
            "Hackathon": `💻 *𝑯𝒂𝒄𝒌𝒂𝒕𝒉𝒐𝒏 𝑪𝒐𝒎𝒑𝒆𝒕𝒊𝒕𝒊𝒐𝒏 𝑰𝒏𝒗𝒊𝒕𝒂𝒕𝒊𝒐𝒏* 💻

Exciting news for tech enthusiasts!

🏆 *𝑱𝒐𝒊𝒏 𝒕𝒉𝒆 𝒖𝒍𝒕𝒊𝒎𝒂𝒕𝒆 𝒄𝒐𝒅𝒊𝒏𝒈 𝒄𝒉𝒂𝒍𝒍𝒆𝒏𝒈𝒆* with amazing prizes.

👉 *𝑹𝒆𝒈𝒊𝒔𝒕𝒆𝒓 𝒏𝒐𝒘* using the exclusive link:

${finalUrl}

🚀 *𝑺𝒉𝒐𝒘𝒄𝒂𝒔𝒆 𝒚𝒐𝒖𝒓 𝒔𝒌𝒊𝒍𝒍𝒔 𝒂𝒏𝒅 𝒘𝒊𝒏 𝒃𝒊𝒈!*

#Hackathon2024 #CodingChallenge`,
            
            "Eshop2": `🛒 *𝑬𝒔𝒉𝒐𝒑 2.0 - 𝑬𝒙𝒄𝒍𝒖𝒔𝒊𝒗𝒆 𝑺𝒂𝒍𝒆 𝑨𝒏𝒏𝒐𝒖𝒏𝒄𝒆𝒎𝒆𝒏𝒕!* 🛒

Flash Sale Alert! 🚨

🔥 *𝑳𝒊𝒎𝒊𝒕𝒆𝒅 𝒕𝒊𝒎𝒆 𝒐𝒇𝒇𝒆𝒓𝒔* with massive discounts up to 70% OFF!

🛍️ *𝑺𝒉𝒐𝒑 𝒏𝒐𝒘* using your exclusive customer link:

${finalUrl}

🎁 *𝑩𝒐𝒏𝒖𝒔: 𝑭𝒓𝒆𝒆 𝒔𝒉𝒊𝒑𝒑𝒊𝒏𝒈 𝒐𝒏 𝒂𝒍𝒍 𝒐𝒓𝒅𝒆𝒓𝒔!*

Hurry, offers expire soon! ⏰`,
            
            "Stylo Website": `👔 *𝑺𝒕𝒚𝒍𝒐 𝑭𝒂𝒔𝒉𝒊𝒐𝒏 - 𝑷𝒓𝒆𝒎𝒊𝒖𝒎 𝑪𝒐𝒍𝒍𝒆𝒄𝒕𝒊𝒐𝒏 𝑳𝒂𝒖𝒏𝒄𝒉* 👔

Experience luxury redefined!

🌟 *𝑬𝒙𝒄𝒍𝒖𝒔𝒊𝒗𝒆 𝒂𝒄𝒄𝒆𝒔𝒔* to our new premium collection.

👉 *𝑽𝒊𝒆𝒘 𝒕𝒉𝒆 𝒄𝒐𝒍𝒍𝒆𝒄𝒕𝒊𝒐𝒏* at:

${finalUrl}

✨ *𝑬𝒍𝒆𝒈𝒂𝒏𝒄𝒆 𝒎𝒆𝒆𝒕𝒔 𝒔𝒐𝒑𝒉𝒊𝒔𝒕𝒊𝒄𝒂𝒕𝒊𝒏* - Limited edition pieces available.

- Stylo Fashion House`
        };

        // Get message based on website name, or use default
        const message = messages[websiteName] || `🔗 *${websiteName} - Secure Access Link*

Please use the following link to access your account:

${finalUrl}

📱 *Secure login portal* - Click to proceed.

Note: This link is unique to your account and should not be shared.`;

        // WhatsApp URL
        const whatsappUrl = `https://web.whatsapp.com/send?phone=${cleanedNumber}&text=${encodeURIComponent(message)}`;
        
        // Open WhatsApp Web
        window.open(whatsappUrl, 'whatsapp_tab');
    } catch (e) {
        console.error("Error decoding number:", e);
        // Fallback - open link directly
        window.open(finalUrl, '_blank');
    }

    // Reset dropdown
    setTimeout(() => {
        selectElement.value = '';
    }, 2000);
}
</script>
</body>
</html>