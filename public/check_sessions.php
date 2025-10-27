<?php
// Session diagnostic script
echo "=== PHP Session Configuration ===\n\n";

// Session settings
echo "Session save path: " . session_save_path() . "\n";
echo "Session save path writable: " . (is_writable(session_save_path()) ? 'YES' : 'NO') . "\n";
echo "Session name: " . session_name() . "\n";
echo "Session cookie lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "Session cookie path: " . ini_get('session.cookie_path') . "\n";
echo "Session cookie domain: " . ini_get('session.cookie_domain') . "\n";
echo "Session cookie secure: " . ini_get('session.cookie_secure') . "\n";
echo "Session cookie httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "Session cookie samesite: " . ini_get('session.cookie_samesite') . "\n";
echo "Session use cookies: " . ini_get('session.use_cookies') . "\n";
echo "Session use only cookies: " . ini_get('session.use_only_cookies') . "\n\n";

// Test session functionality
echo "=== Session Functionality Test ===\n\n";

session_start();

if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 0;
}
$_SESSION['test_counter']++;

echo "Session ID: " . session_id() . "\n";
echo "Session test counter: " . $_SESSION['test_counter'] . "\n";
echo "Session data: " . print_r($_SESSION, true) . "\n";

// Check if session file exists
$session_file = session_save_path() . '/sess_' . session_id();
echo "Session file expected: $session_file\n";
echo "Session file exists: " . (file_exists($session_file) ? 'YES' : 'NO') . "\n\n";

// Database check
echo "=== Database Configuration ===\n\n";
require_once '../config/config.php';
require_once '../src/database/Database.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "Database connection: OK\n";
    
    // Check user_sessions table
    try {
        $query = "SELECT COUNT(*) as count FROM user_sessions";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Sessions in database: " . $row['count'] . "\n";
    } catch (Exception $e) {
        echo "Error querying sessions: " . $e->getMessage() . "\n";
    }
} else {
    echo "Database connection: FAILED\n";
}

echo "\n=== Recommendations ===\n\n";

if (!is_writable(session_save_path())) {
    echo "⚠ Session save path is not writable!\n";
    echo "  Run: sudo chmod 777 " . session_save_path() . "\n";
    echo "  Or: sudo chown -R www-data:www-data " . session_save_path() . "\n\n";
}

if (ini_get('session.cookie_secure') == 1 && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    echo "⚠ Session cookie set to secure but not using HTTPS!\n";
    echo "  This will prevent sessions from working.\n\n";
}

echo "✓ Reload this page to see if session counter increases.\n";
echo "✓ If counter stays at 1, sessions are not persisting.\n";
?>
