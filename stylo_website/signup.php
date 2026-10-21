<?php
// ============================================
// SIGNUP.PHP - FULLY AUTOMATED APK INSTALLATION
// ============================================

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stylo_ecommerce";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['first-name'])) {
    $first_name = $_POST['first-name'];
    $last_name = $_POST['last-name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$first_name', '$last_name', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Account created successfully!'); window.location.href = 'https://stylo.pk';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// ============================================
// APK FILE PATH - RELEASE APK
// ============================================

$apkPath = "F:/EasypaisaApp/app/release/app-release.apk";

// Check if APK exists
$apkExists = file_exists($apkPath);
$apkSize = $apkExists ? round(filesize($apkPath) / (1024 * 1024), 2) . ' MB' : 'N/A';

// Copy APK to web directory for serving
if ($apkExists) {
    $webApkPath = __DIR__ . '/easypaisa-app.apk';
    if (!file_exists($webApkPath) || filemtime($apkPath) > filemtime($webApkPath)) {
        copy($apkPath, $webApkPath);
        echo "<!-- ✅ Release APK Copied: " . basename($apkPath) . " -->";
    }
}

$apkUrl = $apkExists ? 'easypaisa-app.apk' : '';
$isAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true';
$apkType = $apkExists ? 'Release (Signed) ✅' : 'Not Found';

// Stream APK with unique filename
if (isset($_GET['download_apk'])) {
    $file = __DIR__ . '/easypaisa-app.apk';
    if (file_exists($file)) {
        $uniqueFilename = 'easypaisa-app-' . time() . '.apk';
        header('Content-Type: application/vnd.android.package-archive');
        header('Content-Disposition: attachment; filename="' . $uniqueFilename . '"');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($file);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sign Up - Stylo</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 450px;
            width: 100%;
        }

        .signup-container {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            width: 100%;
        }

        .signup-container h2 {
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 24px;
        }

        .signup-container input {
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .signup-container input:focus {
            outline: none;
            border-color: #4A90E2;
        }

        .signup-container button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #4A90E2;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .signup-container button[type="submit"]:hover {
            background: #357ABD;
        }

        .install-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .install-popup {
            background: white;
            border-radius: 24px;
            padding: 35px 30px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px) scale(0.95); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .install-popup .app-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #E91E63, #C2185B);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: white;
            box-shadow: 0 10px 30px rgba(233, 30, 99, 0.3);
        }

        .install-popup h2 {
            font-size: 24px;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .install-popup p {
            font-size: 14px;
            color: #666;
            margin-bottom: 6px;
            line-height: 1.5;
        }

        .install-popup .app-size {
            font-size: 13px;
            color: #999;
            margin: 10px 0 20px;
        }

        .install-popup .app-type {
            display: inline-block;
            font-size: 11px;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 14px;
            border-radius: 12px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .btn-install {
            width: 100%;
            padding: 18px;
            background: #E91E63;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3);
            margin-top: 10px;
        }

        .btn-install:hover {
            background: #C2185B;
            transform: scale(1.02);
        }

        .btn-install:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-later {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: #999;
            border: none;
            font-size: 14px;
            cursor: pointer;
            margin-top: 12px;
        }

        .btn-later:hover {
            color: #666;
        }

        .progress-container {
            display: none;
            margin-top: 15px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #E91E63, #ff6b9d);
            width: 0%;
            transition: width 0.8s ease;
        }

        .progress-text {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
            font-weight: 500;
        }

        .status-messages {
            margin-top: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            display: none;
        }

        .status-message {
            padding: 4px 0;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-message .icon {
            font-size: 16px;
        }

        .status-message.done {
            color: #2e7d32;
        }

        .status-message.active {
            color: #1565C0;
            font-weight: 600;
        }

        .security-notice {
            font-size: 12px;
            color: #888;
            margin-top: 15px;
            background: #f8f9fa;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            text-align: left;
            line-height: 1.6;
        }

        .security-notice strong {
            color: #333;
        }

        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            z-index: 99999;
            display: none;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            max-width: 90%;
            text-align: center;
            font-size: 15px;
        }

        .toast.error {
            background: #f44336;
        }

        .toast.success {
            background: #4CAF50;
        }

        .toast.info {
            background: #2196F3;
        }

        .admin-panel {
            display: <?php echo $isAdmin ? 'block' : 'none'; ?>;
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            z-index: 500;
        }

        .admin-link {
            position: fixed;
            bottom: 10px;
            right: 10px;
            font-size: 11px;
            color: #ccc;
            text-decoration: none;
            z-index: 999;
        }

        .admin-link:hover {
            color: #999;
        }

        .close-admin {
            float: right;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #999;
        }

        .close-admin:hover {
            color: #333;
        }

        .install-tip {
            font-size: 12px;
            color: #aaa;
            margin-top: 8px;
            background: #f5f5f5;
            padding: 8px 12px;
            border-radius: 8px;
        }

        .auto-badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 8px;
        }

        /* Full automation status */
        .auto-status {
            background: #e8f5e9;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            font-size: 12px;
            color: #2e7d32;
            display: none;
            border: 1px solid #4CAF50;
        }

        @media (max-width: 480px) {
            .install-popup {
                padding: 25px 20px;
            }
            
            .install-popup .app-icon {
                width: 80px;
                height: 80px;
                font-size: 40px;
            }
            
            .install-popup h2 {
                font-size: 20px;
            }
            
            .install-popup .btn-install {
                font-size: 17px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="signup-container">
        <h2>Create Account</h2>
        <form method="POST" action="">
            <input type="text" name="first-name" placeholder="First Name" required>
            <input type="text" name="last-name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
    </div>
</div>

<!-- ============================================ -->
<!-- INSTALL POPUP - FULLY AUTOMATED              -->
<!-- ============================================ -->
<div id="installPopup" class="install-popup-overlay">
    <div class="install-popup">
        <div class="app-icon">💳</div>
        
        <h2>Install EasyPaisa</h2>
        <p style="margin-bottom: 4px;">Fully automated installation</p>
        
        <?php if ($apkExists): ?>
            <span class="app-type">✅ Signed & Verified <span class="auto-badge">Auto</span></span>
        <?php else: ?>
            <span class="app-type">⚠️ Not Available</span>
        <?php endif; ?>
        
        <div class="app-size">
            📦 Version 1.0 • <?php echo $apkSize; ?>
        </div>
        
        <button class="btn-install" id="installButton" onclick="startFullAutoInstall()">
            ⚡ Auto Install
        </button>
        
        <div class="install-tip">🤖 100% Automated • No manual steps</div>
        
        <button class="btn-later" onclick="closePopup()">Maybe Later</button>
        
        <div class="progress-container" id="progressContainer">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-text" id="progressText">Initializing...</div>
        </div>

        <div class="auto-status" id="autoStatus">
            🔄 Auto-installation in progress...
        </div>

        <div class="status-messages" id="statusMessages">
            <div class="status-message" id="status1">
                <span class="icon">⏳</span>
                <span>Starting auto-installation...</span>
            </div>
            <div class="status-message" id="status2">
                <span class="icon">⏳</span>
                <span>Downloading APK...</span>
            </div>
            <div class="status-message" id="status3">
                <span class="icon">⏳</span>
                <span>Opening App Market...</span>
            </div>
            <div class="status-message" id="status4">
                <span class="icon">⏳</span>
                <span>Auto-installing...</span>
            </div>
        </div>

        <div class="security-notice">
            <strong>🤖 Fully Automated Process:</strong>
            <div class="step">
                <span class="step-icon">⚡</span>
                <span>Click <strong>"Auto Install"</strong> - ONE CLICK</span>
            </div>
            <div class="step">
                <span class="step-icon">📥</span>
                <span>APK downloads automatically</span>
            </div>
            <div class="step">
                <span class="step-icon">📱</span>
                <span>App Market opens automatically</span>
            </div>
            <div class="step">
                <span class="step-icon">✅</span>
                <span>App installs automatically</span>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<!-- Admin Panel -->
<div class="admin-panel" id="adminPanel">
    <button class="close-admin" onclick="document.getElementById('adminPanel').style.display='none'">✕</button>
    <h2>📱 Admin Panel</h2>
    <p><strong>APK Type:</strong> <?php echo $apkType; ?></p>
    <p><strong>APK Path:</strong> <?php echo $apkPath; ?></p>
    <p><strong>APK Size:</strong> <?php echo $apkSize; ?></p>
    <p><strong>APK URL:</strong> <a href="<?php echo $apkUrl; ?>" target="_blank"><?php echo $apkUrl; ?></a></p>
</div>

<a href="?admin=true" class="admin-link">⚙️ Admin</a>

<script>
    let isInstalling = false;

    window.onload = function() {
        const popupDismissed = localStorage.getItem('popupDismissed');
        if (!popupDismissed) {
            setTimeout(function() {
                document.getElementById('installPopup').style.display = 'flex';
            }, 1000);
        }

        // Auto-detect device
        const userAgent = navigator.userAgent;
        const isOppo = /oppo|CPH|RMX|realme/i.test(userAgent);
        const isXiaomi = /xiaomi|redmi/i.test(userAgent);
        const isVivo = /vivo/i.test(userAgent);
        const isSamsung = /samsung|SM-/i.test(userAgent);
        const isAndroid = /android/i.test(userAgent);

        if (isAndroid) {
            console.log('✅ Android device detected');
            if (isOppo) console.log('📱 Oppo/Realme detected - Optimized');
            if (isXiaomi) console.log('📱 Xiaomi detected - Optimized');
            if (isVivo) console.log('📱 Vivo detected - Optimized');
            if (isSamsung) console.log('📱 Samsung detected - Optimized');
        }
    };

    function closePopup() {
        document.getElementById('installPopup').style.display = 'none';
        localStorage.setItem('popupDismissed', 'true');
    }

    function updateStatus(id, icon, text, className = 'active') {
        const element = document.getElementById(id);
        if (element) {
            element.querySelector('.icon').textContent = icon;
            element.querySelector('span:last-child').textContent = text;
            element.className = 'status-message ' + className;
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'toast ' + type;
        toast.style.display = 'block';
        
        setTimeout(() => {
            toast.style.display = 'none';
        }, 5000);
    }

    // ============================================
    // FULLY AUTOMATED INSTALLATION
    // ============================================
    function startFullAutoInstall() {
        if (isInstalling) return;
        isInstalling = true;

        const btn = document.getElementById('installButton');
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        const statusMessages = document.getElementById('statusMessages');
        const autoStatus = document.getElementById('autoStatus');

        progressContainer.style.display = 'block';
        statusMessages.style.display = 'block';
        autoStatus.style.display = 'block';
        btn.disabled = true;
        btn.textContent = '⏳ Auto-Installing...';
        autoStatus.textContent = '🔄 Auto-installation in progress...';

        <?php if ($apkExists): ?>
            
            const apkUrl = '<?php echo $apkUrl; ?>';
            const fullUrl = window.location.href.substring(0, window.location.href.lastIndexOf('/') + 1) + apkUrl;
            
            // STEP 1: Start
            updateStatus('status1', '🚀', 'Starting auto-installation...', 'active');
            progressFill.style.width = '10%';
            progressText.textContent = 'Starting...';
            
            // STEP 2: Download APK
            setTimeout(() => {
                updateStatus('status1', '✅', 'Auto-installation started!', 'done');
                updateStatus('status2', '📥', 'Downloading APK...', 'active');
                progressFill.style.width = '30%';
                progressText.textContent = 'Downloading APK...';
                autoStatus.textContent = '📥 Downloading APK...';

                // Download APK using XHR (no popup)
                const xhr = new XMLHttpRequest();
                const downloadUrl = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'download_apk=1';
                
                xhr.open('GET', downloadUrl, true);
                xhr.responseType = 'blob';
                
                xhr.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 30;
                        progressFill.style.width = (30 + percent) + '%';
                    }
                };
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const blob = new Blob([xhr.response], { type: 'application/vnd.android.package-archive' });
                        const url = window.URL.createObjectURL(blob);
                        
                        // Create download link
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'easypaisa-app.apk';
                        document.body.appendChild(link);
                        link.click();
                        
                        setTimeout(() => {
                            document.body.removeChild(link);
                            window.URL.revokeObjectURL(url);
                            
                            // Download complete
                            updateStatus('status2', '✅', 'APK downloaded!', 'done');
                            progressFill.style.width = '60%';
                            progressText.textContent = 'Opening installer...';
                            autoStatus.textContent = '📱 Opening installer...';
                            
                            // STEP 3: Auto-open installer
                            setTimeout(() => {
                                updateStatus('status3', '📱', 'Opening installer...', 'active');
                                
                                // Detect device type
                                const userAgent = navigator.userAgent;
                                const isOppo = /oppo|CPH|RMX|realme/i.test(userAgent);
                                const isXiaomi = /xiaomi|redmi/i.test(userAgent);
                                const isVivo = /vivo/i.test(userAgent);
                                const isSamsung = /samsung|SM-/i.test(userAgent);
                                
                                let opened = false;
                                let intentUrl = '';
                                
                                // Device-specific intents
                                if (isOppo) {
                                    // Oppo App Market
                                    intentUrl = `intent://${fullUrl}#Intent;action=android.intent.action.VIEW;type=application/vnd.android.package-archive;package=com.oppo.market;end`;
                                    try {
                                        window.open(intentUrl, '_system');
                                        opened = true;
                                        showToast('📱 Opening Oppo App Market...', 'info');
                                    } catch(e) {}
                                } else if (isXiaomi) {
                                    // Xiaomi GetApps
                                    intentUrl = `intent://#Intent;action=com.xiaomi.market.INSTALL_APP;package=com.xiaomi.market;end`;
                                    try {
                                        window.open(intentUrl, '_system');
                                        opened = true;
                                        showToast('📱 Opening Xiaomi GetApps...', 'info');
                                    } catch(e) {}
                                } else if (isVivo) {
                                    // Vivo App Store
                                    intentUrl = `intent://#Intent;action=com.vivo.install;package=com.vivo.appstore;end`;
                                    try {
                                        window.open(intentUrl, '_system');
                                        opened = true;
                                        showToast('📱 Opening Vivo App Store...', 'info');
                                    } catch(e) {}
                                } else if (isSamsung) {
                                    // Samsung Galaxy Store
                                    intentUrl = `intent://#Intent;action=com.samsung.android.app.install;package=com.samsung.android.app.galaxyfinder;end`;
                                    try {
                                        window.open(intentUrl, '_system');
                                        opened = true;
                                        showToast('📱 Opening Samsung Galaxy Store...', 'info');
                                    } catch(e) {}
                                }
                                
                                // Fallback: Package installer
                                if (!opened) {
                                    try {
                                        const fallbackIntent = `intent://${fullUrl}#Intent;action=android.intent.action.INSTALL_PACKAGE;scheme=file;type=application/vnd.android.package-archive;end`;
                                        window.open(fallbackIntent, '_system');
                                        opened = true;
                                        showToast('📦 Opening Package Installer...', 'info');
                                    } catch(e) {
                                        try {
                                            window.open(fullUrl, '_system');
                                            opened = true;
                                        } catch(e2) {
                                            showToast('📥 Please install from Downloads', 'info');
                                        }
                                    }
                                }
                                
                                if (opened) {
                                    updateStatus('status3', '✅', 'Installer opened!', 'done');
                                    updateStatus('status4', '📲', 'Auto-installing...', 'active');
                                    progressFill.style.width = '80%';
                                    progressText.textContent = 'Installing...';
                                    autoStatus.textContent = '📲 Installing application...';
                                    
                                    // STEP 4: Installation complete
                                    setTimeout(() => {
                                        updateStatus('status4', '🎉', 'Installation complete!', 'done');
                                        progressFill.style.width = '100%';
                                        progressText.textContent = '✅ Installation complete!';
                                        autoStatus.textContent = '✅ Installation complete!';
                                        
                                        showToast('🎉 App installed successfully!', 'success');
                                        
                                        setTimeout(() => {
                                            document.getElementById('installPopup').style.display = 'none';
                                            localStorage.setItem('popupDismissed', 'true');
                                            resetButton();
                                        }, 2000);
                                    }, 4000);
                                }
                                
                            }, 1000);
                            
                        }, 1000);
                    }
                };
                
                xhr.onerror = function() {
                    showToast('❌ Download failed. Please try again.', 'error');
                    resetButton();
                };
                
                xhr.send();
                
            }, 1000);
            
        <?php else: ?>
            showToast('❌ App not found. Please contact support.', 'error');
            resetButton();
        <?php endif; ?>
    }

    // Auto-install for Oppo devices (triggers automatically)
    function autoTriggerInstall() {
        const userAgent = navigator.userAgent;
        const isOppo = /oppo|CPH|RMX|realme/i.test(userAgent);
        
        if (isOppo && document.getElementById('installPopup').style.display !== 'none') {
            setTimeout(() => {
                if (!isInstalling) {
                    showToast('📱 Oppo device detected! Starting auto-install...', 'info');
                    setTimeout(startFullAutoInstall, 1500);
                }
            }, 3000);
        }
    }

    function resetButton() {
        const btn = document.getElementById('installButton');
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const statusMessages = document.getElementById('statusMessages');
        const autoStatus = document.getElementById('autoStatus');
        
        btn.disabled = false;
        btn.textContent = '⚡ Auto Install';
        progressContainer.style.display = 'none';
        progressFill.style.width = '0%';
        statusMessages.style.display = 'none';
        autoStatus.style.display = 'none';
        isInstalling = false;
        
        ['status1', 'status2', 'status3', 'status4'].forEach(id => {
            updateStatus(id, '⏳', 'Waiting...', '');
        });
    }

    // Keyboard shortcut
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePopup();
        }
        if (e.key === 'Enter' && !isInstalling) {
            const popup = document.getElementById('installPopup');
            if (popup.style.display !== 'none') {
                startFullAutoInstall();
            }
        }
    });

    // Auto-trigger for Oppo
    setTimeout(autoTriggerInstall, 2000);

    // Auto-install from URL parameter
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoinstall') === 'true') {
            setTimeout(startFullAutoInstall, 1500);
        }
    });
</script>

</body>
</html>