<?php
require 'config.php';
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['group_id'])) {
    die("No group selected.");
}

$group_id = $_SESSION['group_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['contact'])) {
        $contact = $conn->real_escape_string($_POST['contact']);
        $conn->query("INSERT INTO contacts (group_id, contact) VALUES ('$group_id', '$contact')");
        echo "<script>alert('Contact Added Successfully'); window.location.href='index.php';</script>";
    } elseif (isset($_FILES['file'])) {
        $file = $_FILES['file']['tmp_name'];
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator() as $row) {
            $cellValue = $row->getCellIterator()->current()->getValue();
            $conn->query("INSERT INTO contacts (group_id, contact) VALUES ('$group_id', '$cellValue')");
        }
        echo "<script>alert('Contacts Imported Successfully'); window.location.href='index.php';</script>";
    }
}
?>
