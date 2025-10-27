<?php
// Debug session and login state
ob_start();
require_once 'config/config.php';
session_start();

require_once 'src/database/Database.php';
require_once 'src/models/User.php';
require_once 'src/auth/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

echo "=== Session Debug Info ===\n\n";

echo "PHP Session Data:\n";
print_r($_SESSION);
echo "\n";

echo "Session ID: " . session_id() . "\n\n";

if (isset($_SESSION['user_id']) && isset($_SESSION['session_id'])) {
    echo "Session variables exist:\n";
    echo "- user_id: " . $_SESSION['user_id'] . "\n";
    echo "- session_id: " . $_SESSION['session_id'] . "\n";
    echo "- username: " . ($_SESSION['username'] ?? 'not set') . "\n";
    echo "- role: " . ($_SESSION['role'] ?? 'not set') . "\n\n";
    
    echo "Checking database session...\n";
    $query = "SELECT id, user_id, expires_at, datetime('now') as now, 
              CASE WHEN expires_at > datetime('now') THEN 'VALID' ELSE 'EXPIRED' END as status
              FROM user_sessions 
              WHERE id = ? AND user_id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $_SESSION['session_id']);
    $stmt->bindParam(2, $_SESSION['user_id']);
    $stmt->execute();
    
    $db_session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($db_session) {
        echo "✓ Session found in database:\n";
        print_r($db_session);
        echo "\n";
    } else {
        echo "✗ Session NOT found in database\n\n";
        
        echo "All sessions in database:\n";
        $all_query = "SELECT id, user_id, expires_at, datetime('now') as now FROM user_sessions";
        $all_stmt = $db->prepare($all_query);
        $all_stmt->execute();
        $all_sessions = $all_stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($all_sessions);
        echo "\n";
    }
    
    echo "Auth->isLoggedIn() returns: " . ($auth->isLoggedIn() ? 'TRUE' : 'FALSE') . "\n\n";
    
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        if ($user) {
            echo "✓ Current user:\n";
            echo "- ID: " . $user->id . "\n";
            echo "- Username: " . $user->username . "\n";
            echo "- Email: " . $user->email . "\n";
            echo "- Role: " . $user->role . "\n";
        } else {
            echo "✗ getCurrentUser() returned null\n";
        }
    } else {
        echo "✗ User is NOT logged in according to Auth->isLoggedIn()\n";
    }
    
} else {
    echo "✗ Session variables NOT set\n";
    echo "- user_id exists: " . (isset($_SESSION['user_id']) ? 'YES' : 'NO') . "\n";
    echo "- session_id exists: " . (isset($_SESSION['session_id']) ? 'YES' : 'NO') . "\n";
}

echo "\n=== Action ===\n";
echo "Visit the login page and log in, then come back to this page to see the session state.\n";
?>
