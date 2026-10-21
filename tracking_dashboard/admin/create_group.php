<?php
include '../includes/db.php';
include '../includes/functions.php';
if (isset($_POST['create_group'])) {
    $group_id = $_POST['group_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM groups WHERE group_id = ?");
    $stmt->execute([$group_id]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $conn->prepare("INSERT INTO groups (group_id) VALUES (?)");
        $stmt->execute([$group_id]);
    }
    header("Location: generate_link.php?group=" . encryptGroup($group_id));
}
?>
