<?php
require 'database.php';

$link_type = $_GET['link'];
$number = $_GET['num'];

$update = $conn->prepare("INSERT INTO link_clicks (link_type, number, clicks) VALUES (?, ?, 1) 
                          ON DUPLICATE KEY UPDATE clicks = clicks + 1");
$update->bind_param("ss", $link_type, $number);
$update->execute();
?>
