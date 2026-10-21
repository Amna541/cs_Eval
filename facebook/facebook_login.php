<?php
session_start(); // Start session for login

/* Database Connection */
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* Login Script */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id; // Store user session
            $_SESSION['email'] = $email;
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            echo "Invalid credentials!";
        }
    } else {
        echo "Invalid credentials!";
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
    <title>Facebook Login</title>
    <link rel="icon" href="fbook.png" type="image/png">

    <style>
       body {
    font-family: Arial, sans-serif;
    background-color: #f0f2f5;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100vh;
    justify-content: flex-start; /* Adjusted */
    margin: 0; /* Ensures no extra margin on body */
}

.facebook-text {
    font-size: 32px;
    font-weight: bold;
    color: #1877f2;
    margin-bottom: 20px;
    margin-top: 50px; /* Adjusted */
}

.login-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin-top: 20px; /* Adjusted */
    width: 100%;
    max-width: 400px; /* Ensures the login container doesn't stretch too wide */
}

.login-container h2 {
    color: black;
    margin-bottom: 20px;
}

.login-container input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.login-container button {
    width: 100%;
    background: #1877f2;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.footer {
    position: relative; /* Changed from absolute */
    bottom: 0;
    width: 100%;
    color: gray;
    background-color: white;
    padding: 20px 0;
    margin-top: auto; /* Pushes footer to the bottom */
}

.footer-text {
    font-size: 14px;
    color: gray;
    text-align: left;
    margin-left: 20px;
    line-height: 1.6;
}

.divider {
    border: none;
    border-top: 1px solid #ddd;
    margin: 10px 0;
    margin-left: 20px;
    margin-right: 20px;
}

.footer-text span {
    margin-right: 20px;
}

.language-bar {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-left: 20px;
}

.plus-icon {
    cursor: pointer;
    font-weight: bold;
    font-size: 18px;
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f0f0f0;
}

.dialog-box {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 650px;
    height: 500px;
    background: white;
    border: 1px solid #ccc;
    padding: 20px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    overflow-y: auto;
}

.dialog-box.active {
    display: block;
}

.dialog-header {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
    background-color: #6d84b4;
    color: white;
    padding: 10px;
}

.language-column {
    width: 22%;
    margin-bottom: 20px;
}

.language-column h3 {
    font-size: 18px;
    margin-bottom: 10px;
    font-weight: bold;
}

.language-column span {
    display: block;
    margin-bottom: 5px;
    cursor: pointer;
}

.language-column span:hover {
    background-color: #f0f0f0;
    border-radius: 3px;
}

.facebook-logo {
    max-width: 200px;
    margin-bottom: 20px; /* Reduced the space below the logo */
}



    </style>
</head>
<body>
<img src="fb.svg" alt="Facebook Logo" class="facebook-logo">
    <div class="login-container">
        <h2>Log in to Facebook</h2>
        <form method="POST">
            <input type="text" name="email" placeholder="Email address or phone number" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log in</button>
        </form>
        <p><a href="#">Forgotten account?</a> · <a href="signup.html">Sign up for Facebook</a></p>
    </div>
    <div class="footer">
    <div class="language-bar">
    <span>English (UK)</span>
    <span>اردو</span>
    <span>ਪੰਜਾਬੀ</span>
    <span>हिन्दी</span>
    <span>বাংলা</span>
    <span>العربية</span>
    <span>中文</span>
    <span>Deutsch</span>
    <span>Español</span>
    <div class="plus-icon" onclick="openDialog()">+</div>
</div>

