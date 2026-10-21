<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stylo E-Commerce</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #fff;
            border-bottom: 1px solid #ccc;
            padding: 15px 40px;
        }
        .logo img {
            height: 50px;
        }
        nav {
            display: flex;
            gap: 30px;
        }
        nav a {
            text-decoration: none;
            color: #000;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #ff007f;
        }
        .icons {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        .icons img {
            height: 24px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .icons img:hover {
            transform: scale(1.1);
        }
        .promo-banner {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        /* Sign Up Form Styles */
        .signup-form-container {
            width: 350px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }
        .signup-form-container h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .signup-form-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
        }
        .signup-form-container button {
            width: 100%;
            padding: 12px;
            background-color: #ff007f;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .signup-form-container button:hover {
            background-color: #e6006f;
        }
        footer {
    background-color: #fff;
    color: #000;
    padding: 40px 20px;
    text-align: center;
    border-top: 1px solid #ddd;
}

.footer-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    max-width: 1200px;
    margin: auto;
}

.footer-section {
    flex: 1;
    min-width: 200px;
    margin-bottom: 20px;
    text-align: left;
}

.footer-section h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #000;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin: 5px 0;
}

.footer-section ul li a {
    color: #000;
    text-decoration: none;
    font-size: 14px;
}

.footer-section ul li a:hover {
    text-decoration: underline;
}

.footer-section input {
    width: 80%;
    padding: 10px;
    margin-top: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.footer-section button {
    margin-top: 10px;
    background-color: #ff007f;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
}

.footer-section button:hover {
    background-color: #e6006f;
}

.social-icons a img {
    width: 30px;
    margin: 5px;
    filter: grayscale(100%);
    transition: filter 0.3s;
}

.social-icons a img:hover {
    filter: none;
}

.footer-bottom {
    margin-top: 20px;
    padding-top: 10px;
    border-top: 1px solid #ddd;
    font-size: 14px;
}


    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="stylo.svg" alt="Stylo Logo">
        </div>
        <nav>
            <a href="#">Winter Sale</a>
            <a href="#">New In</a>
            <a href="#">Women Shoes</a>
            <a href="#">Women Apparel</a>
            <a href="#">Bags</a>
            <a href="#">Kids</a>
            <a href="#">Accessories</a>
            <a href="#">Fragrances</a>
            <a href="#">Online Exclusives</a>
        </nav>
        <div class="icons">
            <img src="user_icon.png" alt="User Icon" title="Profile">
            <img src="search.png" alt="Search Icon" title="Search">
            <img src="cart.png" alt="Cart Icon" title="Cart">
        </div>
    </header>

    <div class="promo-banner">
        Use code ‘freedelivery' at checkout to get free delivery on prepaid orders.
    </div>

    <!-- Signup Form -->
    <div class="signup-form-container">
        <h2>CREATE ACCOUNT</h2>
        <form action="signup.php" method="POST" onsubmit="return validateForm()">
    <label for="first-name">FIRST NAME</label><br>
    <input type="text" id="first-name" name="first-name" placeholder="First Name" required><br><br>

    <label for="last-name">LAST NAME</label><br>
    <input type="text" id="last-name" name="last-name" placeholder="Last Name" required><br><br>

    <label for="email">EMAIL</label><br>
    <input type="email" id="email" name="email" placeholder="Email" required><br><br>

    <label for="password">PASSWORD</label><br>
    <input type="password" id="password" name="password" placeholder="Password" required><br><br>

    <button type="submit">CREATE</button>
</form>


<script>
    function redirectUser(event) {
        event.preventDefault(); // Prevent form submission
        // Simulate account creation process (you may replace it with an actual AJAX request)
        setTimeout(function () {
            window.location.href = "https://stylo.pk/";
        }, 1000); // Redirect after 1 second
    }
</script>



    </div>
    <footer>
    <div class="footer-container">
        <div class="footer-section">
            <h3>Get in touch</h3>
            <p>📞 +92 42 111178956</p>
            <p>📧 support@stylo.pk</p>
        </div>

        <div class="footer-section">
            <h3>Information</h3>
            <ul>
                <li><a href="#">Online Order Tracking</a></li>
                <li><a href="#">Shipping Policy</a></li>
                <li><a href="#">Returns & Exchange Policy</a></li>
                <li><a href="#">Discount Policy</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">FAQs</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>About</h3>
            <ul>
                <li><a href="#">Our Story</a></li>
                <li><a href="#">Career@Stylo</a></li>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Blogs</a></li>
                <li><a href="#">Download Our App</a></li>
                <li><a href="#">Store Locations</a></li>
                <li><a href="#">Join Franchise Program</a></li>
                
                



            </ul>
        </div>

        <div class="footer-section">
            <h3>Download Our App</h3>
            <ul>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Store Locations</a></li>
                <li><a href="#">Join Franchise Program</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Size Chart</h3>
            <ul>
                <li><a href="#">Ladies Shoes Size Chart</a></li>
                <li><a href="#">Ladies Apparel Size Chart</a></li>
                <li><a href="#">Denim Size Chart</a></li>
                <li><a href="#">Kids Shoes Size Chart</a></li>
                <li><a href="#">Kids Pret Size Chart</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Sign up and save</h3>
            <p>Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p>
            <input type="email" placeholder="Enter your email">
            <button>Subscribe</button>
        </div>

        <div class="footer-section social-icons">
            <h3>Follow Us</h3>
            <a href="#"><img src="insta.png" alt="Instagram"></a>
            <a href="#"><img src="facebook.png" alt="Facebook"></a>
            <a href="#"><img src="youtube.png" alt="YouTube"></a>
            <a href="#"><img src="twitter.png" alt="Twitter"></a>
            <a href="#"><img src="pinterest.png" alt="Pinterest"></a>
            <a href="#"><img src="linkedin.png" alt="LinkedIn"></a>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2025 Stylo | All Rights Reserved</p>
    </div>
</footer>


</body>
</html>
