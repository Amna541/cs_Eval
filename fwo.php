<?php
// Start session
session_start();

// ==========================================
// 1. DATABASE CONFIGURATION
// ==========================================
$host = "localhost";
$dbname = "fwo_db"; // <-- Yahan apna database name likhein (e.g., fwo_db)
$username = "root"; // XAMPP default
$password = "";     // XAMPP default (usually empty)

$error_message = "";
$success_message = "";

// ==========================================
// 2. DATABASE CONNECTION & AUTO-SETUP
// ==========================================
try {
    // Connect to MySQL (bina database select kiye pehle)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Database create karein agar exist nahi karta
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Ab us database se connect karein
    $pdo->exec("USE $dbname");

    // Users table create karein agar nahi hai
    $tableSql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($tableSql);

    // Check karein ke table khali hai ya nahi. Agar khali hai, toh default admin user daalein.
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        // Default User: admin@fwo.com / password123
        $default_email = "admin@fwo.com";
        $default_pass = password_hash("password123", PASSWORD_DEFAULT);
        
        $insertStmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $insertStmt->execute([$default_email, $default_pass]);
        $success_message = "Database setup complete! Default login: admin@fwo.com / password123";
    }

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}

// ==========================================
// 3. FORM SUBMISSION HANDLING (LOGIN)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message)) {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if (empty($email) || empty($pass)) {
        $error_message = "Please fill in both fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($pass, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirect to dashboard
                header("Location: https://career.fwo.com.pk/"); 
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error_message = "Login Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - FWO</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* =========================================
           CSS STYLES (Same as your layout)
           ========================================= */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* --- 1. Navbar Styles --- */
        .navbar {
            background-color: #0a1128;
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .logo-circle {
            width: 40px;
            height: 40px;
            background-color: #dc2626;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #cbd5e1;
        }

        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .sign-up-link {
            color: white;
            text-decoration: none;
        }

        .sign-in-btn-nav {
            background-color: #dc2626;
            color: white;
            text-decoration: none;
            padding: 8px 24px;
            border-radius: 6px;
            transition: background 0.3s;
        }

        .sign-in-btn-nav:hover {
            background-color: #b91c1c;
        }

        /* --- 2. Main Content & Background Pattern --- */
        .main-content {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            background-color: #fcf8f8;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f0e6e6' fill-opacity='0.6'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* --- 3. Login Card Styles --- */
        .login-card {
            background: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 500px;
        }

        .card-title {
            text-align: center;
            color: #4a5568;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: 700;
        }

        /* Alerts */
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background-color: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            background-color: #f8f9fa;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .input-group input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .forgot-password-container {
            text-align: right;
            margin-bottom: 25px;
        }

        .forgot-link {
            color: #dc2626;
            text-decoration: none;
            font-size: 13px;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .submit-btn-container {
            display: flex;
            justify-content: center;
        }

        .submit-btn {
            background-color: #0a1128;
            color: white;
            border: none;
            padding: 14px 40px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .submit-btn:hover {
            background-color: #1e293b;
        }

        .bottom-link {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #64748b;
        }

        .bottom-link a {
            color: #4a5568;
            font-weight: 600;
            text-decoration: none;
        }

        .bottom-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- 1. Navbar -->
    <nav class="navbar">
        <div class="logo-container">
            <div class="logo-circle">LOGO</div>
        </div>
        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#">Jobs</a>
            <a href="#">About</a>
            <a href="#">Contact Us</a>
        </div>
        <div class="auth-buttons">
            <a href="#" class="sign-up-link">Sign Up</a>
            <a href="#" class="sign-in-btn-nav">Sign In</a>
        </div>
    </nav>

    <!-- 2. Main Content Area -->
    <main class="main-content">
        
        <!-- 3. Login Card -->
        <div class="login-card">
            <h1 class="card-title">Sign In</h1>

            <!-- Success Message (Default user creation) -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login-form">
                
                <div class="input-group">
                    <label for="email">Enter Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="input-group">
                    <label for="password">Enter Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="forgot-password-container">
                    <a href="#" class="forgot-link">Forget Password? &raquo;</a>
                </div>

                <div class="submit-btn-container">
                    <button type="submit" class="submit-btn">Sign In</button>
                </div>

            </form>

            <div class="bottom-link">
                Not a Registered User? <a href="#">Create an account &raquo;</a>
            </div>
        </div>

    </main>

</body>
</html>