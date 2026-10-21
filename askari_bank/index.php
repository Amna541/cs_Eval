<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Askari Bank Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            background-color: #004a8e;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .checkbox {
            margin-bottom: 20px;
        }

        button {
            padding: 10px 20px;
            background-color: #004a8e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button.cancel {
            background-color: #ccc;
            margin-left: 10px;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Page 1 -->
    <div class="container" id="terms-page">
        <h2>Register</h2>
        <p>Please read Askari internet banking Terms & Conditions carefully. By accessing this service, you agree to be bound by these terms. If you do not agree, do not proceed further.</p>
        <form action="page2.php" method="post">
            <div class="checkbox">
                <input type="checkbox" id="agree" name="agree" required>
                <label for="agree">I have read and agree to the Askari Internet Banking Terms and Conditions.</label>
            </div>
            <button type="submit">Proceed</button>
            <button type="button" class="cancel" onclick="window.location.href='index.html';">Cancel</button>
        </form>
    </div>

    <!-- Page 2 -->
    
</body>
</html>