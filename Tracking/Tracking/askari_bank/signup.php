<?php
// If Proceed is clicked and checkbox is checked, redirect to login.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['agree'])) {
        header("Location: login.php");
        exit;
    } else {
        $error = "Please agree to the Terms and Conditions.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Askari Bank iNet Banking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #fff;
      font-family: Arial, sans-serif;
    }
    .header {
      background-color: #0088cc;
      color: white;
      padding: 10px;
      font-size: 18px;
      text-align: center;
      font-weight: bold;
    }
    .card {
      margin: 40px auto;
      max-width: 900px;
      border-radius: 0;
      border: 1px solid #ccc;
    }
    .card-header {
      background-color: #1c5d99;
      color: white;
      font-weight: bold;
    }
    .btn-proceed {
      background-color: #1c5d99;
      color: white;
      border-radius: 3px;
    }
    .btn-cancel {
      background-color: #e5e5e5;
      color: black;
      border-radius: 3px;
    }
    .footer {
      background-color: #1c5d99;
      color: white;
      text-align: center;
      padding: 10px;
      margin-top: 30px;
      font-size: 13px;
    }
    .logo {
      margin: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .error {
      color: red;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <!-- Top Logo Section -->
  <div class="logo">
    <img src="askari_digital_logo.png" alt="Askari Digital" height="60">
    <img src="askari_bank_logo.png" alt="Askari Bank" height="50">
  </div>

  <!-- Header -->
  <div class="header">
    Welcome to Askari Bank iNet Banking
  </div>

  <!-- Register Card -->
  <div class="card">
    <div class="card-header">Register</div>
    <div class="card-body">
      <p>
        Please read Askari internet banking Terms &amp; Conditions carefully as access and use of Askari internet banking service is subject to these Terms and Conditions. 
        By accessing this Web Site or any pages thereof, the Customer unconditionally and irrevocably agrees to be bound by the Terms and Conditions. 
        If the Customer does not agree with these Terms and Conditions, please do not access this Web Site or any pages thereof. 
        In order to signify your agreement to the Terms and Conditions, please check the “I Agree” checkbox and press submit to proceed further with the sign up process.
      </p>

      <form method="post">
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="agree" name="agree">
          <label class="form-check-label" for="agree">
            I have read and agree to the Askari Internet Banking Terms and Conditions.
          </label>
        </div>

        <?php if (!empty($error)) { echo "<div class='error'>$error</div>"; } ?>

        <button type="submit" class="btn btn-proceed">Proceed</button>
        <button type="button" class="btn btn-cancel" onclick="window.location.href='index.php'">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    Copyright © 2025 Askari Bank. All rights reserved. Terms of Service - Copyright/IP Policy Helpline: 111-000-787
  </div>

</body>
</html>
