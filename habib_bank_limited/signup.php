<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBL Signup</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="signup-container">
        <h2>HBL Bank Signup</h2>
        <form action="register.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="text" name="cnic" placeholder="Enter CNIC (XXXXX-XXXXXXX-X)" required pattern="\d{5}-\d{7}-\d">
            
            <label for="cnic-upload" class="upload-container">
                <i class="fas fa-camera"></i>
                <span>Upload CNIC</span>
                <input type="file" name="cnic_image" id="cnic-upload" accept="image/*" required>
            </label>

            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>
