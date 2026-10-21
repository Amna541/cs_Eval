<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome, <?= htmlspecialchars($user['email']); ?></h2>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>
