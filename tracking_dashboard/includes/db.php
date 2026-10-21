<?php
$host = 'localhost';
$dbname = 'cyber_track_db';
$username = 'root';
$password = ''; // Change if necessary
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
