<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: easypaisa.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Easypaisa</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            overflow-x: hidden;
        }
        .page {
            min-height: 100vh;
            padding: 50px 20px;
            text-align: center;
            color: #fff;
        }
        #page1 {
            background-image: url('image.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        #page2 {
            background-color: #EBF5EF;
            display: flex;
            justify-content: space-between;
            align-items: center;
            opacity: 0;
            transform: translateY(100px);
            transition: opacity 1s ease-out, transform 1s ease-out;
        }
        #page2.show {
            opacity: 1;
            transform: translateY(0);
        }
        #page3 {
            background-color: #EBF5EF;
        }
        #page4 {
            background-color: #EBF5EF;
        }
        #page5 {
            background-color: #EBF5EF;
        }
        #page6 {
            background-color: #EBF5EF;
        }
        .highlight-green {
            color: green;
        }
    </style>
</head>
<body>

<div id="page1" class="page"></div>

<div id="page2" class="page">
    <div style="flex: 1; padding: 20px; text-align: left;">
        <img src="e5.webp" alt="Image Description" style="max-width: 100%; height: auto; border-radius: 8px;">
    </div>
    <div style="flex: 1; padding: 20px; text-align: left;">
        <img src="e4.png" alt="Small Image" style="max-width: 30%; height: auto; border-radius: 8px;">
        <p style="font-size: 20px; color:black; margin-bottom: 10px;">Making your everyday transactions easy, one tap at a time!</p>
        <p style="font-size: 22px; color:black; font-weight: bold;">#live<span class="highlight-green">easy</span></p>
        <video width="100%" controls>
            <source src="v1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</div>

<div id="page3" class="page">
<p style="font-size: 20px; color:black; max-width: 700%; margin-bottom: 10px;">Welcome to Pakistan’s favorite way to pay!<br></p>
    
    <p style="font-family: 'Roboto', Sans-serif;font-size: 20px;text-align:center; max-width: 50%;margin-left: auto;
    margin-right: auto; color:black; margin-bottom: 10px;font-weight: 500;">easypaisa is more than just money transfer. With the latest easypaisa app you can order your own <br class="hide-on-mobile">
easypaisa debit card. You can also recharge your phone, pay utility bills, send gifts, win rewards, cashbacks<br class="hide-on-mobile">
and enjoy discounts on partner restaurants and shopping</p>
</div>

<div id="page4" class="page">
    <h2>Bank Transfer</h2>
    <p>Transfer funds directly to your bank account with Easypaisa.</p>
</div>

<div id="page5" class="page">
    <h2>Donations</h2>
    <p>Make charitable donations directly through the Easypaisa platform.</p>
</div>

<div id="page6" class="page">
    <h2>Account Statements</h2>
    <p>View and download your Easypaisa account statements here.</p>
</div>

<script>
    document.addEventListener('scroll', function() {
        const page2 = document.getElementById('page2');
        const page2Position = page2.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;

        if (page2Position < screenPosition) {
            page2.classList.add('show');
        }
    });
</script>

</body>
</html>
