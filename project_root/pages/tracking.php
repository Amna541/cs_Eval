<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$encryptedContact = $_GET['link'] ?? null;
if (!$encryptedContact) {
    die("Error: Invalid tracking link.");
}

$contact = decryptData($encryptedContact);
$service = basename($_SERVER['REQUEST_URI'], '.php');

$stmt = $conn->prepare("INSERT INTO tracing_data (email_phone, link_clicked, timestamp) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $contact, $service);
$stmt->execute();
$stmt->close();

echo "Tracking recorded for: $contact on $service";
?>
