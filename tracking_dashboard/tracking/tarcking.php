<?php
include '../includes/db.php';
include '../includes/functions.php';

if (isset($_GET['group']) && isset($_GET['contact'])) {
    $group_id = decryptGroup($_GET['group']);
    $contact = $_GET['contact'];
    $os = php_uname('s');
    $browser = $_SERVER['HTTP_USER_AGENT'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("INSERT INTO tracking_data (group_id, contact, os, browser, ip) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$group_id, $contact, $os, $browser, $ip]);

    echo "Tracking Data Captured!";
}
?>
