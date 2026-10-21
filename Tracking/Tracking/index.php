<!DOCTYPE html>
<html>
<head>
    <title>Generate Tracking Links</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        form {
            margin: 20px auto;
            width: 300px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h2>Enter Contact to Generate Links</h2>
    <form action="generate.php" method="POST">
        <input type="text" name="contact" placeholder="Enter Phone or Email" required>
        <button type="submit">Generate Links</button>
    </form>
</body>
</html>
