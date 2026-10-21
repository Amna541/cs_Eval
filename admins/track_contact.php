<?php
// track_contact.php - Track contact access decisions
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once 'F:\xampp\htdocs\admins\db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'No status received']);
    exit;
}

$status = $data['status'];
$timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent = $data['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$isReal = isset($data['isReal']) ? (int)$data['isReal'] : 0;
$contactsCount = $data['contactsCount'] ?? 0;

// Create table
$createTable = "
CREATE TABLE IF NOT EXISTS contact_access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(50),
    ip_address VARCHAR(50),
    user_agent TEXT,
    is_real TINYINT(1) DEFAULT 0,
    contacts_count INT DEFAULT 0,
    created_at DATETIME,
    session_id VARCHAR(100),
    INDEX idx_status (status),
    INDEX idx_ip (ip_address)
)";

if (!$conn->query($createTable)) {
    echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $conn->error]);
    exit;
}

session_start();
$sessionId = session_id();

$stmt = $conn->prepare("INSERT INTO contact_access_log (status, ip_address, user_agent, is_real, contacts_count, created_at, session_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiiss", $status, $ip, $userAgent, $isReal, $contactsCount, $timestamp, $sessionId);

$result = $stmt->execute();

echo json_encode([
    'success' => $result,
    'message' => $result ? 'Tracked successfully' : 'Tracking failed: ' . $stmt->error
]);

$stmt->close();
$conn->close();
?>