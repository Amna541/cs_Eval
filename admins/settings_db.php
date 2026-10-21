<?php
$host = 'localhost';
$username = 'settings_user';
$password = 'strongpassword123';
$database = 'settings_database';

$conn_settings = new mysqli($host, $username, $password, $database);

if ($conn_settings->connect_error) {
    die("Connection failed: " . $conn_settings->connect_error);
}
?>
