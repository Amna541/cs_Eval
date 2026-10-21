<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Easypaisa Footer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f9f3;
            margin: 0;
            padding: 0;
        }
        .footer {
            padding: 40px;
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            background-color: #f3f9f3;
        }
        .footer h3 {
            color: #00a651;
            font-size: 18px;
        }
        .footer ul {
            list-style: none;
            padding: 0;
        }
        .footer ul li {
            margin-bottom: 8px;
            color: #000;
        }
        .social-icons img {
            width: 30px;
            margin-right: 10px;
        }
        .footer-bottom {
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom:40px;
        }
        .app-links img {
            width: 150px;
            margin-right: 10px;
        }
        .contact-info {
            color: #000;
            font-size: 14px;
        }
        .whatsapp-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            align-items: center;
        }
        .help-text {
            background-color:rgb(255, 255, 255);
            color: black;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-right: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: relative;
            transition: all 0.3s ease;
        }
        .help-text::after {
            content: "";
            position: absolute;
            top: 50%;
            right: -10px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent transparent transparent #ffffff; /* White color */
            transform: translateY(-50%);
        }
        .whatsapp-button img {
            width: 50px;
            height: 50px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="footer">
    <div>
        <h3>About easypaisa</h3>
        <ul>
            <li>About Us</li>
            <li>Awards</li>
            <li>Terms & Conditions</li>
            <li>Privacy Policy</li>
            <li>Careers</li>
        </ul>
        <h3>Connect</h3>
        <div class="social-icons">
            <img src="fb2.png" alt="Facebook">
            <img src="in2.png" alt="Instagram">
            <img src="tw1.png" alt="Twitter">
        </div>
    </div>

    <div>
        <h3>Any questions</h3>
        <ul>
            <li>Contact Us</li>
            <li>FAQs</li>
            <li>Schedule of Charges</li>
            <li>Legal</li>
        </ul>
    </div>

    <div class="contact-info">
        <p>Copyright &copy; 2025 easypaisa,</p>
        <p>Acet Mcs,Rawalpindi,</p>
        <p>Islamabad, Pakistan</p>
        <p>Phone: 03125825394</p>
        <p>Email: info@telenorbank.pk</p>
    </div>
</div>

<div class="footer-bottom">
 

<p style="font-size: 18px; color: #333; font-weight: bolder;margin: left 100px;">
Join Our Complete Lifestyle Platform And Adopt The Digital Way Of Life!</p>
    <div class="app-links">
        <img src="asset2.png" alt="Google Play ">
        <img src="asset3.png" alt="App Store">
        <img src="asset1.png" alt="App Gallery">
    </div>
</div>

<div class="whatsapp-container">
    <div class="help-text" id="helpText">Need Help?</div>
    <div class="whatsapp-button">
        <a href="https://wa.me/yourphonenumber" 
           onmouseover="changeText('WhatsApp')" 
           onmouseout="changeText('Need Help?')">
            <img src="whatsapp.png" alt="WhatsApp">
        </a>
    </div>
</div>

<script>
    function changeText(newText) {
        document.getElementById("helpText").textContent = newText;
    }
</script>

</body>
</html>
