<?php
// send_whatsapp.php
require_once 'db_connect.php';

function sendWhatsAppMessage($phone, $message) {
    // Escape arguments for security
    $escapedPhone = escapeshellarg($phone);
    $escapedMessage = escapeshellarg($message);
    
    // Build the command with correct path
    $command = "python F:\\xampp\\htdocs\\admins\\whatsapp_sender.py " . $escapedPhone . " " . $escapedMessage . " 2>&1";
    
    // Execute the command
    $output = shell_exec($command);
    
    // Log the result
    error_log("WhatsApp sending result: " . $output);
    
    // Check if message was sent successfully
    return strpos($output, "SUCCESS:") !== false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (!empty($phone) && !empty($message)) {
        $success = sendWhatsAppMessage($phone, $message);
        if ($success) {
            echo json_encode([
                'status' => 'success',
                'message' => 'WhatsApp message sent successfully'
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to send WhatsApp message'
            ]);
            exit;
        }
    }
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing phone number or message'
    ]);
    exit;
}