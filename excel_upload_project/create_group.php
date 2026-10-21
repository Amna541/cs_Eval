<?php
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = $_POST['group_name'];

    // Check if group already exists
    $check = $conn->prepare("SELECT * FROM groups WHERE name = ?");
    $check->bind_param("s", $group_name);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "Group name already exists!";
    } else {
        // Insert new group
        $stmt = $conn->prepare("INSERT INTO groups (name) VALUES (?)");
        $stmt->bind_param("s", $group_name);
        if ($stmt->execute()) {
            header("Location: group_page.php?group=" . urlencode($group_name));
        } else {
            echo "Error creating group.";
        }
    }
}
?>
