<?php
require 'database.php';

$query = "SELECT link_type, SUM(clicks) as total FROM link_clicks GROUP BY link_type";
$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
