<!DOCTYPE html>
<html lang="en">
<head>
    <title>Click Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>Link Click Statistics</h2>
    <canvas id="clickChart"></canvas>

    <script>
        $(document).ready(function() {
            $.get("fetch_graph_data.php", function(data) {
                let parsedData = JSON.parse(data);
                let labels = parsedData.map(d => d.link_type);
                let values = parsedData.map(d => d.total);

                new Chart(document.getElementById("clickChart"), {
                    type: "bar",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Clicks",
                            data: values,
                            backgroundColor: "blue"
                        }]
                    }
                });
            });
        });
    </script>
</body>
</html>
