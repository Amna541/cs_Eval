<?php
// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "easypaisa"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $army_number = trim($_POST['army_number']);
    $organization = trim($_POST['organization']);

    if (empty($army_number) || empty($organization)) {
        echo "All fields are mandatory.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET army_number = ?, organization = ? WHERE username = ?");
        $stmt->bind_param("sss", $army_number, $organization, $username);  // Assuming you have the username stored in session or passed from the signup

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: welcome.php"); // Redirect after successful submission
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>
