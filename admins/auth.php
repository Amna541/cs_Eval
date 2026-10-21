<?php
require_once __DIR__ . '/database.php';

class Auth {
    private $conn;
    private $sessionTimeout = 1800; // 30 minutes

    public function __construct($conn) {
        $this->conn = $conn;
        session_start();
        $this->checkSession();
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("
            SELECT u.*, us.api_key 
            FROM users u
            LEFT JOIN user_settings us ON u.id = us.user_id
            WHERE u.username = ? AND u.is_active = TRUE
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['api_key'] = $user['api_key'];
            $_SESSION['last_activity'] = time();

            // Record session in database
            $this->recordSession($user['id']);

            // Log login
            $this->conn->logAction($user['id'], 'login', 'User logged in');

            return true;
        }

        return false;
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->conn->logAction($_SESSION['user_id'], 'logout', 'User logged out');
            
            // Remove session from database
            $this->revokeSession(session_id());
        }

        $_SESSION = array();
        session_destroy();
    }

    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return $this->isAuthenticated() && $_SESSION['role'] === 'admin';
    }

    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            header('Location: login.php');
            exit();
        }
    }

    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied');
        }
    }

    private function checkSession() {
        if ($this->isAuthenticated()) {
            // Check session timeout
            if (time() - $_SESSION['last_activity'] > $this->sessionTimeout) {
                $this->logout();
                header('Location: login.php?timeout=1');
                exit();
            }
            
            $_SESSION['last_activity'] = time();
            
            // Verify session exists in database
            if (!$this->verifySession(session_id())) {
                $this->logout();
                header('Location: login.php?invalid=1');
                exit();
            }
        }
    }

    private function recordSession($userId) {
        $stmt = $this->conn->prepare("
            INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE last_activity = CURRENT_TIMESTAMP
        ");
        $stmt->execute([
            $userId,
            session_id(),
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    }

    private function verifySession($sessionId) {
        $stmt = $this->conn->prepare("
            SELECT 1 FROM user_sessions 
            WHERE session_id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$sessionId, $_SESSION['user_id']]);
        return $stmt->rowCount() > 0;
    }

    public function revokeSession($sessionId) {
        $stmt = $this->conn->prepare("
            DELETE FROM user_sessions WHERE session_id = ?
        ");
        return $stmt->execute([$sessionId]);
    }

    public function getCurrentUser() {
        if (!$this->isAuthenticated()) return null;
        
        $stmt = $this->conn->prepare("
            SELECT u.*, us.* 
            FROM users u
            JOIN user_settings us ON u.id = us.user_id
            WHERE u.id = ?
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Initialize authentication
$auth = new Auth($conn);
?>