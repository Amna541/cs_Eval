<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tracking_db"); // Updated DB name

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle group creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['group_name'])) {
    $_SESSION['group_name'] = $_POST['group_name'];
}

$groupName = isset($_SESSION['group_name']) ? $_SESSION['group_name'] : "No Group Selected";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

    <h2 class="text-center">Group Dashboard</h2>

    <form method="POST" class="mb-4">
        <label>Enter Group Name:</label>
        <input type="text" name="group_name" class="form-control" required>
        <button type="submit" class="btn btn-primary mt-2">Create Group</button>
    </form>

    <h3>Group: <?php echo htmlspecialchars($groupName); ?></h3>

    <form action="generate_links.php" method="POST">
        <label>Enter Email or Phone Number:</label>
        <input type="text" name="contact" class="form-control" required>
        <button type="submit" class="btn btn-success mt-2">Generate Links</button>
    </form>

</body>
</html>
