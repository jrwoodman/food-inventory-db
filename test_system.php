<?php
/**
 * Food Inventory System Test Script
 * Run this to verify the system is working correctly
 */

echo "🧪 Food Inventory System Test\n";
echo "============================\n\n";

// Test 1: Check if required files exist
echo "1. Checking file structure...\n";
$required_files = [
    'src/database/Database.php',
    'src/models/User.php',
    'src/models/Food.php',
    'src/models/Ingredient.php',
    'src/auth/Auth.php',
    'src/controllers/UserController.php',
    'src/controllers/InventoryController.php',
    'src/views/auth/login.php',
    'src/views/users/index.php',
    'src/views/dashboard.php',
    'assets/css/style.css',
    'assets/js/app.js',
    'config/config.php',
    'database/food_inventory.db'
];

$missing_files = [];
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file\n";
    } else {
        echo "   ❌ $file (MISSING)\n";
        $missing_files[] = $file;
    }
}

if (!empty($missing_files)) {
    echo "\n❌ Missing files detected! Please ensure all files are in place.\n";
    exit(1);
}

echo "\n2. Testing database connection...\n";

try {
    require_once 'src/database/Database.php';
    require_once 'config/config.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "   ✅ Database connection successful\n";
        
        // Test database tables
        echo "\n3. Checking database tables...\n";
        $tables = [
            'users', 'user_sessions', 'foods', 'ingredients', 
            'ingredient_locations', 'categories', 'recipes', 'food_ingredients'
        ];
        
        foreach ($tables as $table) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM $table");
                $stmt->execute();
                $count = $stmt->fetchColumn();
                echo "   ✅ Table '$table' exists (rows: $count)\n";
            } catch (Exception $e) {
                echo "   ❌ Table '$table' missing or error: " . $e->getMessage() . "\n";
            }
        }
        
        // Test views
        echo "\n4. Checking database views...\n";
        $views = [
            'expiring_foods', 'ingredient_totals', 'low_stock_ingredients', 
            'ingredient_location_details', 'inventory_summary'
        ];
        
        foreach ($views as $view) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM $view");
                $stmt->execute();
                $count = $stmt->fetchColumn();
                echo "   ✅ View '$view' exists (rows: $count)\n";
            } catch (Exception $e) {
                echo "   ❌ View '$view' missing or error: " . $e->getMessage() . "\n";
            }
        }
        
    } else {
        echo "   ❌ Database connection failed\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n5. Testing user authentication...\n";

try {
    require_once 'src/models/User.php';
    require_once 'src/auth/Auth.php';
    
    $user = new User($db);
    $auth = new Auth($db);
    
    // Check if admin user exists
    if ($user->findByUsername('admin')) {
        echo "   ✅ Default admin user exists\n";
        
        // Test password verification
        if ($user->verifyPassword('admin123')) {
            echo "   ✅ Admin password verification works\n";
        } else {
            echo "   ❌ Admin password verification failed\n";
        }
        
        echo "   📝 Admin user details:\n";
        echo "      Username: " . $user->username . "\n";
        echo "      Email: " . $user->email . "\n";
        echo "      Role: " . $user->role . "\n";
        echo "      Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
        
    } else {
        echo "   ❌ Default admin user not found\n";
    }
    
    $user_count = $user->getUsersCount();
    echo "   📊 Total users in system: $user_count\n";
    
} catch (Exception $e) {
    echo "   ❌ Auth system error: " . $e->getMessage() . "\n";
}

echo "\n6. Testing models...\n";

try {
    require_once 'src/models/Food.php';
    require_once 'src/models/Ingredient.php';
    
    $food = new Food($db);
    $ingredient = new Ingredient($db);
    
    echo "   ✅ Food model loaded\n";
    echo "   ✅ Ingredient model loaded\n";
    
    // Test reading data
    $foods = $food->read();
    $ingredients = $ingredient->read();
    
    echo "   📊 Foods in database: " . $foods->rowCount() . "\n";
    echo "   📊 Ingredients in database: " . $ingredients->rowCount() . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Model error: " . $e->getMessage() . "\n";
}

echo "\n7. Checking web server requirements...\n";

// Check PHP version
$php_version = phpversion();
echo "   📋 PHP Version: $php_version\n";

if (version_compare($php_version, '7.4.0', '>=')) {
    echo "   ✅ PHP version is compatible (7.4+)\n";
} else {
    echo "   ❌ PHP version too old (requires 7.4+)\n";
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_sqlite', 'json', 'session'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ Extension '$ext' loaded\n";
    } else {
        echo "   ❌ Extension '$ext' missing\n";
    }
}

// Check file permissions
echo "\n8. Checking file permissions...\n";
$check_dirs = ['database', 'uploads', 'backups', 'logs'];

foreach ($check_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "   📁 Created directory: $dir\n";
    }
    
    if (is_writable($dir)) {
        echo "   ✅ Directory '$dir' is writable\n";
    } else {
        echo "   ❌ Directory '$dir' is not writable\n";
    }
}

if (file_exists('database/food_inventory.db')) {
    if (is_writable('database/food_inventory.db')) {
        echo "   ✅ Database file is writable\n";
    } else {
        echo "   ❌ Database file is not writable\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 TESTING COMPLETE!\n\n";

echo "📋 Next Steps:\n";
echo "1. Start the development server:\n";
echo "   php -S localhost:8000 -t public/\n\n";
echo "2. Open in browser:\n";
echo "   http://localhost:8000\n\n";
echo "3. Login with default credentials:\n";
echo "   Username: admin\n";
echo "   Password: admin123\n\n";
echo "⚠️  IMPORTANT: Change the default password after first login!\n\n";

echo "🔍 What to test in the browser:\n";
echo "• Login/logout functionality\n";
echo "• User management (admin only)\n";
echo "• Adding/editing foods and ingredients\n";
echo "• Theme toggle (light/dark mode)\n";
echo "• Multi-location ingredient storage\n";
echo "• Profile management\n";
echo "• Session management\n";
echo "• Role-based access control\n\n";

echo "🎉 System appears to be ready for testing!\n";
?>