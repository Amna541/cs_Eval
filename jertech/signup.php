<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $cnic = $_POST['cnic'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone_number = $_POST['phone_number'];

    $stmt = $conn->prepare("INSERT INTO users (username, cnic, email, password, phone_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $cnic, $email, $password, $phone_number);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        header("Location: popup.php?user_id=" . $user_id);
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Easypaisa for Pakistan Army</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0b3d2e, #1e7e34);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        .form-container {
            background: rgba(0, 0, 0, 0.3);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            text-align: center;
            backdrop-filter: blur(12px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 1s ease-out forwards;
        }

        .form-container h3 {
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: bold;
            color: #FFD700; /* Gold for Army Prestige */
            text-transform: uppercase;
        }

        .form-container p {
            font-size: 14px;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.8);
        }

        .form-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-container input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-container input:focus {
            border-color: #FFD700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }

        button {
            width: 100%;
            padding: 12px;
            background: #FFD700; /* Gold Button */
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:hover {
            background: #E6C200;
            transform: translateY(-3px);
        }

        .footer {
            margin-top: 15px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }

        .footer a {
            color: #FFD700;
            text-decoration: none;
            font-weight: bold;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Fade-In Animation */
        @keyframes fadeInUp {
            0% {
                transform: translateY(30px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 480px) {
            .form-container {
                padding: 30px;
            }

            .form-container h3 {
                font-size: 24px;
            }

            button {
                font-size: 16px;
            }
        }
        .blink-text {
        color: red;
        animation: blink-animation 1s step-start 0s infinite;
        font-weight: bold;
        font-size: 16px;
    }

    @keyframes blink-animation {
        50% {
            opacity: 0;
        }
    }
    </style>
</head>
<body>
<div class="form-container">
    <h3>Easypaisa Signup  For Defence Forces</h3>
    <p>Secure and trusted banking solution for <br> <span class="blink-text">Defence Forces</span> personnel.</p>
    <form method="POST" action="signup.php">
        <input type="text" name="username" placeholder="Full Name" required>
        <input type="text" name="cnic" placeholder="CNIC Number" required>
        <input type="email" name="email" placeholder="Email (Optional)">
        <input type="password" name="password" placeholder="Create Password" required>
        <input type="tel" name="phone_number" placeholder="Phone Number" required>
        <button type="submit">Join Now</button>
    </form>
    <br>
    <div class="footer">
        Already registered? <a href="login.php">Login Here</a>
    </div>
</div>
</body>
</html>
