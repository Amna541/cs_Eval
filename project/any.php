<?php
// ==========================
// SHOW ERRORS (DEV MODE)
// ==========================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================
// DATABASE CONNECTION
// ==========================
$host = "localhost";
$user = "root";
$password = "";
$database = "trace_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// ==========================
// FETCH ALL DATA
// ==========================
$sql = "SELECT * FROM trace_data";
$result = mysqli_query($conn, $sql);

// ==========================
// GET COLUMN NAMES
// ==========================
$columns = [];

if ($result && mysqli_num_rows($result) > 0) {
    $fieldCount = mysqli_num_fields($result);
    for ($i = 0; $i < $fieldCount; $i++) {
        $field = mysqli_fetch_field_direct($result, $i);
        $columns[] = $field->name;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trace Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 14px;
            text-align: left;
        }
        th {
            background: #222;
            color: #fff;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
    </style>
</head>
<body>

<h2>Trace Data (All Records)</h2>

<table>
    <tr>
        <?php foreach ($columns as $col) { ?>
            <th><?php echo htmlspecialchars($col); ?></th>
        <?php } ?>
    </tr>

    <?php
    if ($result && mysqli_num_rows($result) > 0) {

        // Reset pointer after fetching columns
        mysqli_data_seek($result, 0);

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";

            foreach ($columns as $col) {
                // NULL SAFE + PHP 8.2 FIX
                $value = $row[$col] ?? '';
                echo "<td>" . htmlspecialchars((string)$value) . "</td>";
            }

            echo "</tr>";
        }

    } else {
        echo "<tr><td colspan='" . count($columns) . "'>No data found</td></tr>";
    }
    ?>
</table>

</body>
</html>

<?php
mysqli_close($conn);
?>
