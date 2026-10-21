<?php
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = $_POST['group_name'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];

    $links = [
        "Easypaisa" => "https://localhost/tracking/tracking/php_login/php_login_system/testing_website/easypaisa.php",
        "Askari Bank" => "https://localhost/tracking/tracking/askari_bank/page2.php",
        "CSD Register" => "https://localhost/tracking/tracking/csd_gov_pk/csd_register.php"
    ];

    foreach ($links as $name => $url) {
        echo "<p><a href='$url?num=$phone_number' target='_blank'>$name</a></p>";
    }
}
?>
