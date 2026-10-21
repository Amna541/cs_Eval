<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hackathon 2025</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <img src="ieee.jpg" alt="Hackathon Logo">
            </div>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="event-info.html">Event Info</a></li>
                <li><a href="judging.html">Judging</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
    </header>

    <section class="registration-form">
        <h2>Register for Hackathon 2025</h2>
        <form action="register.php" method="POST">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <input type="text" name="team" placeholder="Team Name" required>
            <button type="submit" class="btn">Submit</button>
        </form>
    </section>

    <footer>
        <p>&copy; 2025 Hackathon 2025. All Rights Reserved.</p>
    </footer>
</body>
</html>
