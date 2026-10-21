<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "track_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['link']) && isset($_GET['group'])) {
    $link_name = $_GET['link'];
    $group_id = $_GET['group'];

    // Detect user data
    $ip = $_SERVER['REMOTE_ADDR'];
    $device = $_SERVER['HTTP_USER_AGENT'];
    $browser = $_SERVER['HTTP_USER_AGENT'];
    $os = php_uname();

    // Log click in tracking-data
    $stmt = $conn->prepare("INSERT INTO `tracking_data` (link_clicked, os, device, browser, ip_address, timestamp, group_id) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("ssssss", $link_name, $os, $device, $browser, $ip, $group_id);
    $stmt->execute();

    // Update click count
    $stmt = $conn->prepare("UPDATE generated_links SET clicks = clicks + 1 WHERE link LIKE ? AND group_id = ?");
    $stmt->bind_param("ss", $link_name, $group_id);
    $stmt->execute();

    // Redirect user
    $redirect_urls = [
        "easypaisa" => "https://easypaisa.com",
        "askari" => "https://askaribank.com",
        "csd" => "https://csd.gov.pk"
    ];

    header("Location: " . $redirect_urls[$link_name]);
    exit();
}

$conn->close();
?>
