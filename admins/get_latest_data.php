<?php
header('Content-Type: application/json');
include 'db_connect.php';

// Function to safely fetch data
function fetchData($conn, $query) {
    $result = $conn->query($query);
    if (!$result) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get current timestamp for the response
$response = [
    'timestamp' => date('Y-m-d H:i:s')
];

// 1. Get total clicks
$totalQuery = "SELECT COUNT(*) as total FROM trace_data";
$totalResult = $conn->query($totalQuery);
$response['totalClicks'] = (int)$totalResult->fetch_assoc()['total'];

// 2. Get group counts
$groupQuery = "SELECT group_name, COUNT(*) as count FROM trace_data GROUP BY group_name";
$groupResult = $conn->query($groupQuery);
$groupCounts = [];
while ($row = $groupResult->fetch_assoc()) {
    $groupCounts[$row['group_name']] = (int)$row['count'];
}

$response['groupLabels'] = array_keys($groupCounts);
$response['groupValues'] = array_values($groupCounts);

// 3. Get top URLs
$urlQuery = "SELECT url, COUNT(*) as count FROM trace_data GROUP BY url ORDER BY count DESC LIMIT 10";
$urlResult = $conn->query($urlQuery);
$topUrls = [];
while ($row = $urlResult->fetch_assoc()) {
    $topUrls[] = [
        'url' => $row['url'],
        'count' => (int)$row['count']
    ];
}

$response['topUrlLabels'] = array_column($topUrls, 'url');
$response['topUrlValues'] = array_column($topUrls, 'count');

// 4. Get recent activities (last 5)
$activityQuery = "SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 5";
$response['recentActivities'] = fetchData($conn, $activityQuery);

// 5. Get click locations (sample data - replace with your actual geolocation data)
$locationQuery = "SELECT 
    COUNT(*) as clicks,
    country,
    region,
    city,
    latitude,
    longitude
    FROM click_locations 
    GROUP BY country, region, city, latitude, longitude
    ORDER BY clicks DESC
    LIMIT 50";

$locations = fetchData($conn, $locationQuery);
$response['locations'] = $locations;

// Close connection
$conn->close();

// Return JSON response
echo json_encode($response);
?>