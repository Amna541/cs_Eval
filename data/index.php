<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h2>Enter Group Number</h2>
        <form method="POST" action="process_group.php">
            <div class="mb-3">
                <input type="text" class="form-control" name="group_number" placeholder="Enter Group Number" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
    
    <div class="container mt-5">
        <h2>Tracking Data</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email/Phone</th>
                    <th>Link Clicked</th>
                    <th>OS</th>
                    <th>Device</th>
                    <th>Browser</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Connect to database
                include 'db_connect.php';
                $result = $conn->query("SELECT * FROM tracking_data");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['email_phone']}</td>
                        <td>{$row['link_clicked']}</td>
                        <td>{$row['os']}</td>
                        <td>{$row['device']}</td>
                        <td>{$row['browser']}</td>
                        <td>{$row['ip_address']}</td>
                        <td>{$row['timestamp']}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <div class="container mt-5">
        <h2>Clicks Per Link</h2>
        <canvas id="clickChart"></canvas>
    </div>
    
    <script>
        fetch('fetch_click_data.php')
        .then(response => response.json())
        .then(data => {
            new Chart(document.getElementById("clickChart"), {
                type: 'bar',
                data: {
                    labels: ['Easypaisa', 'Askari', 'CSD'],
                    datasets: [{
                        label: 'Number of Clicks',
                        data: data,
                        backgroundColor: ['green', 'blue', 'red']
                    }]
                }
            });
        });
    </script>
</body>
</html>
