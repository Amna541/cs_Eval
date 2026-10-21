<?php
function decryptData($data) {
    return base64_decode(urldecode($data));
}

function storeClickInfo($link, $input) {
    $conn = new mysqli("localhost", "username", "password", "trace_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $stmt = $conn->prepare("INSERT INTO trace_data (email_phone, link_clicked, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $input, $link);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

if (isset($_GET['data']) && isset($_GET['link'])) {
    $decryptedInput = decryptData($_GET['data']);
    $clickedLink = urldecode($_GET['link']);

    storeClickInfo($clickedLink, $decryptedInput);

    header("Location: " . $clickedLink);
    exit();
}
?>
