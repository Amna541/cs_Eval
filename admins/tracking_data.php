<?php
include 'db_connect.php';
$filterValue = isset($_GET['group_name']) ? $_GET['group_name'] : '';

// Database queries remain the same
$totalQuery = "SELECT COUNT(*) as total FROM trace_data";
$totalResult = $conn->query($totalQuery);
$totalClicks = $totalResult->fetch_assoc()['total'];

$groupQuery = "SELECT group_name, COUNT(*) as count FROM trace_data GROUP BY group_name";
$groupResult = $conn->query($groupQuery);
$groupCounts = [];
while ($row = $groupResult->fetch_assoc()) {
    $groupCounts[$row['group_name']] = $row['count'];
}

$urlQuery = "SELECT url, COUNT(*) as count FROM trace_data GROUP BY url";
$urlResult = $conn->query($urlQuery);
$urlCounts = [];
while ($row = $urlResult->fetch_assoc()) {
    $urlCounts[$row['url']] = $row['count'];
}

$groupedUrlQuery = "SELECT group_name, url, COUNT(*) as count FROM trace_data GROUP BY group_name, url";
$groupedUrlResult = $conn->query($groupedUrlQuery);
$groupedUrlCounts = [];
while ($row = $groupedUrlResult->fetch_assoc()) {
    $groupedUrlCounts[$row['group_name']][$row['url']] = $row['count'];
}

$filteredData = [];
if ($filterValue) {
    $filterQuery = "SELECT url, contact, device, os, clicked_at FROM trace_data WHERE group_name = ?";
    $stmt = $conn->prepare($filterQuery);
    $stmt->bind_param("s", $filterValue);
    $stmt->execute();
    $filterResult = $stmt->get_result();
    while ($row = $filterResult->fetch_assoc()) {
        $filteredData[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title> HQ Cyber Div 101</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="image.png" type="image/x-icon">
    <link rel="icon" href="F:\xampp\htdocs\admins\image.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3a0ca3;
            --danger: #f72585;
            --success: #4cc9f0;
            --warning: #f8961e;
            --dark-bg: #121212;
            --darker-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --text-light: #f8f9fa;
            --text-muted: #adb5bd;
            --border-color: #444;
            --border-radius: 12px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            line-height: 1.6;
            padding: 0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header Styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .dashboard-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-light);
        }

        /* Card Styles */
        .card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Stats Card */
        .stats-card {
            text-align: center;
            padding: 2rem;
        }

        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.5rem 0;
        }

        .stats-label {
            font-size: 1rem;
            color: var(--text-muted);
        }

        /* Filter Form */
        .filter-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .form-select {
            background-color: var(--darker-bg);
            color: var(--text-light);
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            flex: 1;
            transition: var(--transition);
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .data-table th {
            background-color: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
            position: sticky;
            top: 0;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animated {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </h1>
            <div class="last-updated">
                <span class="text-muted">Last updated: <?php echo date('M d, Y H:i'); ?></span>
            </div>
        </div>

        <!-- Stats Overview Card -->
        <div class="card stats-card animated" style="animation-delay: 0.1s;">
            <div class="card-title">
                <i class="fas fa-chart-line"></i> Overview
            </div>
            <div class="stats-number"><?php echo number_format($totalClicks); ?></div>
            <div class="stats-label">Total Clicks Tracked</div>
        </div>

        <!-- Filter Card -->
        <div class="card animated" style="animation-delay: 0.2s;">
            <div class="card-title">
                <i class="fas fa-filter"></i> Filter Data
            </div>
            <form method="GET" class="filter-form">
                <select name="group_name" class="form-select">
                    <option value="">All Groups</option>
                    <?php foreach ($groupCounts as $group => $count): ?>
                        <option value="<?php echo htmlspecialchars($group); ?>" <?php echo $filterValue === $group ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group); ?> (<?php echo number_format($count); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
            </form>
        </div>
        
        <?php if ($filterValue): ?>
            <!-- Filtered Results Card -->
            <div class="card animated" style="animation-delay: 0.3s;">
                <div class="card-title">
                    <i class="fas fa-list"></i> Filtered Results: <?php echo htmlspecialchars($filterValue); ?>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th>Contact</th>
                                <th>Device</th>
                                <th>OS</th>
                                <th>Clicked At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['url']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['device']); ?></td>
                                    <td><?php echo htmlspecialchars($row['os']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($row['clicked_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Analytics Card -->
        <div class="card animated" style="animation-delay: 0.4s;">
            <div class="card-title">
                <i class="fas fa-chart-bar"></i> Click Analytics
            </div>
            <div class="chart-container">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>
    
    <script>
        const groupedUrlCounts = <?php echo json_encode($groupedUrlCounts); ?>;
        const groupNames = Object.keys(groupedUrlCounts);
        const urlLabels = <?php echo json_encode(array_keys($urlCounts)); ?>;
        
        // Generate distinct colors for each group
        const colors = [
            '#4361ee', '#f72585', '#4cc9f0', '#7209b7', '#3a0ca3', 
            '#4895ef', '#f8961e', '#43aa8b', '#577590', '#90be6d'
        ];
        
        const datasets = groupNames.map((group, index) => {
            const counts = urlLabels.map(url => groupedUrlCounts[group][url] || 0);
            return {
                label: group,
                data: counts,
                borderColor: colors[index % colors.length],
                backgroundColor: colors[index % colors.length] + '20',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            };
        });

        // Initialize chart with dark theme
        new Chart(document.getElementById('lineChart').getContext('2d'), {
            type: 'line',
            data: { 
                labels: urlLabels, 
                datasets: datasets 
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'top', 
                        labels: { 
                            color: "#fff",
                            font: {
                                family: 'Inter'
                            }
                        }
                    },
                    tooltip: { 
                        bodyColor: "#fff", 
                        titleColor: "#fff", 
                        backgroundColor: "rgba(30,30,30,0.9)",
                        borderColor: "rgba(255,255,255,0.1)",
                        borderWidth: 1,
                        padding: 12,
                        bodyFont: {
                            family: 'Inter'
                        },
                        titleFont: {
                            family: 'Inter'
                        }
                    }
                },
                scales: { 
                    x: { 
                        ticks: { 
                            color: "#adb5bd",
                            font: {
                                family: 'Inter'
                            }
                        },
                        grid: { 
                            color: "rgba(255,255,255,0.1)" 
                        } 
                    },
                    y: { 
                        beginAtZero: true, 
                        ticks: { 
                            color: "#adb5bd",
                            font: {
                                family: 'Inter'
                            }
                        },
                        grid: { 
                            color: "rgba(255,255,255,0.1)" 
                        } 
                    } 
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });

        // Add smooth scrolling for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>