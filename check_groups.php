<?php
/**
 * Groups Diagnostic Script
 * Check if groups tables exist and are properly configured
 */

require_once 'config/config.php';
require_once 'src/database/Database.php';
require_once 'src/models/Group.php';
require_once 'src/models/User.php';

echo "<h1>Groups System Diagnostics</h1>";
echo "<pre>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "✓ Database connection successful\n\n";
    
    // Check if tables exist
    echo "=== Checking Tables ===\n";
    $tables = ['groups', 'user_groups', 'users', 'foods', 'ingredients'];
    
    foreach ($tables as $table) {
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
        if ($stmt->fetch()) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' NOT FOUND\n";
        }
    }
    
    echo "\n=== Checking Groups ===\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM groups");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Groups in database: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        $stmt = $db->query("SELECT id, name, description FROM groups");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - Group #{$row['id']}: {$row['name']}\n";
        }
    }
    
    echo "\n=== Checking User-Group Associations ===\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM user_groups");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "User-group associations: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        $stmt = $db->query("
            SELECT ug.user_id, u.username, ug.group_id, g.name as group_name, ug.role
            FROM user_groups ug
            JOIN users u ON ug.user_id = u.id
            JOIN groups g ON ug.group_id = g.id
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - User '{$row['username']}' (ID: {$row['user_id']}) is {$row['role']} of '{$row['group_name']}' (ID: {$row['group_id']})\n";
        }
    }
    
    echo "\n=== Testing Group Model ===\n";
    $group = new Group($db);
    $stmt = $group->read();
    echo "Group->read() returned: " . ($stmt ? "PDOStatement" : "false/null") . "\n";
    
    if ($stmt) {
        $count = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $count++;
        }
        echo "Found $count groups via model\n";
    }
    
    echo "\n=== Testing User Model ===\n";
    $user = new User($db);
    $user->id = 1; // Admin user
    if ($user->readOne()) {
        echo "✓ User found: {$user->username}\n";
        
        $stmt = $user->getGroups();
        if ($stmt) {
            echo "✓ getGroups() returned PDOStatement\n";
            $count = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $count++;
                echo "  - Group: {$row['name']} (Role: {$row['role']})\n";
            }
            echo "User belongs to $count group(s)\n";
        } else {
            echo "✗ getGroups() returned false/null\n";
        }
        
        $group_ids = $user->getGroupIds();
        echo "getGroupIds() returned: " . implode(', ', $group_ids) . "\n";
    } else {
        echo "✗ Could not load user ID 1\n";
    }
    
    echo "\n=== Diagnosis Complete ===\n";
    
} catch (Exception $e) {
    echo "\n✗✗✗ ERROR ✗✗✗\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>
