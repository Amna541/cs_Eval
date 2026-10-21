<?php
require_once __DIR__ . '/../config.php';

function encryptData($data) {
    return base64_encode(openssl_encrypt($data, 'AES-128-ECB', ENCRYPTION_KEY));
}

function decryptData($data) {
    return openssl_decrypt(base64_decode($data), 'AES-128-ECB', ENCRYPTION_KEY);
}

function generateTrackingLink($contact, $service) {
    $encryptedContact = encryptData($contact);
    return BASE_URL . "/tracking/{$encryptedContact}/{$service}.php";
}
?>
