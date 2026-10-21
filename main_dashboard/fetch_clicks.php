<?php
include 'db_connect.php'; // Ensure this connects to your database

// Define a mapping of URLs to friendly website names
$websiteNames = [
    "easypaisa.php" => "Easypaisa",
    "logout.php" => "Askari Bank",
    "login.php" => "CSD",
    "googlei.php" => "Google",
    "facebook_login.php" => "Facebook",
    "index.php" => "War Zone",
    "hackathon/index.html#about" => "Hackathon",
    "eshop2" => "E-Shop",
    "welcome_stylo.php" => "Stylo",
    "real-Estate-Management-System-main" => "Real Estate"
];

// Query to count clicks per link
$sql = "SELECT url, COUNT(*) as click_count FROM trace_data GROUP BY url";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $url = $row['url'];
    $friendlyName = "Unknown"; // Default name if not found

    // Try to match a known website name
    foreach ($websiteNames as $key => $name) {
        if (strpos($url, $key) !== false) {
            $friendlyName = $name;
            break;
        }
    }

    $data[] = ['website' => $friendlyName, 'click_count' => $row['click_count']];
}

echo json_encode($data);
?>
