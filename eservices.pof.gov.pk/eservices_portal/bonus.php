<?php
date_default_timezone_set('Asia/Karachi');
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonus Confirmation - POF e-Services</title>

    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        #pageLoader {
            position: fixed;
            inset: 0;
            background: #4fc3e7;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        }

        .loader-bars {
            display: flex;
            gap: 6px;
        }

        .loader-bars span {
            width: 6px;
            height: 30px;
            background: #fff;
            animation: bounce 1s infinite ease-in-out;
        }

        .loader-bars span:nth-child(2) { animation-delay: .1s; }
        .loader-bars span:nth-child(3) { animation-delay: .2s; }
        .loader-bars span:nth-child(4) { animation-delay: .3s; }
        .loader-bars span:nth-child(5) { animation-delay: .4s; }

        @keyframes bounce {
            0%,100% { transform: scaleY(0.4); }
            50% { transform: scaleY(1); }
        }

        .bonus-results-container {
            position: relative;
            z-index: 2;
            max-width: 500px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .bonus-card {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.98), 
                rgba(248, 250, 252, 0.95));
            padding: 40px 35px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.12),
                0 8px 30px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .success-icon {
            font-size: 70px;
            color: #4CAF50;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .main-title {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .sub-title {
            color: #4CAF50;
            font-size: 18px;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .message-box {
            background: rgba(255, 215, 0, 0.08);
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
            border-left: 4px solid #FFD700;
        }

        .message-text {
            color: #424242;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .message-text:last-child {
            margin-bottom: 0;
        }

        .highlight {
            color: #2E7D32;
            font-weight: 600;
            background: rgba(46, 125, 50, 0.1);
            padding: 2px 8px;
            border-radius: 4px;
        }

        .footer-note {
            background: rgba(33, 150, 243, 0.08);
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
            border-top: 3px solid #2196F3;
        }

        .footer-text {
            color: #2c3e50;
            font-size: 15px;
            line-height: 1.5;
        }

        .department {
            color: #2196F3;
            font-weight: 600;
            margin-top: 10px;
            font-size: 16px;
        }

        .action-btn {
            background: linear-gradient(135deg,#4fc3f7,#0288d1);
            color: white;
            border: none;
            padding: 14px 40px;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>

<body>

<!-- PAGE LOADER -->
<div id="pageLoader">
    <div class="loader-bars">
        <span></span><span></span><span></span><span></span><span></span>
    </div>
</div>

<!-- NETWORK ANIMATION -->
<canvas id="network"></canvas>

<!-- DARK OVERLAY -->
<div class="overlay"></div>

<div class="header">
    <h1>Bonus Confirmation - POF e-Services</h1>
</div>

<div class="bonus-results-container">
    <div class="bonus-card">
        <div class="success-icon">✓</div>
        
        <h1 class="main-title">BONUS ELIGIBILITY CONFIRMED</h1>
        <div class="sub-title">Congratulations. Your bonus has been approved.</div>

        <div class="message-box">
            <p class="message-text">The approved amount will be <span class="highlight">credited </span> with your <span class="highlight">January 2026 salary payment</span>.</p>
            <p class="message-text">Payment will be processed automatically between <span class="highlight">25th to 31st January 2026</span>.</p>
        </div>

        <div class="footer-note">
            <p class="footer-text">Thank you for your service.</p>
            <div class="department">Pakistan Ordnance Factories<br>Employee Benefits Department</div>
        </div>

        <button class="action-btn" onclick="window.location.href='index.php'">Return to Portal</button>
    </div>
</div>

<script>
/* HIDE LOADER AFTER PAGE LOAD */
window.addEventListener("load", () => {
    setTimeout(() => {
        document.getElementById("pageLoader").style.display = "none";
    }, 1200);
});
</script>

<script src="network.js"></script>
</body>
</html>