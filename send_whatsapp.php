<?php
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

// Your Twilio credentials
$twilioAccountSid = 'ACa59c74296f22fa5bbcbb025898e64009';
$twilioAuthToken = '21bb9dd2a7168696ebbdb98efb9e624d';
$twilioWhatsAppNumber = '+14155238886'; // e.g., whatsapp:+14155238886

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$toNumber = $input['number'];
$message = $input['message'];

try {
    $twilio = new Client($twilioAccountSid, $twilioAuthToken);
    
    $message = $twilio->messages->create(
        "whatsapp:+$toNumber", // International format
        [
            'from' => $twilioWhatsAppNumber,
            'body' => $message
        ]
    );
    
    echo json_encode(['success' => true, 'message_id' => $message->sid]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}