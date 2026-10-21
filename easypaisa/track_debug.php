<?php
// track_debug.php - For debugging tracking issues
session_start();
date_default_timezone_set('Asia/Karachi');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'trace_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    error_log("DEBUG: DB Connection failed: " . $conn->connect_error);
    exit();
}

// Log all GET parameters
error_log("=== DEBUG TRACKING ===");
error_log("GET Parameters: " . print_r($_GET, true));
error_log("Session Data: " . print_r($_SESSION, true));

// Check if short code exists
if (isset($_GET['sc'])) {
    $shortCode = $_GET['sc'];
    
    $stmt = $conn->prepare("SELECT encrypted_data FROM short_codes WHERE short_code = ?");
    if ($stmt) {
        $stmt->bind_param("s", $shortCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        error_log("Short code query result: " . $result->num_rows . " rows");
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            error_log("Found short code data");
            
            // Try to insert a test record
            $test_sql = "INSERT INTO trace_data (contact, group_name, url, clicked_at) VALUES (?, ?, ?, NOW())";
            $test_stmt = $conn->prepare($test_sql);
            
            if ($test_stmt) {
                $contact = 'TEST_' . time();
                $group = 'Debug_Group';
                $url = $_SERVER['HTTP_REFERER'] ?? 'direct';
                
                $test_stmt->bind_param("sss", $contact, $group, $url);
                
                if ($test_stmt->execute()) {
                    error_log("DEBUG SUCCESS: Test record inserted with ID: " . $conn->insert_id);
                } else {
                    error_log("DEBUG ERROR: " . $test_stmt->error);
                }
                $test_stmt->close();
            }
        }
        $stmt->close();
    }
}

$conn->close();
echo "Debug completed. Check error logs.";
?>