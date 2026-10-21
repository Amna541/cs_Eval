<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_name'])) {
    $group_name = $_POST['group_name'];

    // Prevent duplicate groups
    $stmt = $conn->prepare("SELECT id FROM groups WHERE group_name = ?");
    $stmt->execute([$group_name]);
    $group = $stmt->fetch();

    if (!$group) {
        // Insert new group
        $stmt = $conn->prepare("INSERT INTO groups (group_name) VALUES (?)");
        $stmt->execute([$group_name]);
        $group_id = $conn->lastInsertId();
    } else {
        $group_id = $group['id'];
    }

    $_SESSION['group_id'] = $group_id;
    $_SESSION['group_name'] = $group_name;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Create a Tracking Group</h2>
        <form method="post">
            <div class="mb-3">
                <label for="group_name" class="form-label">Group Name</label>
                <input type="text" class="form-control" name="group_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Group</button>
        </form>

        <?php if (isset($_SESSION['group_id'])): ?>
            <div class="mt-4">
                <h3>Options</h3>
                <a href="add_email_phone.php" class="btn btn-success">Enter Email</a>
                <a href="add_email_phone.php" class="btn btn-info">Enter Phone Number</a>
                <a href="upload_excel.php" class="btn btn-warning">Upload Excel</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
