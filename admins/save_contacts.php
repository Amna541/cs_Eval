<?php
// save_contacts.php - Save REAL contacts to database
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once 'F:\xampp\htdocs\admins\db_connect.php';

// Get POST data
$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

if (!$data || !isset($data['contacts'])) {
    echo json_encode(['success' => false, 'message' => 'No contact data received']);
    exit;
}

$contacts = $data['contacts'];
$timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$source = $data['source'] ?? 'unknown';
$isReal = isset($data['is_real']) ? (int)$data['is_real'] : 0;

// Create table
$createTable = "
CREATE TABLE IF NOT EXISTS user_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_name VARCHAR(255),
    contact_phone VARCHAR(50),
    ip_address VARCHAR(50),
    user_agent TEXT,
    source VARCHAR(50),
    is_real TINYINT(1) DEFAULT 0,
    created_at DATETIME,
    session_id VARCHAR(100),
    INDEX idx_phone (contact_phone),
    INDEX idx_ip (ip_address)
)";

if (!$conn->query($createTable)) {
    echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $conn->error]);
    exit;
}

// Start session
session_start();
$sessionId = session_id();

$successCount = 0;
$failedCount = 0;

foreach ($contacts as $contact) {
    $name = $contact['name'] ?? 'Unknown';
    $phone = $contact['phone'] ?? 'No number';
    
    $stmt = $conn->prepare("INSERT INTO user_contacts (contact_name, contact_phone, ip_address, user_agent, source, is_real, created_at, session_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $name, $phone, $ip, $userAgent, $source, $isReal, $timestamp, $sessionId);
    
    if ($stmt->execute()) {
        $successCount++;
    } else {
        $failedCount++;
        error_log("Failed to insert contact: " . $stmt->error);
    }
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'message' => "$successCount contacts saved successfully" . ($failedCount > 0 ? ", $failedCount failed" : ""),
    'total' => count($contacts),
    'saved' => $successCount,
    'failed' => $failedCount,
    'source' => $source,
    'is_real' => $isReal ? 'Yes' : 'No'
]);

$conn->close();
?>