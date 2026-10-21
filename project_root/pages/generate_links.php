<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Debugging: Check if POST data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} else {
    die("Error: No data received. Make sure the form is using method='POST'.");
}

$contact = $_POST['contact_info'] ?? null;

if (!$contact) {
    die("Error: No contact information provided.");
}

$services = ['easypaisa', 'askari', 'csd'];
$links = [];

foreach ($services as $service) {
    $links[$service] = generateTrackingLink($contact, $service);
}

?>
