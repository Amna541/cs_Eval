<?php
session_start();
date_default_timezone_set('Asia/Karachi');

// Number URL se uthayein (agar ho)
if (isset($_GET['n']) && $_GET['n'] !== '') {
    $decoded = base64_decode($_GET['n'], true);
    if ($decoded !== false) {
        $_SESSION['decoded_number'] = $decoded; // Session mein save
    }
    $_SESSION['group_name'] = $_GET['group_name'] ?? 'Unknown';
    $_SESSION['screen_width'] = $_GET['width'] ?? '1536';
    $_SESSION['screen_height'] = $_GET['height'] ?? '864';
}

// Ab asli fwo.php par redirect karein (bina kisi query ke)
header("Location: /fwo.php");
exit;
?>
