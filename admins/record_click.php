<?php
require_once "db_connect.php";
require_once "helpers.php";

// Read POST data safely
$groupName   = $_POST['group_name'] ?? '';
$contact     = $_POST['contact'] ?? '';
$group       = $_POST['group'] ?? '';
$browser     = $_POST['browser'] ?? '';
$device      = $_POST['device'] ?? '';
$agent       = $_POST['agent'] ?? '';
$ipAddress   = $_POST['ip_address'] ?? '';
$clickedAt   = date('Y-m-d H:i:s'); // current timestamp
$url         = $_POST['url'] ?? '';
$score       = $_POST['score'] ?? 0;

// Insert into trace_data table
$sql = "INSERT INTO trace_data 
    (group_name, contact, `group`, browser, device, agent, `ip_address`, `clicked_at`, url, score) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssi",
    $groupName,
    $contact,
    $group,
    $browser,
    $device,
    $agent,
    $ipAddress,
    $clickedAt,
    $url,
    $score
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "error" => $stmt->error]);
}
?>
