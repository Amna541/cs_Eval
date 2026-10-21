<?php
// Start output buffering at the VERY BEGINNING - NO WHITESPACE BEFORE THIS
ob_start();

// Increase execution time and memory limit for large files
ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '512M');

session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_management');
require 'vendor/autoload.php'; // PhpSpreadsheet autoload
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
class ChunkReadFilter implements IReadFilter
{
    private int $startRow = 0;
    private int $endRow = 0;

    public function setRows(int $startRow, int $chunkSize): void
    {
        $this->startRow = $startRow;
        $this->endRow   = $startRow + $chunkSize;
    }

    public function readCell($column, $row, $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row < $this->endRow;
    }
}

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if they don't exist
$createGroupsTable = "CREATE TABLE IF NOT EXISTS groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    designation VARCHAR(255),
    mobile VARCHAR(50),
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    INDEX idx_group_id (group_id)
)";

if (!$conn->query($createGroupsTable) || !$conn->query($createUsersTable)) {
    die("Error creating tables: " . $conn->error);
}

// Initialize groups and users array if not exists (for session fallback)
if (!isset($_SESSION['groups'])) {
    $_SESSION['groups'] = [];
}

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_group'])) {
        $groupName = trim($_POST['group_name']);
        if (!empty($groupName)) {
            if (preg_match('/^[a-zA-Z0-9\- ]+$/', $groupName)) {
                // Check if group exists in database
                $stmt = $conn->prepare("SELECT id FROM groups WHERE name = ?");
                $stmt->bind_param("s", $groupName);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    // Insert into database
                    $stmt = $conn->prepare("INSERT INTO groups (name) VALUES (?)");
                    $stmt->bind_param("s", $groupName);
                    
                    if ($stmt->execute()) {
                        $_SESSION['groups'][] = $groupName;
                        $_POST['group'] = $groupName;
                        $_SESSION['message'] = "Group '$groupName' created successfully!";
                    } else {
                        $_SESSION['error'] = "Error creating group: " . $conn->error;
                    }
                } else {
                    $_SESSION['error'] = "Group '$groupName' already exists!";
                }
            } else {
                $_SESSION['error'] = "Group name can only contain letters, numbers, spaces and hyphens!";
            }
        } else {
            $_SESSION['error'] = "Please enter a group name!";
        }
        ob_end_clean(); // Clear buffer before redirect
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } 

    elseif (isset($_POST['upload_excel'])) {
        $groupName = $_POST['group'];

        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
            // Check file extension
            $allowedExtensions = ['xlsx', 'xls', 'csv'];
            $fileName = $_FILES['excel_file']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (!in_array($fileExt, $allowedExtensions)) {
                $_SESSION['error'] = "Invalid file type. Please upload Excel files (.xlsx, .xls) or CSV.";
                ob_end_clean(); // Clear buffer before redirect
                header("Location: " . $_SERVER['PHP_SELF'] . "?group=" . urlencode($groupName));
                exit();
            }

            // Get group ID first
            $stmt = $conn->prepare("SELECT id FROM groups WHERE name = ?");
            $stmt->bind_param("s", $groupName);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $_SESSION['error'] = "Group not found!";
                ob_end_clean(); // Clear buffer before redirect
                header("Location: " . $_SERVER['PHP_SELF'] . "?group=" . urlencode($groupName));
                exit();
            }
            
            $group = $result->fetch_assoc();
            $groupId = $group['id'];
            
            $filePath = $_FILES['excel_file']['tmp_name'];
            $successCount = 0;
            $errorCount = 0;
            $importedUsers = [];
            
            // For CSV files (faster for large datasets)
            if ($fileExt === 'csv') {
                try {
                    $handle = fopen($filePath, 'r');
                    if ($handle !== false) {
                        $rowCount = 0;
                        $conn->autocommit(FALSE); // Start transaction
                        
                        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                            $rowCount++;
                            // Skip header row
                            if ($rowCount === 1) continue;
                            
                            // Clean the data - Pakistan Government Format
                            // A = Sr# (0) - SKIP
                            // B = NAME (1)
                            // C = PI# (2) - Optional
                            // D = BPS (3) - Optional
                            // E = DESIGN (4) - Designation
                            // F = DEPTT (5) - Optional
                            // G = CNIC (6) - Optional (could be email)
                            // H = CONTACT (7) - Mobile number
                            
                            $userName = isset($row[1]) ? trim($row[1]) : '';
                            $mobile = isset($row[7]) ? trim($row[7]) : '';
                            $designation = isset($row[4]) ? trim($row[4]) : '';
                            $email = isset($row[6]) ? trim($row[6]) : '';
                            
                            // Skip empty rows
                            if (empty($userName) || empty($mobile)) {
                                $errorCount++;
                                continue;
                            }
                            
                            // Pakistan Mobile Validation
                            $mobile = preg_replace('/[^0-9]/', '', $mobile);
                            
                            if (strlen($mobile) < 10) {
                                $errorCount++;
                                continue;
                            }
                            
                            // Keep only last 10 digits if longer
                            if (strlen($mobile) > 10) {
                                $mobile = substr($mobile, -10);
                            }
                            
                            // Generate email if not provided or invalid
                            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', '', $userName);
                                $email = strtolower(str_replace(' ', '.', $cleanName)) . '@example.com';
                                $email = substr($email, 0, 50);
                            }
                            
                            // Add PI# and BPS to designation if available
                            if (isset($row[2]) && !empty(trim($row[2]))) {
                                $piNumber = trim($row[2]);
                                if (!empty($designation)) {
                                    $designation .= " (PI#: $piNumber)";
                                } else {
                                    $designation = "PI#: $piNumber";
                                }
                            }
                            
                            if (isset($row[3]) && !empty(trim($row[3]))) {
                                $bps = trim($row[3]);
                                if (!empty($designation)) {
                                    $designation .= " BPS-$bps";
                                } else {
                                    $designation = "BPS-$bps";
                                }
                            }
                            
                            // Insert into users table
                            try {
                                $stmt = $conn->prepare("INSERT INTO users (group_id, name, designation, mobile, email) VALUES (?, ?, ?, ?, ?)");
                                $stmt->bind_param("issss", $groupId, $userName, $designation, $mobile, $email);
                                
                                if ($stmt->execute()) {
                                    $successCount++;
                                    if ($successCount <= 20) { // Store only first 20 names for display
                                        $importedUsers[] = $userName;
                                    }
                                } else {
                                    $errorCount++;
                                }
                            } catch (Exception $e) {
                                $errorCount++;
                            }
                            
                            // REMOVED: Show progress every 100 rows
                            // if ($rowCount % 100 === 0) {
                            //     flush(); // THIS CAUSES HEADERS ALREADY SENT ERROR
                            // }
                        }
                        fclose($handle);
                        $conn->commit();
                        $conn->autocommit(TRUE);
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    $conn->autocommit(TRUE);
                    $_SESSION['error'] = "Error processing CSV file: " . $e->getMessage();
                    ob_end_clean(); // Clear buffer before redirect
                    header("Location: " . $_SERVER['PHP_SELF'] . "?group=" . urlencode($groupName));
                    exit();
                }
            } 
            // For Excel files (optimized for large files)
            else {
                try {
                    // Optimized loading for large Excel files
                    $reader = IOFactory::createReader(ucfirst($fileExt));
                    $reader->setReadDataOnly(true); // Read only data, not formatting
                    $reader->setReadEmptyCells(false); // Skip empty cells
                    
                    // For XLSX files, use chunk reading
                    if ($fileExt === 'xlsx') {
                        $chunkFilter = new ChunkReadFilter();
                        $reader->setReadFilter($chunkFilter);
                        
                        // Read in chunks of 500 rows
                        $chunkSize = 500;
                        $chunkFilter->setRows(0, $chunkSize);
                        
                        $totalRows = 0;
                        $conn->autocommit(FALSE); // Start transaction
                        
                        // Read file in chunks
                        for ($startRow = 1; $startRow <= 1500; $startRow += $chunkSize) {
                            $chunkFilter->setRows($startRow, $chunkSize);
                            $spreadsheet = $reader->load($filePath);
                            $sheet = $spreadsheet->getActiveSheet();
                            $rows = $sheet->toArray();
                            
                            if (empty($rows)) break;
                            
                            foreach ($rows as $index => $row) {
                                $actualRow = $startRow + $index;
                                // Skip header row
                                if ($actualRow === 1) continue;
                                
                                // Clean the data - Pakistan Government Format
                                // A = Sr# (0) - SKIP
                                // B = NAME (1)
                                // C = PI# (2) - Optional
                                // D = BPS (3) - Optional
                                // E = DESIGN (4) - Designation
                                // F = DEPTT (5) - Optional
                                // G = CNIC (6) - Optional (could be email)
                                // H = CONTACT (7) - Mobile number
                                
                                $userName = isset($row[1]) ? trim($row[1]) : '';
                                $mobile = isset($row[7]) ? trim($row[7]) : '';
                                $designation = isset($row[4]) ? trim($row[4]) : '';
                                $email = isset($row[6]) ? trim($row[6]) : '';
                                
                                // Skip empty rows
                                if (empty($userName) || empty($mobile)) {
                                    $errorCount++;
                                    continue;
                                }
                                
                                // Pakistan Mobile Validation
                                $mobile = preg_replace('/[^0-9]/', '', $mobile);
                                
                                if (strlen($mobile) < 10) {
                                    $errorCount++;
                                    continue;
                                }
                                
                                // Keep only last 10 digits if longer
                                if (strlen($mobile) > 10) {
                                    $mobile = substr($mobile, -10);
                                }
                                
                                // Generate email if not provided or invalid
                                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', '', $userName);
                                    $email = strtolower(str_replace(' ', '.', $cleanName)) . '@example.com';
                                    $email = substr($email, 0, 50);
                                }
                                
                                // Add PI# and BPS to designation if available
                                if (isset($row[2]) && !empty(trim($row[2]))) {
                                    $piNumber = trim($row[2]);
                                    if (!empty($designation)) {
                                        $designation .= " (PI#: $piNumber)";
                                    } else {
                                        $designation = "PI#: $piNumber";
                                    }
                                }
                                
                                if (isset($row[3]) && !empty(trim($row[3]))) {
                                    $bps = trim($row[3]);
                                    if (!empty($designation)) {
                                        $designation .= " BPS-$bps";
                                    } else {
                                        $designation = "BPS-$bps";
                                    }
                                }
                                
                                // Insert into users table
                                try {
                                    $stmt = $conn->prepare("INSERT INTO users (group_id, name, designation, mobile, email) VALUES (?, ?, ?, ?, ?)");
                                    $stmt->bind_param("issss", $groupId, $userName, $designation, $mobile, $email);
                                    
                                    if ($stmt->execute()) {
                                        $successCount++;
                                        if ($successCount <= 20) {
                                            $importedUsers[] = $userName;
                                        }
                                    } else {
                                        $errorCount++;
                                    }
                                } catch (Exception $e) {
                                    $errorCount++;
                                }
                            }
                            
                            // Clear memory
                            unset($spreadsheet, $sheet, $rows);
                            
                            // REMOVED: Show progress
                            // if ($actualRow % 100 === 0) {
                            //     flush(); // THIS CAUSES HEADERS ALREADY SENT ERROR
                            // }
                        }
                        $conn->commit();
                        $conn->autocommit(TRUE);
                    } 
                    // For XLS files (smaller files)
                    else {
                        $spreadsheet = $reader->load($filePath);
                        $sheet = $spreadsheet->getActiveSheet();
                        $rows = $sheet->toArray();
                        
                        $conn->autocommit(FALSE); // Start transaction
                        
                        foreach ($rows as $index => $row) {
                            // Skip header row
                            if ($index === 0) continue;
                            
                            // Clean the data - Pakistan Government Format
                            // A = Sr# (0) - SKIP
                            // B = NAME (1)
                            // C = PI# (2) - Optional
                            // D = BPS (3) - Optional
                            // E = DESIGN (4) - Designation
                            // F = DEPTT (5) - Optional
                            // G = CNIC (6) - Optional (could be email)
                            // H = CONTACT (7) - Mobile number
                            
                            $userName = isset($row[1]) ? trim($row[1]) : '';
                            $mobile = isset($row[7]) ? trim($row[7]) : '';
                            $designation = isset($row[4]) ? trim($row[4]) : '';
                            $email = isset($row[6]) ? trim($row[6]) : '';
                            
                            // Skip empty rows
                            if (empty($userName) || empty($mobile)) {
                                $errorCount++;
                                continue;
                            }
                            
                            // Pakistan Mobile Validation
                            $mobile = preg_replace('/[^0-9]/', '', $mobile);
                            
                            if (strlen($mobile) < 10) {
                                $errorCount++;
                                continue;
                            }
                            
                            // Keep only last 10 digits if longer
                            if (strlen($mobile) > 10) {
                                $mobile = substr($mobile, -10);
                            }
                            
                            // Generate email if not provided or invalid
                            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', '', $userName);
                                $email = strtolower(str_replace(' ', '.', $cleanName)) . '@example.com';
                                $email = substr($email, 0, 50);
                            }
                            
                            // Add PI# and BPS to designation if available
                            if (isset($row[2]) && !empty(trim($row[2]))) {
                                $piNumber = trim($row[2]);
                                if (!empty($designation)) {
                                    $designation .= " (PI#: $piNumber)";
                                } else {
                                    $designation = "PI#: $piNumber";
                                }
                            }
                            
                            if (isset($row[3]) && !empty(trim($row[3]))) {
                                $bps = trim($row[3]);
                                if (!empty($designation)) {
                                    $designation .= " BPS-$bps";
                                } else {
                                    $designation = "BPS-$bps";
                                }
                            }
                            
                            // Insert into users table
                            try {
                                $stmt = $conn->prepare("INSERT INTO users (group_id, name, designation, mobile, email) VALUES (?, ?, ?, ?, ?)");
                                $stmt->bind_param("issss", $groupId, $userName, $designation, $mobile, $email);
                                
                                if ($stmt->execute()) {
                                    $successCount++;
                                    if ($successCount <= 20) {
                                        $importedUsers[] = $userName;
                                    }
                                } else {
                                    $errorCount++;
                                }
                            } catch (Exception $e) {
                                $errorCount++;
                            }
                            
                            // REMOVED: Show progress every 100 rows
                            // if ($index % 100 === 0) {
                            //     flush(); // THIS CAUSES HEADERS ALREADY SENT ERROR
                            // }
                        }
                        $conn->commit();
                        $conn->autocommit(TRUE);
                    }
                    
                } catch (Exception $e) {
                    if (isset($conn) && method_exists($conn, 'rollback')) {
                        $conn->rollback();
                        $conn->autocommit(TRUE);
                    }
                    
                    $errorMessage = $e->getMessage();
                    
                    // Provide user-friendly error messages
                    if (strpos($errorMessage, 'password') !== false || strpos($errorMessage, 'encrypted') !== false) {
                        $_SESSION['error'] = "ERROR: The Excel file is password-protected!<br><br>
                        <strong>To fix this:</strong><br>
                        1. Open the Excel file with your password<br>
                        2. Click <strong>File → Info → Protect Workbook → Encrypt with Password</strong><br>
                        3. <strong>Delete/clear</strong> the password from the box<br>
                        4. Click <strong>OK</strong><br>
                        5. <strong>Save As</strong> a new file (File → Save As → choose .csv format for faster upload)<br>
                        6. Upload the new file<br><br>
                        <em>Tip: For large files (1400+ users), save as CSV format for faster import!</em>";
                    } elseif (strpos($errorMessage, 'zip') !== false || strpos($errorMessage, 'archive') !== false) {
                        $_SESSION['error'] = "The Excel file appears to be corrupted. Please:<br>
                        1. Open the file in Excel<br>
                        2. Go to File → Save As<br>
                        3. Save as a new file (<strong>.csv format recommended for large files</strong>)<br>
                        4. Upload the new file";
                    } elseif (strpos($errorMessage, 'time') !== false) {
                        $_SESSION['error'] = "File too large to process. Please:<br>
                        1. Split your Excel file into smaller files (max 500 rows each)<br>
                        2. Or save your file as <strong>CSV format</strong><br>
                        3. Upload smaller files separately";
                    } else {
                        $_SESSION['error'] = "Error reading Excel file: " . $errorMessage;
                    }
                    ob_end_clean(); // Clear buffer before redirect
                    header("Location: " . $_SERVER['PHP_SELF'] . "?group=" . urlencode($groupName));
                    exit();
                }
            }
            
            if ($successCount > 0) {
                $message = "✅ Successfully imported $successCount users from file!";
                if (count($importedUsers) > 0) {
                    $message .= "<br><strong>Sample imported users:</strong> " . implode(', ', array_slice($importedUsers, 0, 10));
                    if (count($importedUsers) > 10) {
                        $message .= " and " . (count($importedUsers) - 10) . " more";
                    }
                }
                if ($errorCount > 0) {
                    $message .= "<br>⚠️ ($errorCount rows failed to import - check name and mobile number format)";
                }
                $_SESSION['message'] = $message;
            } else {
                $_SESSION['error'] = "No valid users found in the file or all rows failed to import. Please check:<br>
                1. File format should have columns: Sr#, NAME, PI#, BPS, DESIGN, DEPTT, CNIC, CONTACT<br>
                2. Mobile numbers must be valid Pakistan numbers (10+ digits)<br>
                3. At minimum, NAME and CONTACT columns must have data<br>
                4. File should not be password-protected";
            }
            
        } else {
            // Handle file upload errors
            $uploadError = $_FILES['excel_file']['error'] ?? 'Unknown error';
            $errorMessages = [
                0 => 'There is no error, the file uploaded with success',
                1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                3 => 'The uploaded file was only partially uploaded',
                4 => 'No file was uploaded',
                6 => 'Missing a temporary folder',
                7 => 'Failed to write file to disk.',
                8 => 'A PHP extension stopped the file upload.',
            ];
            
            $_SESSION['error'] = "File upload error: " . ($errorMessages[$uploadError] ?? 'Unknown error');
        }

        ob_end_clean(); // Clear buffer before redirect
        header("Location: " . $_SERVER['PHP_SELF'] . "?group=" . urlencode($groupName));
        exit();
    }

    
    elseif (isset($_POST['save_user'])) {
        $groupName = $_POST['group'];
        $userData = [
            'group' => $groupName,
            'name' => trim($_POST['user_name']),
            'designation' => trim($_POST['designation']),
            'mobile' => trim($_POST['mobile']),
            'email' => trim($_POST['email'])
        ];
        
        // Validation
        $errors = [];
        if (empty($userData['name'])) $errors[] = "Name is required";
        if (empty($userData['email'])) $errors[] = "Email is required";
        if (!empty($userData['email']) && !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        // Pakistan mobile validation for manual entry
        if (!empty($userData['mobile'])) {
            $mobile = preg_replace('/[^0-9]/', '', $userData['mobile']);
            if (strlen($mobile) < 10) {
                $errors[] = "Invalid mobile number (must be at least 10 digits)";
            } else {
                $userData['mobile'] = substr($mobile, -10); // Keep only last 10 digits
            }
        }
        
        if (empty($errors)) {
            // Get group ID
            $stmt = $conn->prepare("SELECT id FROM groups WHERE name = ?");
            $stmt->bind_param("s", $groupName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $group = $result->fetch_assoc();
                $groupId = $group['id'];
                
                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (group_id, name, designation, mobile, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $groupId, $userData['name'], $userData['designation'], $userData['mobile'], $userData['email']);
                
                if ($stmt->execute()) {
                    $_SESSION['users'][] = $userData;
                    $_SESSION['message'] = "User added successfully!";
                } else {
                    $_SESSION['error'] = "Error saving user: " . $conn->error;
                }
            } else {
                $_SESSION['error'] = "Group not found!";
            }
        } else {
            $_SESSION['error'] = implode("<br>", $errors);
        }
        ob_end_clean(); // Clear buffer before redirect
        header("Location: ".$_SERVER['PHP_SELF']."?group=".urlencode($groupName));
        exit();
    } 
    elseif (isset($_POST['delete_user'])) {
        $userId = $_POST['user_id'];
        $groupName = $_POST['group'];
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "User deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting user!";
        }
        ob_end_clean(); // Clear buffer before redirect
        header("Location: ".$_SERVER['PHP_SELF']."?group=".urlencode($groupName));
        exit();
    }
    
    elseif (isset($_POST['delete_group'])) {
        $groupName = $_POST['group_name'];
        
        $stmt = $conn->prepare("DELETE FROM groups WHERE name = ?");
        $stmt->bind_param("s", $groupName);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Group '$groupName' deleted successfully!";
            unset($_POST['group']);
        } else {
            $_SESSION['error'] = "Error deleting group!";
        }
        ob_end_clean(); // Clear buffer before redirect
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    elseif (isset($_POST['select_group'])) {
        $_POST['group'] = $_POST['group_name'];
    }
    elseif (isset($_POST['change_group'])) {
        unset($_POST['group']);
    }
}

