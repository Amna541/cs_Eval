<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'group_db');

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>

