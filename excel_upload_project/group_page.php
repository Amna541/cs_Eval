<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Data</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Group: <?php echo $_GET['group']; ?></h2>
        
        <h3>Upload Excel File</h3>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="groupName" placeholder="Enter Group Name" required>
    <input type="file" name="excelFile" required>
    <button type="submit">Upload</button>
</form>


        <h3>Or Enter Manually</h3>
        <form action="generate_links.php" method="POST">
            <input type="hidden" name="group_name" value="<?php echo $_GET['group']; ?>">
            <input type="text" name="phone_number" placeholder="Enter Phone Number">
            <input type="email" name="email" placeholder="Enter Email (optional)">
            <button type="submit">Generate Links</button>
        </form>
    </div>
</body>
</html>
