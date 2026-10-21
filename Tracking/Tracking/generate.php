<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "track_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact = $_POST['contact'];

    // Step 1: Insert a new group record and get its ID
    $stmt = $conn->prepare("INSERT INTO groups () VALUES ()"); // Remove 'created_at'

    $stmt->execute();
    $group_id = $stmt->insert_id; // Fetch the new group ID
    $stmt->close();

    // Step 2: Define tracking links with group_id
    $links = [
        "Easypaisa" => "track.php?link=easypaisa&group=$group_id",
        "Askari" => "track.php?link=askari&group=$group_id",
        "CSD" => "track.php?link=csd&group=$group_id"
    ];

    // Step 3: Insert generated links into `generated_links`
    foreach ($links as $name => $link) {
        $stmt = $conn->prepare("INSERT INTO generated_links (email_or_phone, link, group_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $contact, $link, $group_id);
        $stmt->execute();
    }

    echo "Tracking Links Generated:<br>";
    foreach ($links as $name => $link) {
        echo "<a href='$link' target='_blank'>$name</a><br>";
    }
}

$conn->close();
?>
