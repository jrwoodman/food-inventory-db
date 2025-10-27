<?php
class Auth {
    private $conn;
    private $user;
    private $session_lifetime = 3600; // 1 hour
    
    public function __construct($db) {
        $this->conn = $db;
        if (!isset($_SESSION)) {
            session_start();
        }
    }
    
    public function login($username, $password) {
        $user = new User($this->conn);
        
        // Find user by username or email
        if (!$user->findByUsername($username) && !$user->findByEmail($username)) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Verify password
        if (!$user->verifyPassword($password)) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Check if user is active
        if (!$user->is_active) {
            return ['success' => false, 'message' => 'Account is deactivated'];
        }
        
        // Update last login
        $user->updateLastLogin();
        
        // Create session
        $this->createSession($user);
        
        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
    }
    
    public function logout() {
        if (isset($_SESSION['session_id'])) {
            $this->destroySession($_SESSION['session_id']);
        }
        
        // Clear session data
        session_unset();
        session_destroy();
        
        // Start new session
        session_start();
        session_regenerate_id(true);
        
        return true;
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
            return false;
        }
        
        // Verify session in database
        return $this->verifySession($_SESSION['session_id'], $_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $user = new User($this->conn);
        $user->id = $_SESSION['user_id'];
        
        if ($user->readOne()) {
            return $user;
        }
        
        return null;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: index.php?action=login&redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
    }
    
    public function requireRole($required_role) {
        $this->requireLogin();
        
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->logout();
            header('Location: index.php?action=login');
            exit();
        }
        
        $role_hierarchy = ['viewer' => 1, 'user' => 2, 'admin' => 3];
        $user_level = $role_hierarchy[$user->role] ?? 0;
        $required_level = $role_hierarchy[$required_role] ?? 999;
        
        if ($user_level < $required_level) {
            header('Location: index.php?action=access_denied');
            exit();
        }
    }
    
    public function requireAdmin() {
        $this->requireRole('admin');
    }
    
    private function createSession($user) {
        // Generate session ID
        $session_id = $this->generateSessionId();
        
        // Store in database
        $query = "INSERT INTO user_sessions (id, user_id, expires_at, ip_address, user_agent, created_at)
                 VALUES (:id, :user_id, :expires_at, :ip_address, :user_agent, datetime('now'))";
        
        $stmt = $this->conn->prepare($query);
        $expires_at = date('Y-m-d H:i:s', time() + $this->session_lifetime);
        
        $stmt->bindParam(':id', $session_id);
        $stmt->bindParam(':user_id', $user->id);
        $stmt->bindParam(':expires_at', $expires_at);
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);
        
        if ($stmt->execute()) {
            // Set session variables
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['role'] = $user->role;
            $_SESSION['session_id'] = $session_id;
            $_SESSION['login_time'] = time();
            
            return true;
        }
        
        return false;
    }
    
    private function verifySession($session_id, $user_id) {
        $query = "SELECT id FROM user_sessions 
                 WHERE id = ? AND user_id = ? AND expires_at > datetime('now')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $session_id);
        $stmt->bindParam(2, $user_id);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            // Extend session if it's still valid
            $this->extendSession($session_id);
            return true;
        }
        
        return false;
    }
    
    private function extendSession($session_id) {
        $query = "UPDATE user_sessions 
                 SET expires_at = datetime('now', '+{$this->session_lifetime} seconds')
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $session_id);
        $stmt->execute();
    }
    
    private function destroySession($session_id) {
        $query = "DELETE FROM user_sessions WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $session_id);
        $stmt->execute();
    }
    
    private function generateSessionId() {
        return bin2hex(random_bytes(32));
    }
    
    public function cleanupExpiredSessions() {
        $query = "DELETE FROM user_sessions WHERE expires_at < datetime('now')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }
    
    public function getUserSessions($user_id) {
        $query = "SELECT id, ip_address, user_agent, created_at, expires_at
                 FROM user_sessions 
                 WHERE user_id = ? AND expires_at > datetime('now')
                 ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    public function revokeSession($session_id, $user_id) {
        $query = "DELETE FROM user_sessions WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $session_id);
        $stmt->bindParam(2, $user_id);
        return $stmt->execute();
    }
    
    public function revokeAllSessions($user_id, $except_current = true) {
        if ($except_current && isset($_SESSION['session_id'])) {
            $query = "DELETE FROM user_sessions WHERE user_id = ? AND id != ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $_SESSION['session_id']);
        } else {
            $query = "DELETE FROM user_sessions WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
        }
        
        return $stmt->execute();
    }
    
    public function generatePasswordResetToken($user_id) {
        // This would be implemented for password reset functionality
        // For now, return a simple token
        return bin2hex(random_bytes(16));
    }
    
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>