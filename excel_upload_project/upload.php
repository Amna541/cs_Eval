<?php
// Include the required libraries and database connection
require 'vendor/autoload.php';  // Autoload PhpSpreadsheet
require 'database.php';         // Database connection

use PhpOffice\PhpSpreadsheet\IOFactory;

// Check if the form is submitted with an uploaded file and group name
if ($_FILES['excelFile']['name'] && isset($_POST['groupName'])) {
    // File and group name variables
    $file = $_FILES['excelFile']['tmp_name'];
    $groupName = $_POST['groupName'];

    // Load the Excel file using PhpSpreadsheet
    try {
        $spreadsheet = IOFactory::load($file); // Load Excel file
        $worksheet = $spreadsheet->getActiveSheet(); // Get active sheet
        $data = $worksheet->toArray(); // Convert sheet to array
    } catch (Exception $e) {
        echo "<p style='color: red; font-weight: bold;'>Error loading Excel file: " . $e->getMessage() . "</p>";
        exit;
    }

    // Check if data exists in the file
    if (empty($data)) {
        echo "<p style='color: red; font-weight: bold;'>No data found in the Excel file.</p>";
        exit;
    }

    // Styling for the output page
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { width: 80%; margin: auto; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
        th { background-color: #007BFF; color: white; text-transform: uppercase; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #ddd; }
        a { text-decoration: none; color: #007BFF; font-weight: bold; }
        a:hover { text-decoration: underline; color: #0056b3; }
        h2 { color: #333; margin-bottom: 20px; }
    </style>";

    // Displaying group name and table with generated links
    echo "<div class='container'>";
    echo "<h2>Group Name: <span style='color: #007BFF;'>$groupName</span></h2>";
    echo "<table>";
echo "<tr><th>Number</th><th>Easypaisa</th><th>Askari Bank</th><th>CSD Register</th></tr>";

for ($i = 1; $i < count($data); $i++) {
    if (!isset($data[$i][0])) continue;

    $number = htmlspecialchars($data[$i][0]);

    echo "<tr>";
    echo "<td>$number</td>";
    $link = "track.php?data=" . urlencode($encrypted_phone) . "&group_id=$group_id&redirect=php_login/php_login_system/testing_website/easypaisa.php";

echo "<td><a href='http://localhost/tracking/tracking/track.php?link=askari_bank&number=$number' target='_blank'>Askari Bank</a></td>";
echo "<td><a href='http://localhost/tracking/tracking/track.php?link=csd_register&number=$number' target='_blank'>CSD Register</a></td>";


    echo "</tr>";
}

echo "</table>";


    echo "</div>";
} else {
    echo "<p style='color: red; font-weight: bold;'>Please upload an Excel file and provide a group name.</p>";
}
?>
