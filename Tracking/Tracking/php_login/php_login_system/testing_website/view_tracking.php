<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "tracking_db";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tracking data (Removed clicked_at)
$sql = "SELECT * FROM tracking ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h2>📊 Stored Tracking Data</h2>
    <table>
        <tr>
            
            <th>Phone</th>
            <th>IP Address</th>
            <th>Device</th>
            <th>Browser</th>
            <th>OS</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        
                        <td>{$row['phone']}</td>
                        <td>{$row['ip_address']}</td>
                        <td>{$row['device']}</td>
                        <td>{$row['browser']}</td>
                        <td>{$row['os']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No data found</td></tr>";
        }
        ?>
    </table>
</body>
</html>

<?php $conn->close(); ?>
