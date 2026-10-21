<?php
require_once "db_connect.php";

$id = intval($_GET['id'] ?? 0);
$result = $conn->query("SELECT * FROM trace_data WHERE id = $id");

if (!$result || $result->num_rows === 0) {
    die("No data found for this record.");
}

$row = $result->fetch_assoc();

// If country/city not stored yet, fetch from IP API
if (empty($row['country']) || empty($row['city'])) {
    $ip = $row['ip_address'];

    // Call IP geolocation API
    $geoData = @file_get_contents("http://ip-api.com/json/$ip");
    if ($geoData) {
        $geo = json_decode($geoData, true);
        if ($geo && $geo['status'] === 'success') {
            $country = $geo['country'] ?? 'Unknown';
            $city = $geo['city'] ?? 'Unknown';

            // Save back to DB for future requests
            $updateStmt = $conn->prepare("UPDATE trace_data SET country=?, city=? WHERE id=?");
            $updateStmt->bind_param("ssi", $country, $city, $id);
            $updateStmt->execute();

            $row['country'] = $country;
            $row['city'] = $city;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>User Report</title>
</head>
<body>
    <h1>Awareness Report</h1>
    <p><strong>Email:</strong> <?= htmlspecialchars($row['contact'] ?? 'N/A') ?></p>
    <p><strong>Country:</strong> <?= htmlspecialchars($row['country'] ?? 'Unknown') ?></p>
    <p><strong>City:</strong> <?= htmlspecialchars($row['city'] ?? 'Unknown') ?></p>
</body>
</html>
