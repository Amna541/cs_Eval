<?php
require_once __DIR__ . '/../config/auth.php';
$auth->requireAdmin();

header('Content-Type: application/json');

try {
    $currentUser = $auth->getCurrentUser();
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    if (isset($data['timezone'])) {
        // Handle general settings
        $stmt = $conn->prepare("
            UPDATE user_settings 
            SET timezone = ?, date_format = ?, records_per_page = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $data['timezone'],
            $data['date_format'],
            $data['records_per_page'],
            $currentUser['user_id']
        ]);
        
        $conn->logAction($currentUser['user_id'], 'settings_update', 'Updated general settings');
    }
    elseif (isset($data['email_notifications'])) {
        // Handle notification settings
        $stmt = $conn->prepare("
            UPDATE user_settings 
            SET email_notifications = ?, browser_notifications = ?, two_factor_auth = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $data['email_notifications'] ? 1 : 0,
            $data['browser_notifications'] ? 1 : 0,
            $data['two_factor_auth'] ? 1 : 0,
            $currentUser['user_id']
        ]);
        
        $conn->logAction($currentUser['user_id'], 'settings_update', 'Updated notification settings');
    }
    elseif (isset($data['current_password'])) {
        // Handle password change
        if ($data['new_password'] !== $data['confirm_password']) {
            throw new Exception('Passwords do not match');
        }

        // Verify current password
        $userStmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $userStmt->execute([$currentUser['user_id']]);
        $user = $userStmt->fetch();

        if (!password_verify($data['current_password'], $user['password'])) {
            throw new Exception('Current password is incorrect');
        }

        // Update password
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([
            password_hash($data['new_password'], PASSWORD_DEFAULT),
            $currentUser['user_id']
        ]);
        
        $conn->logAction($currentUser['user_id'], 'password_change', 'Password updated');
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    
    if (isset($currentUser)) {
        $conn->logAction(
            $currentUser['user_id'], 
            'settings_error', 
            'Failed to update settings: ' . $e->getMessage()
        );
    }
}
?>