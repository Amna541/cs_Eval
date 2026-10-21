<?php
if (isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];
    $file_path = "links/$group_id.txt";

    if (file_exists($file_path)) {
        echo "<h2>Tracking Links for Group $group_id</h2>";
        echo nl2br(file_get_contents($file_path));
    } else {
        echo "No tracking links found for this group.";
    }
}
?>
