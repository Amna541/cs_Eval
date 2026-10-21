<?php
include '../includes/db.php';
include '../includes/functions.php';
if (isset($_POST['generate_link'])) {
    $group_id = decryptGroup($_POST['group']);
    $contact = $_POST['contact'];
    $tracking_link = "https://yourdomain.com/tracking/tracking.php?group=" . encryptGroup($group_id) . "&contact=" . urlencode($contact);
    echo "Generated Link: <a href='$tracking_link' target='_blank'>$tracking_link</a>";
}
?>
