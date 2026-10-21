<?php
include '../includes/db.php';

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: manage_users.php");
?>
