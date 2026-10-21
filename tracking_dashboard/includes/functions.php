<?php
function encryptGroup($group_id) {
    return base64_encode($group_id);
}
function decryptGroup($encrypted) {
    return base64_decode($encrypted);
}
?>
