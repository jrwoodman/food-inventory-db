<?php
// Reset admin password script
require_once 'config/config.php';
require_once 'src/database/Database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed\n");
}

echo "=== Reset Admin Password ===\n\n";

// Check if admin user exists
$query = "SELECT id, username, email FROM users WHERE username = 'admin'";
$stmt = $db->prepare($query);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$new_password = 'admin123';
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

if ($admin) {
    echo "Admin user found (ID: {$admin['id']})\n";
    echo "Email: {$admin['email']}\n";
    echo "Updating password...\n\n";
    
    $update_query = "UPDATE users SET password_hash = ? WHERE username = 'admin'";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(1, $password_hash);
    
    if ($update_stmt->execute()) {
        echo "✓ Password reset successfully!\n\n";
    } else {
        echo "✗ Failed to update password\n\n";
    }
} else {
    echo "Admin user not found. Creating new admin user...\n\n";
    
    $insert_query = "INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active, created_at, updated_at) 
                     VALUES ('admin', 'admin@foodinventory.local', ?, 'System', 'Administrator', 'admin', 1, datetime('now'), datetime('now'))";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(1, $password_hash);
    
    if ($insert_stmt->execute()) {
        echo "✓ Admin user created successfully!\n\n";
    } else {
        echo "✗ Failed to create admin user\n";
        print_r($insert_stmt->errorInfo());
    }
}

echo "Login credentials:\n";
echo "Username: admin\n";
echo "Password: $new_password\n\n";

echo "New password hash: $password_hash\n\n";

echo "⚠ IMPORTANT: Delete this script after use for security!\n";
echo "   Run: rm reset_admin_password.php\n";
?>