$showStep1 = !isset($_POST['group']) && !isset($_GET['group']);
$showStep2 = isset($_POST['group']) || isset($_GET['group']);
$currentGroup = $_POST['group'] ?? ($_GET['group'] ?? '');

// Get all groups from database for display
$groups = [];
$result = $conn->query("SELECT name FROM groups ORDER BY name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $groups[] = $row['name'];
    }
} else {
    $groups = $_SESSION['groups'];
}

// Get users for current group if selected
$groupUsers = [];
if ($showStep2 && !empty($currentGroup)) {
    $stmt = $conn->prepare("SELECT u.id, u.name, u.designation, u.mobile, u.email 
                           FROM users u 
                           JOIN groups g ON u.group_id = g.id 
                           WHERE g.name = ? 
                           ORDER BY u.name");
    $stmt->bind_param("s", $currentGroup);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $groupUsers[] = $row;
        }
    } else {
        $groupUsers = array_filter($_SESSION['users'], function($user) use ($currentGroup) {
            return $user['group'] === $currentGroup;
        });
    }
}

// Clear the output buffer and start fresh for HTML output
ob_end_clean();
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #3a86ff;
            --primary-dark: #2667cc;
            --secondary: #8338ec;
            --dark: #1a1a2e;
            --darker: #16213e;
            --light: #f8f9fa;
            --success: #4cc9f0;
            --danger: #ff2d75;
            --warning: #ffbe0b;
            --border-radius: 10px;
            --border-radius-sm: 6px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            --box-shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--darker);
            color: var(--light);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated background elements */
        .bg-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .bg-element {
            position: absolute;
            border-radius: 50%;
            background: rgba(58, 134, 255, 0.1);
            animation: float 15s infinite linear;
        }
        
        .bg-element:nth-child(1) {
            width: 300px;
            height: 300px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }
        
        .bg-element:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            left: 15%;
            animation-delay: 2s;
        }
        
        .bg-element:nth-child(3) {
            width: 200px;
            height: 200px;
            top: 30%;
            right: 10%;
            animation-delay: 4s;
        }
        
        .bg-element:nth-child(4) {
            width: 250px;
            height: 250px;
            bottom: 10%;
            right: 15%;
            animation-delay: 6s;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
            100% { transform: translateY(0) rotate(360deg); }
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .card {
            background: rgba(26, 26, 46, 0.8);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-lg);
            border-color: rgba(58, 134, 255, 0.3);
        }
        
        .header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .header h1 {
            color: white;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            margin: 1.5rem auto;
            border-radius: 2px;
        }
        
        .form-group {
            margin-bottom: 1.75rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius-sm);
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(58, 134, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(58, 134, 255, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #e5177b);
            color: white;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #e5177b, var(--danger));
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 45, 117, 0.3);
        }
        
        .button-group {
            display: flex;
            gap: 1.25rem;
            margin-top: 2rem;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .badge i {
            margin-right: 0.5rem;
        }
        
        .badge-alpha {
            background: linear-gradient(135deg, #3a86ff, #2667cc);
        }
        
        .badge-numeric {
            background: linear-gradient(135deg, #8338ec, #6a2bc2);
        }
        
        .badge-mixed {
            background: linear-gradient(135deg, #ff2d75, #e5177b);
        }
        
        .alert {
            padding: 1.25rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-left: 4px solid transparent;
            animation: fadeIn 0.5s ease-out;
        }
        
        .alert-success {
            background: rgba(76, 201, 240, 0.1);
            border-left-color: var(--success);
            color: rgba(255, 255, 255, 0.9);
        }
        
        .alert-error {
            background: rgba(255, 45, 117, 0.1);
            border-left-color: var(--danger);
            color: rgba(255, 255, 255, 0.9);
        }
        
        .alert i {
            font-size: 1.5rem;
        }
        
        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 2rem;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius-sm);
            overflow: hidden;
        }
        
        .user-table th, .user-table td {
            padding: 1.25rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .user-table th {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }
        
        .user-table tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .user-table tr:hover {
            background: rgba(58, 134, 255, 0.05);
        }
        
        .user-table tr:last-child td {
            border-bottom: none;
        }
        
        .step {
            display: none;
            animation: fadeIn 0.6s ease-out;
        }
        
        .step.active {
            display: block;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .empty-state i {
            font-size: 3.5rem;
            color: rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .empty-state p {
            font-size: 1.1rem;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 6px 20px rgba(58, 134, 255, 0.4);
            transition: var(--transition);
            z-index: 100;
            cursor: pointer;
            animation: pulse 2s infinite;
            text-decoration: none;
        }
        
        .fab:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(58, 134, 255, 0.5);
            animation: none;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Security indicators */
        .security-indicator {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, #4cc9f0, #3a86ff, #8338ec);
            z-index: 1000;
        }
        
        .security-badge {
            position: fixed;
            bottom: 1rem;
            left: 1rem;
            background: rgba(0, 0, 0, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 100;
        }
        
        .security-badge i {
            color: #4cc9f0;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }
            
            .card {
                padding: 1.75rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .user-table th, .user-table td {
                padding: 0.9rem;
                font-size: 0.9rem;
            }
        }
        .security-badge {
      font-size: 22px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .typing-text {
      border-right: 2px solid #ffcc00;
      white-space: nowrap;
      overflow: hidden;
      display: inline-block;
      width: 0;
      animation: blink 0.8s infinite;
    }

    @keyframes blink {
      50% { border-color: transparent; }
    }
      h1 {
      font-size: 28px;
      display: flex;
      align-items: center;
      gap: 10px;
      
      
      justify-content: center;   /* horizontally center */
      align-items: center;       /* vertically center */
    
      text-align: center;    
    }

    #typing {
      border-right: 2px solid #ffcc00;
      white-space: nowrap;
      overflow: hidden;
      display: inline-block;
      animation: blink 0.8s infinite;
    }

    @keyframes blink {
      50% { border-color: transparent; }
    }
    </style>
</head>
<body>
    <!-- Security indicator bar -->
    <div class="security-indicator"></div>
    
    <!-- Animated background elements -->
    <div class="bg-elements">
        <div class="bg-element"></div>
        <div class="bg-element"></div>
        <div class="bg-element"></div>
        <div class="bg-element"></div>
    </div>
    
    <div class="container">
        <div class="header">
              <h1>
    <i class="fas fa-shield-alt"></i> 
    <span id="typing"></span>
  </h1>
            <p>Manage groups and users </p>
            <a href="launch.php" class="btn btn-secondary" style="margin-top: 1.5rem;">
                <i class="fas fa-rocket"></i> Launch Group Viewer
            </a>
        </div>
        
        <!-- Display messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Step 1: Add Group -->
        <div id="step1" class="step <?php echo $showStep1 ? 'active' : ''; ?>">
            <div class="card">
                <h2><i class="fas fa-folder-plus"></i> Create New Group</h2>
                <p class="text-muted">Securely organize your users into groups</p>
                
                <form method="post">
                    <div class="form-group">
                        <label for="group_name"><i class="fas fa-lock"></i> Group Name</label>
                        <input type="text" class="form-control" name="group_name" id="group_name" 
                               placeholder="e.g. Security Team, Admin Group" required
                               value="<?php echo $_POST['group_name'] ?? ''; ?>">
                        <small class="text-muted">All group names are encrypted in our secure database</small>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" name="add_group" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Secure Group
                        </button>
                    </div>
                </form>
                
                <?php if (!empty($groups)): ?>
                    <div style="margin-top: 2.5rem;">
                        <h3><i class="fas fa-users-shield"></i> Existing Groups</h3>
                        <p class="text-muted" style="margin-bottom: 1rem;">Select a group to manage its members</p>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.25rem;">
                            <?php foreach ($groups as $group): ?>
                                <?php 
                                $badgeClass = 'badge-mixed';
                                if (preg_match('/^[0-9]+$/', $group)) {
                                    $badgeClass = 'badge-numeric';
                                } elseif (preg_match('/^[a-zA-Z ]+$/', $group)) {
                                    $badgeClass = 'badge-alpha';
                                }
                                ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="group_name" value="<?php echo htmlspecialchars($group); ?>">
                                    <button type="submit" name="select_group" class="badge <?php echo $badgeClass; ?>">
                                        <i class="fas fa-users"></i> <?php echo htmlspecialchars($group); ?>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Step 2: Add Users -->
        <div id="step2" class="step <?php echo $showStep2 ? 'active' : ''; ?>">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2><i class="fas fa-user-plus"></i> Manage Users in 
                        <?php 
                        $badgeClass = 'badge-mixed';
                        if (preg_match('/^[0-9]+$/', $currentGroup)) {
                            $badgeClass = 'badge-numeric';
                        } elseif (preg_match('/^[a-zA-Z ]+$/', $currentGroup)) {
                            $badgeClass = 'badge-alpha';
                        }
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <i class="fas fa-users"></i> <?php echo htmlspecialchars($currentGroup); ?>
                        </span>
                    </h2>
                    <div style="display: flex; gap: 1rem;">
                        <form method="post" style="display: inline;">
                            <button type="submit" name="change_group" class="btn btn-secondary">
                                <i class="fas fa-exchange-alt"></i> Switch Group
                            </button>
                        </form>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="group_name" value="<?php echo htmlspecialchars($currentGroup); ?>">
                            <button type="submit" name="delete_group" class="btn btn-danger" 
                                    onclick="return confirm('WARNING: This will permanently delete the group and all its users. Continue?')">
                                <i class="fas fa-trash"></i> Delete Group
                            </button>
                        </form>
                    </div>
                </div>
                
                <form method="post">
                    <input type="hidden" name="group" value="<?php echo htmlspecialchars($currentGroup); ?>">
                    
                    <div class="form-group">
                        <label for="user_name"><i class="fas fa-user-shield"></i> Full Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" name="user_name" id="user_name" 
                               value="<?php echo $_POST['user_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="designation"><i class="fas fa-id-card"></i> Designation</label>
                        <input type="text" class="form-control" name="designation" id="designation" 
                               value="<?php echo $_POST['designation'] ?? ''; ?>" placeholder="Optional">
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile"><i class="fas fa-mobile-alt"></i> Mobile Number</label>
                        <input type="tel" class="form-control" name="mobile" id="mobile" 
                               value="<?php echo $_POST['mobile'] ?? ''; ?>" placeholder="03331234567">
                        <small class="text-muted">Pakistan mobile numbers only (10+ digits)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email <span style="color: var(--danger);">*</span></label>
                        <input type="email" class="form-control" name="email" id="email" 
                               value="<?php echo $_POST['email'] ?? ''; ?>" required placeholder="user@example.com">
                        <small class="text-muted">Used for secure communications</small>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" name="save_user" class="btn btn-primary">
                            <i class="fas fa-user-lock"></i> Add Secure User
                        </button>
                    </div>
                </form>
                
                <!-- Excel Upload -->
                <form method="post" enctype="multipart/form-data" style="margin-top:2rem;">
                    <input type="hidden" name="group" value="<?php echo htmlspecialchars($currentGroup); ?>">
                    <div class="form-group">
                        <label for="excel_file"><i class="fas fa-file-excel"></i> Upload Excel/CSV File (Pakistan Government Format)</label>
                        <input type="file" class="form-control" name="excel_file" id="excel_file" 
                               accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">
                            <strong>For Pakistan government data format:</strong><br>
                            1. Required columns: NAME (B), CONTACT (H)<br>
                            2. Optional: PI# (C), BPS (D), DESIGN (E), DEPTT (F), CNIC (G)<br>
                            3. Mobile numbers will be auto-cleaned (03XXXXXXXXX)<br>
                            4. Emails auto-generated from names if not provided<br>
                            5. <strong>Save as CSV format</strong> for fastest import<br>
                            6. Remove password protection if any<br>
                        </small>
                    </div>
                    <div class="button-group">
                        <button type="submit" name="upload_excel" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Import Users
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('download-template').click()">
                            <i class="fas fa-download"></i> Download Template
                        </button>
                        <a href="template.csv" id="download-template" style="display:none;" download>Template</a>
                    </div>
                </form>
                
                <!-- Display users in this group -->
                <div class="user-list" style="margin-top: 3rem;">
                    <h3><i class="fas fa-users-cog"></i> Group Members (<?php echo count($groupUsers); ?> users)</h3>
                    <p class="text-muted" style="margin-bottom: 1.5rem;">Manage existing users in this group</p>
                    
                    <?php if (!empty($groupUsers)): ?>
                        <div style="overflow-x: auto;">
                            <table class="user-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Name</th>
                                        <th><i class="fas fa-id-badge"></i> Designation</th>
                                        <th><i class="fas fa-phone"></i> Mobile</th>
                                        <th><i class="fas fa-at"></i> Email</th>
                                        <th><i class="fas fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($groupUsers as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['designation']); ?></td>
                                            <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="group" value="<?php echo htmlspecialchars($currentGroup); ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('WARNING: This will permanently delete the user. Continue?')">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>No users found in this secure group.</p>
                            <p>Add users using the secure form above.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <a href="admin.php" class="fab" title="Back to Secure Dashboard">
    <i class="fa fa-house-user"></i>
    </a>
    
    <script>
        const texts = [
            "Think Before You Click",
            "Strong Passwords = Strong Security",
            "Your Data, Your Responsibility",
            "Stay Alert. Stop Cyber Threats.",
            "Security Starts With You",
        ];

        let index = 0;
        let charIndex = 0;
        let isDeleting = false;
        const typingElement = document.getElementById("typing");
        const typingSpeed = 100;
        const erasingSpeed = 60;
        const delayBetween = 1500;

        function typeEffect() {
            const currentText = texts[index];

            if (!isDeleting && charIndex <= currentText.length) {
                typingElement.textContent = currentText.substring(0, charIndex++);
                setTimeout(typeEffect, typingSpeed);
            } else if (isDeleting && charIndex >= 0) {
                typingElement.textContent = currentText.substring(0, charIndex--);
                setTimeout(typeEffect, erasingSpeed);
            } else {
                if (!isDeleting) {
                    isDeleting = true;
                    setTimeout(typeEffect, delayBetween);
                } else {
                    isDeleting = false;
                    index = (index + 1) % texts.length;
                    setTimeout(typeEffect, typingSpeed);
                }
            }
        }

        typeEffect();
        
        // Add floating animation to cards when they come into view
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                observer.observe(card);
            });
        });
        
        // File upload size warning
        document.getElementById('excel_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024; // in MB
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                
                if (fileSize > 10) {
                    alert('⚠️ File size exceeds 10MB. For large files, save as CSV format.');
                }
                
                if (fileExt === 'xlsx' && fileSize > 2) {
                    alert('⚠️ Large Excel file detected. For faster import, save as CSV format.');
                }
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>