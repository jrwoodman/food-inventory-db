<?php
// Direct login test
ob_start();
require_once '../config/config.php';
session_start();

require_once '../src/database/Database.php';
require_once '../src/models/User.php';
require_once '../src/auth/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

echo "=== Direct Login Test ===\n\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "Attempting login with:\n";
    echo "Username: $username\n";
    echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";
    
    $result = $auth->login($username, $password);
    
    echo "Login result:\n";
    print_r($result);
    echo "\n";
    
    if ($result['success']) {
        echo "✓ Login successful!\n\n";
        echo "Session data after login:\n";
        print_r($_SESSION);
        echo "\n";
        
        echo "Testing isLoggedIn()...\n";
        $logged_in = $auth->isLoggedIn();
        echo "isLoggedIn() returns: " . ($logged_in ? 'TRUE' : 'FALSE') . "\n\n";
        
        if ($logged_in) {
            echo "✓ User is logged in!\n";
            echo '<a href="debug_session.php">Check session debug page</a><br>';
            echo '<a href="index.php?action=dashboard">Go to dashboard</a>';
        } else {
            echo "✗ isLoggedIn() failed even though login succeeded!\n";
            
            // Check database
            if (isset($_SESSION['session_id'])) {
                $query = "SELECT * FROM user_sessions WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $_SESSION['session_id']);
                $stmt->execute();
                $db_session = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "\nDatabase session:\n";
                print_r($db_session);
            }
        }
    } else {
        echo "✗ Login failed: " . $result['message'] . "\n";
    }
} else {
    echo "Current session state:\n";
    print_r($_SESSION);
    echo "\n\n";
    
    echo '<form method="POST">
        Username: <input type="text" name="username" value="admin"><br><br>
        Password: <input type="password" name="password" value="admin123"><br><br>
        <button type="submit">Test Login</button>
    </form>';
}
?>
