<?php
require_once "db_connect.php";

$result = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cyber Awareness Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<h1>Cyber Security Awareness Dashboard</h1>

<table border="1" cellpadding="10">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Awareness Score</th>
        <th>Risk Level</th>
        <th>Report</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['awareness_score'] ?></td>
            <td><?= $row['risk_level'] ?></td>
            <td><a href="user_report.php?id=<?= $row['id'] ?>">View</a></td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
