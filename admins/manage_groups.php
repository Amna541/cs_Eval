<?php
include('db_connect.php');

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

$search = "";
$query = "SELECT * FROM trace_data ORDER BY id DESC";

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM trace_data 
              WHERE contact LIKE '%$search%' 
                 OR group_name LIKE '%$search%' 
              ORDER BY id DESC";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title> HQ Cyber Div 101</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="image.png" type="image/x-icon">
    <link rel="icon" href="F:\xampp\htdocs\admins\image.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --danger: #f72585;
            --success: #4cc9f0;
            --warning: #f8961e;
            --border-radius: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        /* Light Mode */
        :root {
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #212529;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --hover-bg: #f1f5ff;
            --table-header: #f1f5ff;
            --table-row: #ffffff;
            --table-row-alt: #f8f9fa;
        }

        /* Dark Mode */
        [data-theme="dark"] {
            --primary: #5a72e9;
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #f8f9fa;
            --text-muted: #adb5bd;
            --border-color: #444;
            --hover-bg: #2a2a2a;
            --table-header: #2a2a2a;
            --table-row: #1e1e1e;
            --table-row-alt: #252525;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            transition: var(--transition);
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .particles {
            position: absolute;
            display: block;
            background: rgba(67, 97, 238, 0.1);
            animation: float 15s infinite linear;
            border-radius: 50%;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }

        .content {
    margin-left: auto; /* This centers the container */
    margin-right: auto; /* This centers the container */
    padding: 2rem;
    animation: fadeIn 0.6s ease-out;
    position: relative;
    z-index: 1;
    background-color: rgba(var(--bg-color-rgb), 0.9);
    max-width: 1400px; /* Constrains width */
    width: calc(100% - 80px); /* Optional: adds some side margins */
}

        /* Convert color variables to RGB for background transparency */
        :root {
            --bg-color-rgb: 248, 249, 250;
            --card-bg-rgb: 255, 255, 255;
        }

        [data-theme="dark"] {
            --bg-color-rgb: 18, 18, 18;
            --card-bg-rgb: 30, 30, 30;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Security Shield Animation */
        @keyframes shieldPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .security-badge {
            animation: shieldPulse 2s infinite;
            color: var(--primary);
        }

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            background: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .theme-toggle:hover {
            transform: rotate(30deg) scale(1.1);
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #3a0ca3 100%);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
            transform: translateY(-20px);
            opacity: 0;
            animation: slideDown 0.6s 0.2s ease-out forwards;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            animation: rotate 20s linear infinite;
            z-index: 0;
        }

        .page-header h2 {
            position: relative;
            z-index: 1;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes slideDown {
            from { 
                opacity: 0;
                transform: translateY(-20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Search Form */
        .search-form {
            background-color: rgba(var(--card-bg-rgb), 0.95);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
            border: 1px solid var(--border-color);
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s 0.4s ease-out forwards;
            backdrop-filter: blur(5px);
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        .search-form .form-control {
            background-color: rgba(var(--card-bg-rgb), 0.8);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            transition: var(--transition);
        }

        .search-form .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
            background-color: rgba(var(--card-bg-rgb), 1);
        }

        /* Data Table */
        .data-table {
            background-color: rgba(var(--card-bg-rgb), 0.95);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            border: 1px solid var(--border-color);
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s 0.6s ease-out forwards;
            backdrop-filter: blur(5px);
        }

        .data-table th {
            background-color: rgba(var(--table-header), 0.9);
            color: var(--text-color);
            font-weight: 600;
            padding: 1rem;
            position: relative;
        }

        .data-table th::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), transparent);
        }

        .data-table td {
            padding: 0.75rem 1rem;
            border-color: var(--border-color);
            vertical-align: middle;
            color: var(--text-color);
        }

        .data-table tr {
            background-color: rgba(var(--table-row), 0.9);
            transition: var(--transition);
        }

        .data-table tr:hover {
            background-color: var(--hover-bg);
        }

        .data-table tr:nth-child(even) {
            background-color: rgba(var(--table-row-alt), 0.9);
        }

        .data-table tr:nth-child(even):hover {
            background-color: var(--hover-bg);
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 4px;
        }

        /* Buttons */
        .btn {
            transition: var(--transition);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #d91a6b 100%);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-danger::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .btn-danger:focus:not(:active)::after {
            animation: ripple 0.6s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--danger) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            transition: var(--transition);
            z-index: 100;
            animation: pulse 2s infinite;
        }

        .fab:hover {
            transform: scale(1.1) rotate(90deg);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .content {
                margin-left: 0;
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1rem;
            }
            
            .data-table {
                font-size: 0.9rem;
            }
        }

        /* Text Visibility Enhancements */
        .text-contrast {
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }

        .alert-primary {
            background: rgba(67, 97, 238, 0.15);
            border: 1px solid rgba(67, 97, 238, 0.2);
            color: var(--text-color);
            backdrop-filter: blur(5px);
        }

        /* Glow effect for security elements */
        .glow {
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                box-shadow: 0 0 5px rgba(67, 97, 238, 0.5);
            }
            to {
                box-shadow: 0 0 20px rgba(67, 97, 238, 0.8);
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="background" id="particlesBackground"></div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon" id="themeIcon"></i>
    </button>

    <div class="content">
        <div class="page-header">
            <h2 class="text-contrast"><i class="fas fa-shield-alt security-badge"></i> Secure Tracking Manager</h2>
        </div>

        <!-- Search Form -->
        <form method="GET" class="search-form">
            <div class="input-group">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by contact or group..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>

        <!-- Security Status Bar -->
        <div class="alert alert-primary d-flex align-items-center mb-3 glow" role="alert">
            <i class="fas fa-lock me-2"></i>
            <div>
                Secure connection established <span class="badge bg-success ms-2">Verified</span>
            </div>
        </div>

        <div class="table-responsive data-table">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Group</th>
                        <th>Contact</th>
                        <th>OS</th>
                        <th>Device</th>
                        <th>User Agent</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr style="opacity: 0; animation: fadeIn 0.5s forwards; animation-delay: <?= 0.7 + ($row['id'] * 0.01) ?>s;">
                            <td><span class="badge bg-primary">#<?= $row['id'] ?></span></td>
                            <td><?= htmlspecialchars($row['group_name']) ?></td>
                            <td><?= htmlspecialchars($row['contact']) ?></td>
                            <td><span class="badge bg-success"><?= htmlspecialchars($row['os']) ?></span></td>
                            <td><?= htmlspecialchars($row['device']) ?></td>
                            <td class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['agent']) ?>">
                                <?= htmlspecialchars($row['agent']) ?>
                            </td>
                            <td><?= htmlspecialchars($row['ip_address']) ?></td>
                            <td><?= date('M d, Y H:i', strtotime($row['clicked_at'])) ?></td>
                            <td>
                                <a href="delete_record.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirmDeletion();">
                                   <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="admin.php" class="fab" title="Add New Record">
    <i class="fa-solid fa-house fa-bounce"></i>  <!-- Font Awesome 6+ -->

    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const currentTheme = localStorage.getItem('theme') || 'light';

        // Apply saved theme
        document.documentElement.setAttribute('data-theme', currentTheme);
        themeIcon.className = currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

        themeToggle.addEventListener('click', () => {
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeIcon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });

        // Create animated background particles
        function createParticles() {
            const background = document.getElementById('particlesBackground');
            const particleCount = window.innerWidth < 768 ? 20 : 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particles');
                
                // Random properties
                const size = Math.random() * 20 + 10;
                const posX = Math.random() * window.innerWidth;
                const delay = Math.random() * 15;
                const duration = Math.random() * 10 + 10;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}px`;
                particle.style.bottom = `-${size}px`;
                particle.style.animationDuration = `${duration}s`;
                particle.style.animationDelay = `${delay}s`;
                
                background.appendChild(particle);
            }
        }

        // Security Animation for Delete Action
        function confirmDeletion() {
            // Show security verification animation
            const securityShield = document.createElement('div');
            securityShield.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                            background: rgba(0,0,0,0.7); z-index: 9999; display: flex; 
                            flex-direction: column; align-items: center; justify-content: center;
                            animation: fadeIn 0.3s ease-out;">
                    <div style="background: var(--card-bg); padding: 2rem; border-radius: var(--border-radius);
                                max-width: 500px; width: 90%; text-align: center; box-shadow: var(--box-shadow);
                                border: 1px solid var(--primary);">
                        <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--primary); 
                                                           animation: shieldPulse 1s infinite;"></i>
                        <h3 class="mt-3">Security Verification</h3>
                        <p class="mb-4">Are you sure you want to delete this record? This action cannot be undone.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <button id="confirmDelete" class="btn btn-danger px-4">
                                <i class="fas fa-trash-alt"></i> Confirm Delete
                            </button>
                            <button id="cancelDelete" class="btn btn-secondary px-4">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(securityShield);
            
            // Handle button clicks
            securityShield.querySelector('#confirmDelete').addEventListener('click', () => {
                securityShield.style.animation = 'fadeOut 0.3s ease-out forwards';
                setTimeout(() => {
                    securityShield.remove();
                    return true;
                }, 300);
            });
            
            securityShield.querySelector('#cancelDelete').addEventListener('click', () => {
                securityShield.style.animation = 'fadeOut 0.3s ease-out forwards';
                setTimeout(() => {
                    securityShield.remove();
                    return false;
                }, 300);
            });
            
            // Prevent default form submission
            return false;
        }

        // Add CSS for security modal animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            @keyframes shieldPulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
        `;
        document.head.appendChild(style);

        // Initialize particles when page loads
        window.addEventListener('load', createParticles);
    </script>
</body>
</html>