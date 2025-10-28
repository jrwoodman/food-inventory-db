<?php
/**
 * Database Initialization Script
 * 
 * This script recreates the database from the schema file.
 * Run this after pulling new schema changes.
 * 
 * Usage:
 *   php init_database.php
 * 
 * Or access via browser:
 *   http://your-domain.com/init_database.php
 */

// Load configuration
require_once 'config/config.php';
require_once 'src/database/Database.php';

echo "=== Food Inventory Database Initialization ===\n\n";

// Backup existing database if it exists
$db_file = __DIR__ . '/database/food_inventory.db';
if (file_exists($db_file)) {
    $backup_file = __DIR__ . '/database/food_inventory_backup_' . date('Y-m-d_H-i-s') . '.db';
    if (copy($db_file, $backup_file)) {
        echo "✓ Existing database backed up to: " . basename($backup_file) . "\n";
    } else {
        echo "✗ Warning: Could not backup existing database\n";
    }
    
    // Remove old database
    unlink($db_file);
    echo "✓ Old database removed\n";
}

echo "\nInitializing new database...\n";

// Initialize database
$database = new Database();
if ($database->initializeDatabase()) {
    echo "✓ Database initialized successfully!\n\n";
    echo "Default admin user created:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n";
    echo "  Email: admin@foodinventory.local\n\n";
    echo "⚠️  IMPORTANT: Change the admin password immediately!\n\n";
    echo "Default group 'Default Group' has been created.\n";
    echo "The admin user has been assigned as owner of this group.\n\n";
    echo "=== Initialization Complete ===\n";
} else {
    echo "✗ Database initialization failed!\n";
    echo "Check the error messages above for details.\n";
    exit(1);
}
?>
