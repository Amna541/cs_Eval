<?php
session_start();

// Handle reset request
if (isset($_GET['clear_feedback'])) {
    unset($_SESSION['correct_answers']);
    unset($_SESSION['user_answers']);
    header('Location: training.php?tab=assessment');
    exit;
}

// Initialize tracking if not exists
if (!isset($_SESSION['training_data'])) {
    $_SESSION['training_data'] = [
        'assessments' => [],
        'manual_entries' => [],
        'progress' => []
    ];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle MCQ Assessment
    if (isset($_POST['submit_mcqs'])) {
        $score = 0;
        $total_questions = 20;
        $answers = $_POST['answers'] ?? [];
        
        // Correct answers with explanations
        $questions_data = getQuestionsData();
        $correct_answers = [];
        $user_responses = [];
        
        foreach ($questions_data as $q => $data) {
            $correct_answers[$q] = $data['correct'];
            
            if (isset($answers[$q])) {
                $user_responses[$q] = [
                    'selected' => $answers[$q],
                    'correct' => $answers[$q] === $data['correct'],
                    'explanation' => $data['explanation']
                ];
                
                if ($answers[$q] === $data['correct']) {
                    $score++;
                }
            }
        }
        
        $percentage = ($score / $total_questions) * 100;
        $level = getLevel($percentage);
        
        // Store assessment
        $assessment = [
            'score' => $score,
            'total' => $total_questions,
            'percentage' => $percentage,
            'level' => $level,
            'responses' => $user_responses,
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'mcq'
        ];
        
        $_SESSION['training_data']['assessments'][] = $assessment;
        $_SESSION['training_data']['progress']['last_assessment'] = $assessment;
        
        // Store correct answers and user answers in session for immediate feedback
        $_SESSION['correct_answers'] = $correct_answers;
        $_SESSION['user_answers'] = $answers;
        
        header('Location: training.php?results=1&tab=assessment');
        exit;
    }
    
    // Handle Manual Entry
    if (isset($_POST['submit_manual'])) {
        $manual_entry = [
            'employee_info' => [
                'name' => $_POST['employee_name'] ?? '',
                'department' => $_POST['department'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? ''
            ],
            'responses' => [
                'security_concerns' => $_POST['security_concerns'] ?? '',
                'training_needs' => $_POST['training_needs'] ?? '',
                'incident_report' => $_POST['incident_report'] ?? '',
                'suggestions' => $_POST['suggestions'] ?? '',
                'compliance_questions' => [
                    'q1' => $_POST['compliance_q1'] ?? '',
                    'q2' => $_POST['compliance_q2'] ?? '',
                    'q3' => $_POST['compliance_q3'] ?? ''
                ]
            ],
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'manual'
        ];
        
        $_SESSION['training_data']['manual_entries'][] = $manual_entry;
        
        header('Location: training.php?manual_success=1&tab=manual');
        exit;
    }
    
    // Handle Progress Tracking
    if (isset($_POST['update_progress'])) {
        $progress = [
            'modules_completed' => $_POST['modules_completed'] ?? [],
            'confidence_level' => $_POST['confidence_level'] ?? 0,
            'training_hours' => $_POST['training_hours'] ?? 0,
            'goals' => $_POST['goals'] ?? '',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $_SESSION['training_data']['progress']['tracking'] = $progress;
        
        header('Location: training.php?progress_updated=1&tab=progress');
        exit;
    }
}

// Helper functions
function getQuestionsData() {
    return [
        'q1' => [
            'correct' => 'c',
            'explanation' => 'Phishing emails are the most common attack vector, accounting for 90% of data breaches.'
        ],
        'q2' => [
            'correct' => 'b',
            'explanation' => 'Always report suspicious emails to your IT security team for investigation.'
        ],
        'q3' => [
            'correct' => 'c',
            'explanation' => 'MFA uses multiple verification methods (something you know, have, or are).'
        ],
        'q4' => [
            'correct' => 'a',
            'explanation' => 'Passwords should be changed every 60-90 days as per NIST guidelines.'
        ],
        'q5' => [
            'correct' => 'b',
            'explanation' => 'VPN creates an encrypted tunnel for secure remote connections.'
        ],
        'q6' => [
            'correct' => 'c',
            'explanation' => 'Social engineering manipulates human psychology rather than technical vulnerabilities.'
        ],
        'q7' => [
            'correct' => 'a',
            'explanation' => 'VPN encrypts your connection on public Wi-Fi, protecting from eavesdropping.'
        ],
        'q8' => [
            'correct' => 'b',
            'explanation' => 'Ransomware encrypts files and demands payment for decryption.'
        ],
        'q9' => [
            'correct' => 'c',
            'explanation' => 'HTTPS and padlock indicate encrypted SSL/TLS connections.'
        ],
        'q10' => [
            'correct' => 'a',
            'explanation' => 'Updates patch security vulnerabilities discovered since the last version.'
        ],
        'q11' => [
            'correct' => 'c',
            'explanation' => 'Zero Trust assumes no implicit trust and verifies every access request.'
        ],
        'q12' => [
            'correct' => 'b',
            'explanation' => 'DLP prevents unauthorized data transfer through monitoring and blocking.'
        ],
        'q13' => [
            'correct' => 'c',
            'explanation' => 'A rootkit provides persistent, undetectable access by modifying system files.'
        ],
        'q14' => [
            'correct' => 'a',
            'explanation' => 'GDPR focuses on data protection and privacy for EU citizens.'
        ],
        'q15' => [
            'correct' => 'b',
            'explanation' => 'SSO allows access to multiple applications with one set of credentials.'
        ],
        'q16' => [
            'correct' => 'c',
            'explanation' => 'A honeypot is a decoy system designed to attract and study attackers.'
        ],
        'q17' => [
            'correct' => 'd',
            'explanation' => 'An APT is a prolonged, targeted attack often by nation-state actors.'
        ],
        'q18' => [
            'correct' => 'a',
            'explanation' => 'Salting adds random data to passwords before hashing to prevent rainbow table attacks.'
        ],
        'q19' => [
            'correct' => 'b',
            'explanation' => 'A worm self-replicates across networks without user intervention.'
        ],
        'q20' => [
            'correct' => 'c',
            'explanation' => 'BAS simulates attacks to continuously validate security controls.'
        ]
    ];
}

function getLevel($percentage) {
    if ($percentage >= 90) return 'Expert';
    if ($percentage >= 75) return 'Advanced';
    if ($percentage >= 60) return 'Intermediate';
    if ($percentage >= 40) return 'Beginner';
    return 'Novice';
}

function getColorByLevel($level) {
    $colors = [
        'Expert' => '#10B981',
        'Advanced' => '#3B82F6',
        'Intermediate' => '#F59E0B',
        'Beginner' => '#EF4444',
        'Novice' => '#DC2626'
    ];
    return $colors[$level] ?? '#6B7280';
}

// Get data
$training_data = $_SESSION['training_data'] ?? [];
$latest_assessment = end($training_data['assessments']) ?: null;
$total_assessments = count($training_data['assessments']);
$total_manual = count($training_data['manual_entries']);
$progress = $training_data['progress']['tracking'] ?? null;

// Check if assessment was just submitted for immediate feedback
$show_feedback = isset($_SESSION['correct_answers']) && isset($_SESSION['user_answers']);
$correct_answers = $_SESSION['correct_answers'] ?? [];
$user_answers = $_SESSION['user_answers'] ?? [];

// Function to get option class based on answer
function getOptionClass($question_num, $option_value) {
    global $show_feedback, $correct_answers, $user_answers;
    
    if (!$show_feedback) return '';
    
    $question = 'q' . $question_num;
    $class = '';
    
    if (isset($user_answers[$question]) && $user_answers[$question] === $option_value) {
        $class .= 'selected ';
        
        if (isset($correct_answers[$question]) && $correct_answers[$question] === $option_value) {
            $class .= 'correct';
        } else {
            $class .= 'incorrect';
        }
    } else if (isset($correct_answers[$question]) && $correct_answers[$question] === $option_value) {
        // Highlight correct answer if user got it wrong
        if (isset($user_answers[$question]) && $user_answers[$question] !== $option_value) {
            $class .= 'correct';
        }
    }
    
    return trim($class);
}

// Function to check if option should be disabled
function isOptionDisabled() {
    global $show_feedback;
    return $show_feedback ? 'disabled' : '';
}

// Function to check if option is selected
function isOptionSelected($question_num, $option_value) {
    global $show_feedback, $user_answers;
    
    if (!$show_feedback) return '';
    
    $question = 'q' . $question_num;
    return (isset($user_answers[$question]) && $user_answers[$question] === $option_value) ? 'checked' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Cybersecurity Training Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #f8961e;
            --dark: #212529;
            --light: #f8f9fa;
            --border: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--success));
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-icon {
            font-size: 2.5em;
            color: var(--primary);
        }
        
        .logo-text h1 {
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 2.2em;
        }
        
        .logo-text p {
            color: #6c757d;
            font-size: 1.1em;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px 25px;
            background: var(--light);
            border-radius: 12px;
            border: 1px solid var(--border);
            min-width: 140px;
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .tabs-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .tabs-header {
            display: flex;
            background: var(--light);
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }
        
        .tab-btn {
            padding: 20px 30px;
            background: none;
            border: none;
            font-size: 1.1em;
            font-weight: 600;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tab-btn:hover {
            background: rgba(67, 97, 238, 0.05);
            color: var(--primary);
        }
        
        .tab-btn.active {
            color: var(--primary);
            background: white;
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary);
        }
        
        .tab-badge {
            background: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.8em;
        }
        
        .tab-content {
            padding: 40px;
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-title {
            color: var(--dark);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .tab-title i {
            color: var(--primary);
        }
        
        /* MCQ Assessment Styles */
        .assessment-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .questions-grid {
            display: grid;
            gap: 25px;
        }
        
        .question-card {
            background: var(--light);
            border-radius: 15px;
            padding: 25px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .question-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        
        .question-number {
            position: absolute;
            top: -12px;
            left: 20px;
            background: var(--primary);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .question-text {
            font-size: 1.1em;
            margin-bottom: 20px;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .options-grid {
            display: grid;
            gap: 12px;
        }
        
        .option {
            padding: 15px;
            background: white;
            border: 2px solid var(--border);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .option:hover {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }
        
        .option input:checked + .option-label {
            color: var(--primary);
            font-weight: 600;
        }
        
        .option input:checked ~ .option-content {
            color: var(--primary);
        }
        
        .option input {
            display: none;
        }
        
        .option-label {
            background: var(--light);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6c757d;
            flex-shrink: 0;
        }
        
        .option-content {
            color: #495057;
            flex-grow: 1;
        }
        
        /* New Styles for Answer Feedback */
        .option.correct {
            border-color: var(--success) !important;
            background: rgba(16, 185, 129, 0.1) !important;
        }
        
        .option.incorrect {
            border-color: var(--danger) !important;
            background: rgba(239, 68, 68, 0.1) !important;
        }
        
        .option.selected {
            border-width: 3px;
            font-weight: 600;
        }
        
        .option.correct .option-label {
            background: var(--success);
            color: white;
        }
        
        .option.incorrect .option-label {
            background: var(--danger);
            color: white;
        }
        
        .option.disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .option.disabled:hover {
            border-color: inherit;
            background: inherit;
        }
        
        .feedback-icon {
            margin-left: auto;
            font-size: 1.2em;
        }
        
        .correct .feedback-icon {
            color: var(--success);
        }
        
        .incorrect .feedback-icon {
            color: var(--danger);
        }
        
        .option.correct:not(.selected) {
            border-style: dashed;
            border-width: 2px;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: var(--light);
            border-radius: 15px;
            padding: 25px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .progress-circle {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            position: relative;
        }
        
        .progress-ring {
            transform: rotate(-90deg);
        }
        
        .progress-ring-circle {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
        }
        
        .progress-value {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2em;
            font-weight: bold;
            color: var(--primary);
        }
        
        /* Results Page */
        .results-container {
            display: grid;
            gap: 30px;
        }
        
        .results-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
        }
        
        .score-display {
            font-size: 4em;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .level-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1em;
            margin: 10px 0;
        }
        
        .detailed-results {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .result-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        /* Manual Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            color: var(--dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-label i {
            color: var(--primary);
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .compliance-section {
            background: var(--light);
            padding: 25px;
            border-radius: 15px;
            margin: 30px 0;
        }
        
        /* Progress Tracking */
        .progress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .module-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            border: 2px solid var(--border);
            transition: all 0.3s ease;
        }
        
        .module-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
        }
        
        .module-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .module-icon {
            width: 50px;
            height: 50px;
            background: var(--light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            color: var(--primary);
        }
        
        .module-checkbox {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: 2px solid var(--border);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .module-checkbox.checked {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        /* Dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .dashboard-card h3 {
            margin-bottom: 20px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Buttons */
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
        }
        
        .btn-block {
            width: 100%;
            margin-top: 30px;
        }
        
        /* Alerts */
        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 5px solid;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .assessment-container {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
            }
        }
        
        @media (max-width: 768px) {
            .tabs-header {
                flex-direction: column;
            }
            
            .tab-btn {
                padding: 15px;
                justify-content: center;
            }
            
            .tab-content {
                padding: 20px;
            }
            
            .detailed-results {
                grid-template-columns: 1fr;
            }
        }
        
        .timer {
            background: var(--warning);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            margin: 20px 0;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .review-mode {
            background: #e7f5ff;
            border: 2px solid #4cc9f0;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-shield-alt logo-icon"></i>
                    <div class="logo-text">
                        <h1>CyberShield Training</h1>
                        <p>Advanced Cybersecurity Awareness Platform</p>
                    </div>
                </div>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $total_assessments; ?></div>
                        <div class="stat-label">Assessments</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $total_manual; ?></div>
                        <div class="stat-label">Reports</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">
                            <?php echo $latest_assessment ? $latest_assessment['level'] : 'N/A'; ?>
                        </div>
                        <div class="stat-label">Current Level</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_GET['results'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Assessment submitted successfully! Review your answers below.
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['manual_success'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-check-circle"></i>
            Manual entry submitted successfully! Thank you for your feedback.
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-btn active" onclick="switchTab('dashboard')">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                    <span class="tab-badge">New</span>
                </button>
                <button class="tab-btn" onclick="switchTab('assessment')">
                    <i class="fas fa-question-circle"></i>
                    Assessment
                    <span class="tab-badge">20 Qs</span>
                </button>
                <button class="tab-btn" onclick="switchTab('results')">
                    <i class="fas fa-chart-bar"></i>
                    Results
                </button>
                <button class="tab-btn" onclick="switchTab('manual')">
                    <i class="fas fa-edit"></i>
                    Manual Report
                </button>
                <button class="tab-btn" onclick="switchTab('progress')">
                    <i class="fas fa-tasks"></i>
                    Progress
                </button>
                <button class="tab-btn" onclick="switchTab('resources')">
                    <i class="fas fa-graduation-cap"></i>
                    Resources
                </button>
            </div>

            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <div class="tab-title">
                    <i class="fas fa-tachometer-alt"></i>
                    Training Dashboard
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <h3><i class="fas fa-chart-line"></i> Performance Overview</h3>
                        <canvas id="performanceChart" height="200"></canvas>
                    </div>
                    
                    <div class="dashboard-card">
                        <h3><i class="fas fa-shield-alt"></i> Security Level</h3>
                        <div class="progress-circle">
                            <svg class="progress-ring" width="150" height="150">
                                <circle class="progress-ring-circle" stroke="#e6e6e6" stroke-width="8" fill="transparent" r="68" cx="75" cy="75"/>
                                <circle class="progress-ring-circle" stroke="<?php echo $latest_assessment ? getColorByLevel($latest_assessment['level']) : '#4361ee'; ?>" stroke-width="8" fill="transparent" r="68" cx="75" cy="75" stroke-dasharray="427" stroke-dashoffset="0" id="levelCircle"/>
                            </svg>
                            <div class="progress-value">
                                <?php echo $latest_assessment ? $latest_assessment['level'][0] : 'N/A'; ?>
                            </div>
                        </div>
                        <div style="text-align: center; margin-top: 15px;">
                            <h3><?php echo $latest_assessment ? $latest_assessment['level'] : 'Not Assessed'; ?></h3>
                            <p style="color: #6c757d;">Current Security Level</p>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <h3><i class="fas fa-bullseye"></i> Quick Actions</h3>
                        <div style="display: grid; gap: 15px; margin-top: 20px;">
                            <button class="btn btn-primary" onclick="switchTab('assessment')">
                                <i class="fas fa-play-circle"></i> Start Assessment
                            </button>
                            <button class="btn btn-primary" onclick="switchTab('progress')">
                                <i class="fas fa-tasks"></i> Update Progress
                            </button>
                            <button class="btn btn-primary" onclick="switchTab('resources')">
                                <i class="fas fa-download"></i> Download Resources
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assessment Tab -->
            <div id="assessment" class="tab-content">
                <div class="tab-title">
                    <i class="fas fa-question-circle"></i>
                    Cybersecurity Assessment (20 Questions)
                </div>
                
                <?php if ($show_feedback): ?>
                <div class="review-mode">
                    <h4><i class="fas fa-info-circle"></i> Review Mode</h4>
                    <p><strong>✅ Green Border:</strong> Correct Answer | <strong>❌ Red Border:</strong> Your Incorrect Answer | <strong>✅ Dashed Green:</strong> Correct Answer (if you missed it)</p>
                    <p>Answers are locked. <a href="?clear_feedback=1&tab=assessment" style="color: var(--primary); font-weight: bold;">Click here to retake assessment</a></p>
                </div>
                <?php else: ?>
                <div class="timer">
                    <i class="fas fa-clock"></i>
                    Estimated time: 15-20 minutes
                </div>
                <?php endif; ?>
                
                <form method="POST" id="assessmentForm">
                    <div class="assessment-container">
                        <div class="questions-grid">
                            <!-- Question 1 -->
                            <div class="question-card">
                                <div class="question-number">1</div>
                                <div class="question-text">What is the primary goal of a phishing attack?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(1, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q1]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(1, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">To overload a network with traffic</span>
                                        <?php if ($show_feedback && isOptionSelected(1, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(1, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(1, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q1]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(1, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">To encrypt files for ransom</span>
                                        <?php if ($show_feedback && isOptionSelected(1, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(1, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(1, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q1]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(1, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">To steal sensitive information</span>
                                        <?php if ($show_feedback && isOptionSelected(1, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(1, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(1, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q1]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(1, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">To create backdoor access</span>
                                        <?php if ($show_feedback && isOptionSelected(1, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(1, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 2 -->
                            <div class="question-card">
                                <div class="question-number">2</div>
                                <div class="question-text">Which action should you take upon receiving a suspicious email requesting credentials?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(2, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q2]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(2, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Reply asking for verification</span>
                                        <?php if ($show_feedback && isOptionSelected(2, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(2, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(2, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q2]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(2, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Report to IT security team</span>
                                        <?php if ($show_feedback && isOptionSelected(2, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(2, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(2, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q2]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(2, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Forward to colleagues for awareness</span>
                                        <?php if ($show_feedback && isOptionSelected(2, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(2, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(2, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q2]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(2, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Ignore and delete it</span>
                                        <?php if ($show_feedback && isOptionSelected(2, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(2, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 3 -->
                            <div class="question-card">
                                <div class="question-number">3</div>
                                <div class="question-text">What does MFA (Multi-Factor Authentication) typically combine?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(3, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q3]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(3, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Multiple email addresses</span>
                                        <?php if ($show_feedback && isOptionSelected(3, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(3, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(3, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q3]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(3, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Different user roles</span>
                                        <?php if ($show_feedback && isOptionSelected(3, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(3, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(3, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q3]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(3, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Knowledge, possession, and biometrics</span>
                                        <?php if ($show_feedback && isOptionSelected(3, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(3, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(3, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q3]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(3, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Various passwords</span>
                                        <?php if ($show_feedback && isOptionSelected(3, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(3, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 4 -->
                            <div class="question-card">
                                <div class="question-number">4</div>
                                <div class="question-text">How frequently should passwords be changed according to current best practices?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(4, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q4]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(4, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Every 60-90 days</span>
                                        <?php if ($show_feedback && isOptionSelected(4, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(4, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(4, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q4]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(4, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Once a year</span>
                                        <?php if ($show_feedback && isOptionSelected(4, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(4, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(4, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q4]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(4, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Only when compromised</span>
                                        <?php if ($show_feedback && isOptionSelected(4, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(4, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(4, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q4]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(4, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Every 30 days</span>
                                        <?php if ($show_feedback && isOptionSelected(4, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(4, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 5 -->
                            <div class="question-card">
                                <div class="question-number">5</div>
                                <div class="question-text">What is the primary purpose of a VPN (Virtual Private Network)?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(5, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q5]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(5, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">To increase internet speed</span>
                                        <?php if ($show_feedback && isOptionSelected(5, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(5, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(5, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q5]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(5, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">To create a secure encrypted connection</span>
                                        <?php if ($show_feedback && isOptionSelected(5, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(5, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(5, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q5]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(5, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">To block advertisements</span>
                                        <?php if ($show_feedback && isOptionSelected(5, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(5, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(5, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q5]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(5, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">To bypass website restrictions</span>
                                        <?php if ($show_feedback && isOptionSelected(5, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(5, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 6 -->
                            <div class="question-card">
                                <div class="question-number">6</div>
                                <div class="question-text">What is social engineering in cybersecurity?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(6, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q6]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(6, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Engineering social media algorithms</span>
                                        <?php if ($show_feedback && isOptionSelected(6, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(6, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(6, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q6]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(6, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Hacking social media accounts</span>
                                        <?php if ($show_feedback && isOptionSelected(6, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(6, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(6, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q6]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(6, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Manipulating people to divulge information</span>
                                        <?php if ($show_feedback && isOptionSelected(6, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(6, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(6, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q6]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(6, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Analyzing social network data</span>
                                        <?php if ($show_feedback && isOptionSelected(6, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(6, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 7 -->
                            <div class="question-card">
                                <div class="question-number">7</div>
                                <div class="question-text">When using public Wi-Fi, what should you always do first?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(7, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q7]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(7, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Connect to a VPN</span>
                                        <?php if ($show_feedback && isOptionSelected(7, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(7, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(7, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q7]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(7, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Disable your firewall</span>
                                        <?php if ($show_feedback && isOptionSelected(7, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(7, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(7, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q7]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(7, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Check for free promotions</span>
                                        <?php if ($show_feedback && isOptionSelected(7, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(7, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(7, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q7]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(7, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Share the network password</span>
                                        <?php if ($show_feedback && isOptionSelected(7, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(7, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 8 -->
                            <div class="question-card">
                                <div class="question-number">8</div>
                                <div class="question-text">What characterizes ransomware attacks?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(8, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q8]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(8, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">They protect against virus attacks</span>
                                        <?php if ($show_feedback && isOptionSelected(8, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(8, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(8, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q8]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(8, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">They encrypt files and demand payment</span>
                                        <?php if ($show_feedback && isOptionSelected(8, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(8, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(8, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q8]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(8, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">They steal data without encryption</span>
                                        <?php if ($show_feedback && isOptionSelected(8, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(8, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(8, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q8]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(8, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">They only affect mobile devices</span>
                                        <?php if ($show_feedback && isOptionSelected(8, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(8, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 9 -->
                            <div class="question-card">
                                <div class="question-number">9</div>
                                <div class="question-text">How can you verify a website is secure before entering credentials?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(9, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q9]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(9, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Check for "www" in the URL</span>
                                        <?php if ($show_feedback && isOptionSelected(9, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(9, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(9, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q9]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(9, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">See if it loads quickly</span>
                                        <?php if ($show_feedback && isOptionSelected(9, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(9, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(9, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q9]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(9, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Look for HTTPS and padlock icon</span>
                                        <?php if ($show_feedback && isOptionSelected(9, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(9, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(9, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q9]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(9, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Verify the website design quality</span>
                                        <?php if ($show_feedback && isOptionSelected(9, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(9, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 10 -->
                            <div class="question-card">
                                <div class="question-number">10</div>
                                <div class="question-text">Why are regular software updates important for security?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(10, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q10]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(10, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">They fix security vulnerabilities</span>
                                        <?php if ($show_feedback && isOptionSelected(10, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(10, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(10, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q10]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(10, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">They only add new features</span>
                                        <?php if ($show_feedback && isOptionSelected(10, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(10, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(10, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q10]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(10, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">They improve performance only</span>
                                        <?php if ($show_feedback && isOptionSelected(10, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(10, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(10, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q10]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(10, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">They change the user interface</span>
                                        <?php if ($show_feedback && isOptionSelected(10, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(10, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Questions 11-20 -->
                            <!-- Question 11 -->
                            <div class="question-card">
                                <div class="question-number">11</div>
                                <div class="question-text">What is the "Zero Trust" security model?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(11, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q11]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(11, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Trust no software applications</span>
                                        <?php if ($show_feedback && isOptionSelected(11, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(11, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(11, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q11]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(11, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Disable all network access</span>
                                        <?php if ($show_feedback && isOptionSelected(11, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(11, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(11, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q11]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(11, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Verify every access request</span>
                                        <?php if ($show_feedback && isOptionSelected(11, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(11, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(11, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q11]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(11, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Trust but verify approach</span>
                                        <?php if ($show_feedback && isOptionSelected(11, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(11, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 12 -->
                            <div class="question-card">
                                <div class="question-number">12</div>
                                <div class="question-text">What does DLP (Data Loss Prevention) primarily do?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(12, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q12]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(12, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Prevents data encryption</span>
                                        <?php if ($show_feedback && isOptionSelected(12, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(12, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(12, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q12]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(12, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Monitors and blocks data transfer</span>
                                        <?php if ($show_feedback && isOptionSelected(12, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(12, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(12, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q12]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(12, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Accelerates data transmission</span>
                                        <?php if ($show_feedback && isOptionSelected(12, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(12, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(12, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q12]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(12, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Compresses data for storage</span>
                                        <?php if ($show_feedback && isOptionSelected(12, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(12, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 13 -->
                            <div class="question-card">
                                <div class="question-number">13</div>
                                <div class="question-text">Which malware type hides its existence from users and antivirus?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(13, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q13]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(13, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Trojan Horse</span>
                                        <?php if ($show_feedback && isOptionSelected(13, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(13, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(13, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q13]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(13, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Spyware</span>
                                        <?php if ($show_feedback && isOptionSelected(13, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(13, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(13, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q13]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(13, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Rootkit</span>
                                        <?php if ($show_feedback && isOptionSelected(13, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(13, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(13, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q13]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(13, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Adware</span>
                                        <?php if ($show_feedback && isOptionSelected(13, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(13, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 14 -->
                            <div class="question-card">
                                <div class="question-number">14</div>
                                <div class="question-text">What does GDPR stand for and focus on?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(14, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q14]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(14, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">General Data Protection Regulation - Privacy</span>
                                        <?php if ($show_feedback && isOptionSelected(14, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(14, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(14, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q14]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(14, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">General Digital Privacy Rules - Security</span>
                                        <?php if ($show_feedback && isOptionSelected(14, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(14, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(14, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q14]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(14, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Global Data Protection Rules - Encryption</span>
                                        <?php if ($show_feedback && isOptionSelected(14, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(14, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(14, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q14]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(14, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">General Device Protection Regulation - Access</span>
                                        <?php if ($show_feedback && isOptionSelected(14, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(14, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 15 -->
                            <div class="question-card">
                                <div class="question-number">15</div>
                                <div class="question-text">What is the purpose of Single Sign-On (SSO)?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(15, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q15]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(15, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">To use one password forever</span>
                                        <?php if ($show_feedback && isOptionSelected(15, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(15, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(15, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q15]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(15, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">To access multiple apps with one login</span>
                                        <?php if ($show_feedback && isOptionSelected(15, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(15, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(15, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q15]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(15, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">To sign on from single location only</span>
                                        <?php if ($show_feedback && isOptionSelected(15, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(15, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(15, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q15]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(15, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">To reduce authentication security</span>
                                        <?php if ($show_feedback && isOptionSelected(15, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(15, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 16 -->
                            <div class="question-card">
                                <div class="question-number">16</div>
                                <div class="question-text">What is a "honeypot" in cybersecurity?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(16, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q16]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(16, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">A sweet-talking social engineer</span>
                                        <?php if ($show_feedback && isOptionSelected(16, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(16, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(16, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q16]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(16, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">A type of encryption method</span>
                                        <?php if ($show_feedback && isOptionSelected(16, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(16, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(16, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q16]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(16, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">A decoy system to attract attackers</span>
                                        <?php if ($show_feedback && isOptionSelected(16, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(16, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(16, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q16]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(16, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">A password management tool</span>
                                        <?php if ($show_feedback && isOptionSelected(16, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(16, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 17 -->
                            <div class="question-card">
                                <div class="question-number">17</div>
                                <div class="question-text">What characterizes an Advanced Persistent Threat (APT)?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(17, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q17]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(17, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Quick, automated attacks</span>
                                        <?php if ($show_feedback && isOptionSelected(17, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(17, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(17, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q17]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(17, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Targets only individuals</span>
                                        <?php if ($show_feedback && isOptionSelected(17, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(17, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(17, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q17]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(17, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Uses only phishing techniques</span>
                                        <?php if ($show_feedback && isOptionSelected(17, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(17, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(17, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q17]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(17, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Long-term, targeted intrusion</span>
                                        <?php if ($show_feedback && isOptionSelected(17, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(17, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 18 -->
                            <div class="question-card">
                                <div class="question-number">18</div>
                                <div class="question-text">What is "salting" in password security?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(18, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q18]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(18, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Adding random data before hashing</span>
                                        <?php if ($show_feedback && isOptionSelected(18, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(18, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(18, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q18]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(18, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Encrypting passwords twice</span>
                                        <?php if ($show_feedback && isOptionSelected(18, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(18, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(18, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q18]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(18, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Making passwords longer</span>
                                        <?php if ($show_feedback && isOptionSelected(18, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(18, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(18, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q18]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(18, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Adding special characters</span>
                                        <?php if ($show_feedback && isOptionSelected(18, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(18, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 19 -->
                            <div class="question-card">
                                <div class="question-number">19</div>
                                <div class="question-text">What differentiates a worm from a virus?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(19, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q19]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(19, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Worms need user action to spread</span>
                                        <?php if ($show_feedback && isOptionSelected(19, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(19, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(19, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q19]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(19, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Worms self-replicate across networks</span>
                                        <?php if ($show_feedback && isOptionSelected(19, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(19, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(19, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q19]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(19, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Viruses are more dangerous</span>
                                        <?php if ($show_feedback && isOptionSelected(19, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(19, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(19, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q19]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(19, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Viruses only affect files</span>
                                        <?php if ($show_feedback && isOptionSelected(19, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(19, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Question 20 -->
                            <div class="question-card">
                                <div class="question-number">20</div>
                                <div class="question-text">What is Breach and Attack Simulation (BAS)?</div>
                                <div class="options-grid">
                                    <label class="option <?php echo getOptionClass(20, 'a'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q20]" value="a" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(20, 'a'); ?> <?php if (!$show_feedback) echo 'required'; ?>>
                                        <span class="option-label">A</span>
                                        <span class="option-content">Simulating data breaches for training</span>
                                        <?php if ($show_feedback && isOptionSelected(20, 'a')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(20, 'a') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(20, 'b'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q20]" value="b" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(20, 'b'); ?>>
                                        <span class="option-label">B</span>
                                        <span class="option-content">Testing employee responses to attacks</span>
                                        <?php if ($show_feedback && isOptionSelected(20, 'b')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(20, 'b') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(20, 'c'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q20]" value="c" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(20, 'c'); ?>>
                                        <span class="option-label">C</span>
                                        <span class="option-content">Automated security control validation</span>
                                        <?php if ($show_feedback && isOptionSelected(20, 'c')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(20, 'c') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <label class="option <?php echo getOptionClass(20, 'd'); ?>" <?php if ($show_feedback) echo 'style="cursor: default;"'; ?>>
                                        <input type="radio" name="answers[q20]" value="d" <?php echo isOptionDisabled(); ?> <?php echo isOptionSelected(20, 'd'); ?>>
                                        <span class="option-label">D</span>
                                        <span class="option-content">Simulating social engineering attacks</span>
                                        <?php if ($show_feedback && isOptionSelected(20, 'd')): ?>
                                            <span class="feedback-icon">
                                                <?php if (getOptionClass(20, 'd') === 'selected correct'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar">
                            <h3 style="margin-bottom: 20px; color: var(--dark);">Assessment Progress</h3>
                            <div class="progress-circle">
                                <svg class="progress-ring" width="150" height="150">
                                    <circle class="progress-ring-circle" stroke="#e6e6e6" stroke-width="8" fill="transparent" r="68" cx="75" cy="75"/>
                                    <circle class="progress-ring-circle" stroke="#4361ee" stroke-width="8" fill="transparent" r="68" cx="75" cy="75" stroke-dasharray="427" stroke-dashoffset="320" id="progressCircle"/>
                                </svg>
                                <div class="progress-value" id="progressValue">0%</div>
                            </div>
                            <div style="text-align: center; margin: 20px 0;">
                                <p><strong>Questions Answered:</strong> <span id="answeredCount">0</span>/20</p>
                                <?php if (!$show_feedback): ?>
                                <p><strong>Time:</strong> <span id="timer">15:00</span></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!$show_feedback): ?>
                            <button type="submit" name="submit_mcqs" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Submit Assessment
                            </button>
                            <?php else: ?>
                            <a href="?clear_feedback=1&tab=assessment" class="btn btn-primary btn-block">
                                <i class="fas fa-redo"></i> Retake Assessment
                            </a>
                            <?php endif; ?>
                            
                            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 10px;">
                                <p style="color: #856404; font-size: 0.9em;">
                                    <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> 
                                    <?php if ($show_feedback): ?>
                                    Review your answers. Green = Correct, Red = Incorrect
                                    <?php else: ?>
                                    Read each question carefully before answering.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Results Tab -->
            <div id="results" class="tab-content">
                <div class="tab-title">
                    <i class="fas fa-chart-bar"></i>
                    Assessment Results
                </div>
                
                <?php if ($latest_assessment): ?>
                <div class="results-header">
                    <h2>Your Latest Assessment Results</h2>
                    <div class="score-display"><?php echo $latest_assessment['score']; ?>/20</div>
                    <div class="level-badge" style="background: <?php echo getColorByLevel($latest_assessment['level']); ?>;">
                        <?php echo $latest_assessment['level']; ?> Level
                    </div>
                    <p style="margin-top: 10px;">Completed on <?php echo date('F j, Y g:i A', strtotime($latest_assessment['timestamp'])); ?></p>
                </div>
                
                <div class="detailed-results">
                    <div class="result-card">
                        <h3><i class="fas fa-percentage"></i> Score Breakdown</h3>
                        <canvas id="scoreChart" height="200"></canvas>
                    </div>
                    
                    <div class="result-card">
                        <h3><i class="fas fa-check-circle"></i> Performance Summary</h3>
                        <p><strong>Overall Score:</strong> <?php echo round($latest_assessment['percentage'], 2); ?>%</p>
                        <p><strong>Correct Answers:</strong> <?php echo $latest_assessment['score']; ?></p>
                        <p><strong>Incorrect Answers:</strong> <?php echo 20 - $latest_assessment['score']; ?></p>
                        <p><strong>Security Level:</strong> <?php echo $latest_assessment['level']; ?></p>
                        <hr style="margin: 15px 0;">
                        <h4>Recommendations:</h4>
                        <ul style="padding-left: 20px; margin-top: 10px;">
                            <?php
                            $recommendations = [];
                            if ($latest_assessment['percentage'] < 60) {
                                $recommendations = [
                                    'Complete basic cybersecurity training',
                                    'Review phishing identification techniques',
                                    'Practice password security best practices'
                                ];
                            } elseif ($latest_assessment['percentage'] < 80) {
                                $recommendations = [
                                    'Take advanced security modules',
                                    'Practice incident response scenarios',
                                    'Review network security concepts'
                                ];
                            } else {
                                $recommendations = [
                                    'Consider security certification',
                                    'Mentor colleagues in security awareness',
                                    'Stay updated with latest threats'
                                ];
                            }
                            
                            foreach ($recommendations as $rec) {
                                echo "<li>$rec</li>";
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="result-card">
                        <h3><i class="fas fa-history"></i> Assessment History</h3>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: var(--light);">
                                        <th style="padding: 10px; text-align: left;">Date</th>
                                        <th style="padding: 10px; text-align: left;">Score</th>
                                        <th style="padding: 10px; text-align: left;">Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_reverse($training_data['assessments']) as $index => $assessment): ?>
                                    <tr style="border-bottom: 1px solid var(--border);">
                                        <td style="padding: 10px;"><?php echo date('M j, Y', strtotime($assessment['timestamp'])); ?></td>
                                        <td style="padding: 10px;"><?php echo $assessment['score']; ?>/20</td>
                                        <td style="padding: 10px;">
                                            <span style="background: <?php echo getColorByLevel($assessment['level']); ?>; color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.8em;">
                                                <?php echo $assessment['level']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No assessment results available. Please complete the assessment first.
                </div>
                <?php endif; ?>
            </div>

            <!-- Manual Report Tab -->
            <div id="manual" class="tab-content">
                <div class="tab-title">
                    <i class="fas fa-edit"></i>
                    Manual Security Report
                </div>
                
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="employee_name"><i class="fas fa-user"></i> Full Name *</label>
                            <input type="text" class="form-control" id="employee_name" name="employee_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="department"><i class="fas fa-building"></i> Department *</label>
                            <select class="form-control" id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="IT">IT & Security</option>
                                <option value="HR">Human Resources</option>
                                <option value="Finance">Finance</option>
                                <option value="Operations">Operations</option>
                                <option value="Sales">Sales & Marketing</option>
                                <option value="Management">Management</option>
                                <option value="R&D">Research & Development</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="role"><i class="fas fa-briefcase"></i> Job Role *</label>
                            <input type="text" class="form-control" id="role" name="role" required>
                        </div>
                    </div>
                    
                    <div class="compliance-section">
                        <h3><i class="fas fa-clipboard-check"></i> Security Compliance Questions</h3>
                        
                        <div class="form-group">
                            <label class="form-label">1. Have you completed mandatory security training this year? *</label>
                            <select class="form-control" name="compliance_q1" required>
                                <option value="">Select option</option>
                                <option value="yes">Yes, completed</option>
                                <option value="no">Not yet completed</option>
                                <option value="scheduled">Scheduled for later</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">2. Do you report all security incidents immediately? *</label>
                            <select class="form-control" name="compliance_q2" required>
                                <option value="">Select option</option>
                                <option value="always">Always</option>
                                <option value="usually">Usually</option>
                                <option value="sometimes">Sometimes</option>
                                <option value="rarely">Rarely</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">3. Are you familiar with the incident response procedure? *</label>
                            <select class="form-control" name="compliance_q3" required>
                                <option value="">Select option</option>
                                <option value="very">Very familiar</option>
                                <option value="somewhat">Somewhat familiar</option>
                                <option value="not_very">Not very familiar</option>
                                <option value="not_at_all">Not at all familiar</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="security_concerns"><i class="fas fa-exclamation-triangle"></i> Top Security Concerns *</label>
                        <textarea class="form-control" id="security_concerns" name="security_concerns" required placeholder="Describe your main cybersecurity concerns..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="training_needs"><i class="fas fa-graduation-cap"></i> Training Needs Assessment *</label>
                        <textarea class="form-control" id="training_needs" name="training_needs" required placeholder="What cybersecurity training do you need?..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="incident_report"><i class="fas fa-flag"></i> Incident Reports (Optional)</label>
                        <textarea class="form-control" id="incident_report" name="incident_report" placeholder="Report any security incidents you've encountered..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="suggestions"><i class="fas fa-lightbulb"></i> Improvement Suggestions (Optional)</label>
                        <textarea class="form-control" id="suggestions" name="suggestions" placeholder="Suggestions for improving organizational security..."></textarea>
                    </div>
                    
                    <button type="submit" name="submit_manual" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Submit Security Report
                    </button>
                </form>
            </div>

            <!-- Progress Tracking Tab -->
            <div id="progress" class="tab-content">
                <div class="tab-title">
                    <i class="fas fa-tasks"></i>
                    Training Progress Tracking
                </div>
                
                <form method="POST">
                    <div class="progress-grid">
                        <!-- Training Modules -->
                        <div class="module-card">
                            <div class="module-header">
                                <div class="module-icon">
                                    <i class="fas fa-phishing"></i>
                                </div>
                                <div>
                                    <h4>Phishing Awareness</h4>
                                    <p style="color: #6c757d; font-size: 0.9em;">Identify and report phishing attempts</p>
                                </div>
                            </div>
                            <div class="module-checkbox" onclick="toggleCheckbox(this, 'module1')" id="module1-checkbox"></div>
                            <input type="hidden" name="modules_completed[]" id="module1" value="">
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header">
                                <div class="module-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div>
                                    <h4>Password Security</h4>
                                    <p style="color: #6c757d; font-size: 0.9em;">Create and manage secure passwords</p>
                                </div>
                            </div>
                            <div class="module-checkbox" onclick="toggleCheckbox(this, 'module2')" id="module2-checkbox"></div>
                            <input type="hidden" name="modules_completed[]" id="module2" value="">
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header">
                                <div class="module-icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <h4>Social Engineering</h4>
                                    <p style="color: #6c757d; font-size: 0.9em;">Defend against manipulation tactics</p>
                                </div>
                            </div>
                            <div class="module-checkbox" onclick="toggleCheckbox(this, 'module3')" id="module3-checkbox"></div>
                            <input type="hidden" name="modules_completed[]" id="module3" value="">
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header">
                                <div class="module-icon">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                                <div>
                                    <h4>Network Security</h4>
                                    <p style="color: #6c757d; font-size: 0.9em;">Secure network connections and devices</p>
                                </div>
                            </div>
                            <div class="module-checkbox" onclick="toggleCheckbox(this, 'module4')" id="module4-checkbox"></div>
                            <input type="hidden" name="modules_completed[]" id="module4" value="">
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header">
                                <div class="module-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div>
                                    <h4>Mobile Security</h4>
                                    <p style="color: #6c757d; font-size: 0.9em;">Protect mobile devices and data</p>
                                </div>
                            </div>
                            <div class="module-checkbox" onclick="toggleCheckbox(this, 'module5')" id="module5-checkbox"></div>
                            <input type="hidden" name="modules_completed[]" id="module5" value="">
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header">
                                <div class="module-icon">
                                    <i class="fas fa-file-contract"></i>
                                </div>
                                <div>
                                    <h4>Compliance</h4>
                                    <p style="color: #6c757d; font-size: 0.9em;">Understand regulatory requirements</p>
                                </div>
                            </div>
                            <div class="module-checkbox" onclick="toggleCheckbox(this, 'module6')" id="module6-checkbox"></div>
                            <input type="hidden" name="modules_completed[]" id="module6" value="">
                        </div>
                    </div>
                    
                    <div class="form-grid" style="margin-top: 30px;">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-chart-line"></i> Security Confidence Level</label>
                            <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                                <span style="color: #6c757d;">Low</span>
                                <input type="range" name="confidence_level" min="1" max="10" value="5" style="flex-grow: 1;">
                                <span style="color: #6c757d;">High</span>
                                <span id="confidenceValue" style="font-weight: bold; color: var(--primary);">5</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-clock"></i> Training Hours Completed</label>
                            <input type="number" class="form-control" name="training_hours" min="0" max="100" value="0" step="0.5">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-bullseye"></i> Security Goals for Next Quarter</label>
                        <textarea class="form-control" name="goals" placeholder="What are your cybersecurity learning goals?..."></textarea>
                    </div>
                    
                    <button type="submit" name="update_progress" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Save Progress
                    </button>
                </form>
            </div>

            <!-- Resources Tab -->
            <div id="resources" class="tab-content">
                <div class="tab-title">
                    <i class="fas fa-graduation-cap"></i>
                    Training Resources & Materials
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <h3><i class="fas fa-download"></i> Downloadable Resources</h3>
                        <div style="margin-top: 20px;">
                            <a href="#" class="btn btn-primary" style="margin-bottom: 10px; display: block; text-align: left;">
                                <i class="fas fa-file-pdf"></i> Security Policy Handbook (PDF)
                            </a>
                            <a href="#" class="btn btn-primary" style="margin-bottom: 10px; display: block; text-align: left;">
                                <i class="fas fa-file-excel"></i> Incident Report Template (Excel)
                            </a>
                            <a href="#" class="btn btn-primary" style="margin-bottom: 10px; display: block; text-align: left;">
                                <i class="fas fa-file-powerpoint"></i> Security Awareness Presentation
                            </a>
                            <a href="#" class="btn btn-primary" style="display: block; text-align: left;">
                                <i class="fas fa-file-word"></i> Compliance Checklist (Word)
                            </a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <h3><i class="fas fa-video"></i> Video Training</h3>
                        <div style="margin-top: 20px;">
                            <div style="background: var(--light); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                                <strong>Phishing Identification</strong>
                                <p style="color: #6c757d; font-size: 0.9em; margin: 5px 0;">15 min • Beginner</p>
                                <a href="#" style="color: var(--primary);">Watch Now →</a>
                            </div>
                            <div style="background: var(--light); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                                <strong>Password Management</strong>
                                <p style="color: #6c757d; font-size: 0.9em; margin: 5px 0;">20 min • Intermediate</p>
                                <a href="#" style="color: var(--primary);">Watch Now →</a>
                            </div>
                            <div style="background: var(--light); padding: 15px; border-radius: 10px;">
                                <strong>Incident Response</strong>
                                <p style="color: #6c757d; font-size: 0.9em; margin: 5px 0;">25 min • Advanced</p>
                                <a href="#" style="color: var(--primary);">Watch Now →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <h3><i class="fas fa-tools"></i> Security Tools</h3>
                        <div style="margin-top: 20px;">
                            <ul style="padding-left: 20px;">
                                <li><strong>Password Managers:</strong> LastPass, 1Password</li>
                                <li><strong>VPN Services:</strong> NordVPN, ExpressVPN</li>
                                <li><strong>Antivirus:</strong> Bitdefender, Norton</li>
                                <li><strong>Encryption:</strong> VeraCrypt, AxCrypt</li>
                                <li><strong>Backup:</strong> Backblaze, Acronis</li>
                            </ul>
                            <div style="margin-top: 20px; padding: 15px; background: #e7f5ff; border-radius: 10px;">
                                <p style="margin: 0; color: #0c5460;">
                                    <i class="fas fa-info-circle"></i> Contact IT for approved software installations.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 30px; background: white; padding: 25px; border-radius: 15px;">
                    <h3><i class="fas fa-question-circle"></i> Frequently Asked Questions</h3>
                    <div style="margin-top: 20px;">
                        <div style="margin-bottom: 15px;">
                            <strong>Q: How often should I take security training?</strong>
                            <p style="color: #6c757d; margin-top: 5px;">A: Complete annual mandatory training, with quarterly refreshers recommended.</p>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong>Q: What should I do if I suspect a security breach?</strong>
                            <p style="color: #6c757d; margin-top: 5px;">A: Immediately report to IT security team and follow incident response procedures.</p>
                        </div>
                        <div>
                            <strong>Q: Are personal devices allowed for work?</strong>
                            <p style="color: #6c757d; margin-top: 5px;">A: Only if compliant with BYOD policy and approved by IT security.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Set clicked button as active
            event.currentTarget.classList.add('active');
            
            // Update URL
            history.pushState(null, null, `?tab=${tabName}`);
        }
        
        // Initialize based on URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam && document.getElementById(tabParam)) {
            const tabBtn = document.querySelector(`.tab-btn[onclick="switchTab('${tabParam}')"]`);
            if (tabBtn) tabBtn.click();
        }
        
        // Progress tracking for assessment
        function updateProgress() {
            const answered = document.querySelectorAll('input[type="radio"]:checked').length;
            const total = 20;
            const percentage = (answered / total) * 100;
            
            document.getElementById('answeredCount').textContent = answered;
            document.getElementById('progressValue').textContent = Math.round(percentage) + '%';
            
            const circle = document.getElementById('progressCircle');
            const circumference = 2 * Math.PI * 68;
            const offset = circumference - (percentage / 100) * circumference;
            circle.style.strokeDashoffset = offset;
        }
        
        // Timer functionality
        let timeLeft = 15 * 60; // 15 minutes in seconds
        const timerElement = document.getElementById('timer');
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft > 0) {
                timeLeft--;
            } else {
                clearInterval(timerInterval);
                document.getElementById('assessmentForm').submit();
            }
        }
        
        let timerInterval;
        if (document.getElementById('assessment') && !<?php echo $show_feedback ? 'true' : 'false'; ?>) {
            timerInterval = setInterval(updateTimer, 1000);
            updateTimer();
        }
        
        // Update progress when radio buttons are clicked
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', updateProgress);
        });
        
        // Initialize progress
        document.addEventListener('DOMContentLoaded', updateProgress);
        
        // Toggle checkbox for progress tracking
        function toggleCheckbox(element, moduleId) {
            const checkbox = element;
            const hiddenInput = document.getElementById(moduleId);
            
            if (checkbox.classList.contains('checked')) {
                checkbox.classList.remove('checked');
                checkbox.innerHTML = '';
                hiddenInput.value = '';
            } else {
                checkbox.classList.add('checked');
                checkbox.innerHTML = '<i class="fas fa-check"></i>';
                hiddenInput.value = moduleId.replace('module', 'Module ');
            }
        }
        
        // Confidence level display
        const confidenceSlider = document.querySelector('input[name="confidence_level"]');
        const confidenceValue = document.getElementById('confidenceValue');
        if (confidenceSlider) {
            confidenceSlider.addEventListener('input', function() {
                confidenceValue.textContent = this.value;
            });
        }
        
        // Charts
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($latest_assessment): ?>
            // Score Chart
            const scoreCtx = document.getElementById('scoreChart')?.getContext('2d');
            if (scoreCtx) {
                new Chart(scoreCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Correct', 'Incorrect'],
                        datasets: [{
                            data: [<?php echo $latest_assessment['score']; ?>, <?php echo 20 - $latest_assessment['score']; ?>],
                            backgroundColor: ['#10B981', '#EF4444'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            
            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart')?.getContext('2d');
            if (performanceCtx) {
                const assessments = <?php echo json_encode(array_slice($training_data['assessments'], -5)); ?>;
                const labels = assessments.map(a => new Date(a.timestamp).toLocaleDateString());
                const scores = assessments.map(a => a.percentage);
                
                new Chart(performanceCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Score %',
                            data: scores,
                            borderColor: '#4361ee',
                            backgroundColor: 'rgba(67, 97, 238, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
            <?php endif; ?>
        });
        
        // Level circle animation
        document.addEventListener('DOMContentLoaded', function() {
            const levelCircle = document.getElementById('levelCircle');
            if (levelCircle && <?php echo $latest_assessment ? 'true' : 'false'; ?>) {
                const level = '<?php echo $latest_assessment ? $latest_assessment["level"] : ""; ?>';
                const levels = {Novice: 0.2, Beginner: 0.4, Intermediate: 0.6, Advanced: 0.8, Expert: 1};
                const percentage = levels[level] || 0.5;
                
                const circumference = 2 * Math.PI * 68;
                const offset = circumference - (percentage) * circumference;
                levelCircle.style.strokeDashoffset = offset;
            }
        });
        
        // Prevent clicks on disabled options in review mode
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($show_feedback): ?>
            document.querySelectorAll('.option').forEach(option => {
                option.style.cursor = 'default';
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                });
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>