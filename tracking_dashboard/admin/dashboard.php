<?php
include '../includes/db.php';
include '../includes/functions.php';

$groups = $conn->query("SELECT * FROM groups")->fetchAll(PDO::FETCH_ASSOC);
$tracking_data = $conn->query("SELECT * FROM tracking_data ORDER BY timestamp DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Admin Dashboard</h2>
        
        <div class="mt-4">
            <h4>Create Group</h4>
            <form action="create_group.php" method="POST">
                <input type="text" name="group_id" class="form-control" placeholder="Enter Group ID" required>
                <button type="submit" name="create_group" class="btn btn-primary mt-2">Create</button>
            </form>
        </div>

        <div class="mt-4">
            <h4>Tracking Data</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Group</th>
                        <th>Contact</th>
                        <th>OS</th>
                        <th>Browser</th>
                        <th>IP</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tracking_data as $data): ?>
                        <tr>
                            <td><?= $data['id']; ?></td>
                            <td><?= $data['group_id']; ?></td>
                            <td><?= $data['contact']; ?></td>
                            <td><?= $data['os']; ?></td>
                            <td><?= $data['browser']; ?></td>
                            <td><?= $data['ip']; ?></td>
                            <td><?= $data['timestamp']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
