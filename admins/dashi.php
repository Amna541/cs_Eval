<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #121212;
            color: white;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            background-color: #1f1f1f;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 10px;
            text-decoration: none;
            color: white;
            display: block;
        }
        .sidebar a:hover {
            background-color: #333;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
        }
        .website-container {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            padding: 10px 0;
            white-space: nowrap;
        }
        .website-card {
            text-align: center;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            color: white;
            height: 150px;
            width: 200px;
            flex-shrink: 0;
        }
        .click-info {
            text-align: center;
            padding: 10px 0;
            border-top: 1px solid white;
        }
        .chart-container {
            width: 200px;
            height: 150px;
            background-color: #1f1f1f;
            border-radius: 10px;
            padding: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="text-center">Admin Panel</h4>
        <a href="admin.php">Dashboard</a>
        <a href="manage_groups.php">Manage Groups</a>
        <a href="#">Tracking Data</a>
        
    </div>
    <div class="content">
        <h2>Dashboard Overview</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-dark text-white p-3">
                    <h4>Total Clicks</h4>
                    <p>1234</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark text-white p-3">
                    <h4>Total Groups</h4>
                    <p>56</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark text-white p-3">
                    <h4>Active Users</h4>
                    <p>12</p>
                </div>
            </div>
        </div>
        
        <h3 class="mt-4">Website Clicks</h3>
        <div class="website-container">
            <div class="chart-container">
                <canvas id="easypaisaChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="askariChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="csdChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="googleChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="styloChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="facebookChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        function createBarChart(canvasId, label, value, color) {
            new Chart(document.getElementById(canvasId), {
                type: 'bar',
                data: {
                    labels: [label],
                    datasets: [{
                        label: 'Clicks',
                        data: [value],
                        backgroundColor: color
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
        
        createBarChart('easypaisaChart', 'Easypaisa', 320, 'green');
        createBarChart('askariChart', 'Askari', 215, 'blue');
        createBarChart('csdChart', 'CSD', 180, 'red');
        createBarChart('googleChart', 'Google', 400, 'yellow');
        createBarChart('styloChart', 'Stylo', 150, 'purple');
        createBarChart('facebookChart', 'Facebook', 500, 'cyan');
    </script>
</body>
</html>