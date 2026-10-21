<?php
$host = "localhost";
$dbname = "track_db";  
$username = "root";  
$password = "";  

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch click data
$query = "SELECT 
            SUM(CASE WHEN link_clicked='easypaisa' THEN 1 ELSE 0 END) AS easypaisa,
            SUM(CASE WHEN link_clicked='askari' THEN 1 ELSE 0 END) AS askari,
            SUM(CASE WHEN link_clicked='csd' THEN 1 ELSE 0 END) AS csd
          FROM `tracking_data`";

$result = $conn->query($query);
$data = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click Tracking Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div style="width: 60%; margin: auto;">
        <canvas id="clickChart"></canvas>
    </div>

    <!-- Button for downloading the chart -->
    <button id="downloadBtn" style="display: block; margin: 20px auto;">Download Chart</button>

    <script>
        var ctx = document.getElementById("clickChart").getContext("2d");
        var chart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["Easypaisa", "Askari", "CSD"],
                datasets: [{
                    label: "Clicks",
                    data: [<?php echo $data['easypaisa']; ?>, <?php echo $data['askari']; ?>, <?php echo $data['csd']; ?>],
                    backgroundColor: ["green", "blue", "red"]
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 100
                        }
                    }
                }
            }
        });

        // Download chart as PNG when button is clicked
        document.getElementById("downloadBtn").addEventListener("click", function() {
            // Get the image data URL from the chart
            var imageURL = chart.toDataURL("image/png", 1.0);

            // Create a temporary anchor element to download the image
            var link = document.createElement('a');
            link.href = imageURL;
            link.download = "click_tracking_chart.png";  // Set the filename for the download

            // Trigger the download by programmatically clicking the link
            link.click();
        });
    </script>
</body>
</html>