<!-- Language Selection Dialog -->
<div class="dialog-box" id="languageDialog">
<div class="dialog-header">
        Select your language
    </div>
    
    <div class="dialog-content">
        <!-- Left Panel (Categories) -->
        <div class="left-panel">
            <ul>
                <li><b>All languages</b></li>
                <li>Africa and Middle East</li>
                <li>Americas</li>
                <li>Asia-Pacific</li>
                <li>Eastern Europe</li>
                <li>Western Europe</li>
            </ul>
        </div>

        <!-- Right Panel (Languages) -->
        <div class="right-panel">
            <div class="language-list">
                <div class="language-column">
                    <h3>English (UK)</h3>
                    <span>Af-Soomaali</span>
                    <span>Afrikaans</span>
                    <span>Azerbaycan dili</span>
                    <span>Bahasa Indonesia</span>
                    <span>Bahasa Melayu</span>
                    <span>Basa Jawa</span>
                    <span>Bisaya</span>
                    <span>Bosanski</span>
                    <span>Brezhoneg</span>
                    <span>Català</span>
                    <span>Corsu</span>
                    <span>Cymraeg</span>
                    <span>Dansk</span>
                    <span>Deutsch</span>
                    <span>Eesti</span>
                    <span>English (UK)</span>
                    <span>English (US)</span>
                    <span>Español</span>
                    <span>Español (España)</span>
                    <span>Euskara</span>
                    <span>Filipino</span>
                    <span>Français (Canada)</span>
                    <span>Français (France)</span>
                    <span>Frysk</span>
                    <span>Fula</span>
                    <span>Furlan</span>
                    <span>Føroyskt</span>
                    <span>Gaeilge</span>
                </div>

                <div class="language-column">
                    <h3>اردو</h3>
                    <span>Galego</span>
                    <span>Guarani</span>
                    <span>Hausa</span>
                    <span>Hrvatski</span>
                    <span>Ikinyarwanda</span>
                    <span>Inuktitut</span>
                    <span>Italiano</span>
                    <span>Iñupiatun</span>
                    <span>Kiswahili</span>
                    <span>Kreyòl Ayisyen</span>
                    <span>Kurdî (Kurmancî)</span>
                    <span>Latviešu</span>
                    <span>Lietuvių</span>
                    <span>Magyar</span>
                    <span>Malagasy</span>
                    <span>Malti</span>
                    <span>Nederlands</span>
                    <span>Norsk (bokmål)</span>
                    <span>Norsk (nynorsk)</span>
                    <span>O'zbek</span>
                    <span>Polski</span>
                    <span>Português (Brasil)</span>
                    <span>Português (Portugal)</span>
                    <span>Română</span>
                    <span>Sardu</span>
                    <span>Shona</span>
                    <span>Shqip</span>
                    <span>Slovenčina</span>
                </div>

                <div class="language-column">
                    <h3>پښتو</h3>
                    <span>Slovenščina</span>
                    <span>Suomi</span>
                    <span>Svenska</span>
                    <span>Tiếng Việt</span>
                    <span>Türkçe</span>
                    <span>Vlaams</span>
                    <span>Zaza</span>
                    <span>Íslenska</span>
                    <span>Čeština</span>
                    <span>ślōnskŏ gŏdka</span>
                    <span>Ελληνικά</span>
                    <span>Беларуская</span>
                    <span>Български</span>
                    <span>Македонски</span>
                    <span>Монгол</span>
                    <span>Русский</span>
                    <span>Српски</span>
                    <span>Татарча</span>
                    <span>Тоҷикӣ</span>
                    <span>Українська</span>
                    <span>кыргызча</span>
                    <span>Қазақша</span>
                    <span>Հայերեն</span>
                    <span>‏עברית‏</span>
                    <span>‏اردو‏</span>
                    <span>‏العربية‏</span>
                    <span>‏فارسی‏</span>
                    <span>پښتو‏</span>
                </div>

                <div class="language-column">
                    <h3>العربية</h3>
                    <span>‏کوردیی ناوەندی‏</span>
                    <span>‏ܣܘܪܝܝܐ‏</span>
                    <span>नेपाली</span>
                    <span>मराठी</span>
                    <span>हिन्दी</span>
                    <span>অসমীয়া</span>
                    <span>বাংলা</span>
                    <span>ਪੰਜਾਬੀ</span>
                    <span>ગુજરાતી</span>
                    <span>ଓଡ଼ିଆ</span>
                    <span>தமிழ்</span>
                    <span>తెలుగు</span>
                    <span>ಕನ್ನಡ</span>
                    <span>മലയാളം</span>
                    <span>සිංහල</span>
                    <span>ภาษาไทย</span>
                    <span>ພາສາລາວ</span>
                    <span>မြန်မာဘာသာ</span>
                    <span>ქართული</span>
                    <span>አማርኛ</span>
                    <span>ភាសាខ្មែរ</span>
                    <span>ⵜⴰⵎⴰⵣⵉⵖⵜ</span>
                    <span>中文(台灣)</span>
                    <span>中文(简体)</span>
                    <span>中文(香港)</span>
                    <span>日本語</span>
                    <span>日本語(関西)</span>
                    <span>한국어</span>
                </div>
            </div>
        </div>
    </div>

    <button class="close-btn" onclick="closeDialog()">Close</button>
</div>


<script>
    function openDialog() {
        document.getElementById("languageDialog").classList.add("active");
    }

    function closeDialog() {
        document.getElementById("languageDialog").classList.remove("active");
    }
</script>

    <hr class="divider"> <!-- Divider between sections -->
    <div class="footer-text">
    <span>Sign Up</span>
    <span>Log in</span>
    <span>Messenger</span>
    <span>Facebook Lite</span>
    <span>Video</span>
    <span>Places</span>
    <span>Games</span>
    <span>Marketplace</span>
    <span>Meta Pay</span>
    <span>Meta Store</span>
    <span>Meta Quest</span>
    <span>Ray-Ban Meta</span>
    <span>Meta AI</span>
</div>

<div class="footer-text">
    <span>Instagram</span>
    <span>Threads</span>
    <span>Fundraisers</span>
    <span>Services</span>
    <span>Voting Information Centre</span>
    <span>Privacy Policy</span>
    <span>Privacy Centre</span>
    <span>Groups</span>
</div>

<div class="footer-text">
    <span>Careers</span>
    <span>Cookies</span>
    <span>AdChoices</span>
    <span>Terms</span>
    <span>Help</span>
    <span>Contact uploading and non-users</span><br>
    <span>Meta © 2025</span>
</div>

</div>


</body>
</html>
