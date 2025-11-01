<?php
// Food & Ingredient Inventory Database Configuration
// This file contains all configuration settings for the application

// Load environment-specific settings FIRST so they can override defaults
if (file_exists(__DIR__ . '/local.php')) {
    require_once __DIR__ . '/local.php';
}

// Database Configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'food_inventory_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Application Configuration
define('APP_NAME', 'Food & Ingredient Inventory');
define('APP_TITLE', '🍽️ Food Inventory'); // Title shown in navigation/header
define('APP_SUBTITLE', ''); // Optional subtitle shown below title (leave empty to hide)
define('APP_ICON', ''); // Optional 64x64 icon path (e.g., '../assets/images/logo.png')
define('APP_FAVICON', ''); // Optional favicon path (e.g., '../assets/images/favicon.ico')
define('APP_VERSION', '1.0.0-rc2');
define('APP_DESCRIPTION', 'A comprehensive food and ingredient inventory management system');

// Timezone Configuration
date_default_timezone_set('America/New_York');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('CSRF_TOKEN_EXPIRE', 1800); // 30 minutes in seconds

// Application Settings
define('DEFAULT_ITEMS_PER_PAGE', 50);
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Alert Thresholds
if (!defined('EXPIRY_WARNING_DAYS')) define('EXPIRY_WARNING_DAYS', 7);      // Show expiry warning for items expiring within 7 days
if (!defined('LOW_STOCK_THRESHOLD')) define('LOW_STOCK_THRESHOLD', 10);      // Consider ingredients low stock when quantity <= 10
if (!defined('CRITICAL_STOCK_THRESHOLD')) define('CRITICAL_STOCK_THRESHOLD', 5);  // Critical stock level

// Email Configuration (for future notifications)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', '');
define('FROM_NAME', APP_NAME);

// Backup Configuration
define('BACKUP_DIR', 'backups/');
define('BACKUP_RETENTION_DAYS', 30);

// API Configuration
define('API_VERSION', 'v1');
define('API_BASE_URL', '/api/' . API_VERSION . '/');

// Categories Configuration
$food_categories = [
    'Fruits', 'Vegetables', 'Meat', 'Dairy', 'Grains', 
    'Beverages', 'Snacks', 'Frozen', 'Canned', 'Other'
];

$ingredient_categories = [
    'Spices', 'Herbs', 'Oils', 'Vinegars', 'Flour', 
    'Sugar', 'Salt', 'Baking', 'Sauces', 'Condiments', 'Other'
];

$storage_locations = [
    'Refrigerator', 'Freezer', 'Pantry', 'Counter', 'Cupboard', 
    'Basement', 'Spice Rack', 'Other'
];

$units = [
    // Weight units
    'g' => 'Grams',
    'kg' => 'Kilograms', 
    'oz' => 'Ounces',
    'lbs' => 'Pounds',
    
    // Volume units
    'ml' => 'Milliliters',
    'liters' => 'Liters',
    'cups' => 'Cups',
    'tbsp' => 'Tablespoons',
    'tsp' => 'Teaspoons',
    
    // Count units
    'pieces' => 'Pieces',
    'cans' => 'Cans',
    'boxes' => 'Boxes',
    'bottles' => 'Bottles',
    'jars' => 'Jars',
    'packages' => 'Packages'
];

// Helper Functions
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

function isProduction() {
    return getConfig('ENVIRONMENT', 'development') === 'production';
}

function getUploadPath() {
    return __DIR__ . '/../uploads/';
}

function getBackupPath() {
    return __DIR__ . '/../' . BACKUP_DIR;
}


// Auto-create necessary directories
$directories = [
    getUploadPath(),
    getBackupPath(),
    __DIR__ . '/../logs/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
?>