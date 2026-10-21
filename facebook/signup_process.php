<?php
// Database connection
$servername = "localhost";
$username = "root"; // Use your MySQL username
$password = ""; // Use your MySQL password
$dbname = "facebook_signup";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$first_name = $_POST['first_name'];
$surname = $_POST['surname'];
$day = $_POST['day'];
$month = $_POST['month'];
$year = $_POST['year'];
$gender = $_POST['gender'];
$pronoun = isset($_POST['pronoun']) ? $_POST['pronoun'] : null;
$custom_gender = isset($_POST['custom_gender']) ? $_POST['custom_gender'] : null;
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing

// Insert data into the database
$sql = "INSERT INTO users (first_name, surname, day, month, year, gender, pronoun, custom_gender, email, password)
        VALUES ('$first_name', '$surname', '$day', '$month', '$year', '$gender', '$pronoun', '$custom_gender', '$email', '$password')";

if ($conn->query($sql) === TRUE) {
    // Redirect to Facebook after successful signup
    header("Location: https://www.facebook.com");
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
