<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_name = $_POST['group_name'] ?? null;

    if (!$group_name) {
        die("Error: Group name is required.");
    }

    header("Location: generate_links.php?group=" . urlencode($group_name));
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enter Contacts</title>
</head>
<body>
    <h1>Enter Contact Details</h1>
    <form method="POST" action="generate_links.php">
    <label>Enter Contact:</label>
    <input type="text" name="contact" required>
    <button type="submit">Generate Links</button>
</form>

</body>
</html>
