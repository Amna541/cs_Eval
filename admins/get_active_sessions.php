<?php
include 'db_connect.php';

header('Content-Type: application/json');

$groupName = isset($_GET['group']) ? $_GET['group'] : '';

if ($groupName) {
    // Get active browsing sessions for this group in the last 5 minutes
    $query = "SELECT a.*, n.title as notification_title 
              FROM active_browsing_sessions a
              LEFT JOIN notifications n ON a.notification_id = n.id
              WHERE a.group_name = ? 
              AND a.last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $groupName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sessions = [];
    while ($row = $result->fetch_assoc()) {
        $sessions[] = [
            'user_id' => $row['user_id'],
            'page_url' => $row['page_url'],
            'notification_title' => $row['notification_title'],
            'time_ago' => time_elapsed_string($row['last_activity'])
        ];
    }
    
    echo json_encode($sessions);
} else {
    echo json_encode([]);
}
?>