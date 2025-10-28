# Database Migration Guide

This guide will help you migrate your existing database to the new schema that includes:
- Configurable units table
- CHECK constraints to prevent negative quantities
- Low stock foods tracking

## ⚠️ Important: Backup First!

**Always backup your database before running migrations!**

```bash
# Create a backup of your current database
cp database/food_inventory.db database/food_inventory_backup_$(date +%Y%m%d_%H%M%S).db
```

## Migration Steps

### Option 1: Using SQLite Command Line (Recommended)

```bash
# Navigate to your project directory
cd /home/russ/projects/food-inventory-db

# Run the migration script
sqlite3 database/food_inventory.db < src/database/migrate_to_units.sql

# Verify the migration was successful
sqlite3 database/food_inventory.db "SELECT COUNT(*) FROM units;"
# Should return a number > 0 (default is 26 units)

sqlite3 database/food_inventory.db "SELECT * FROM units LIMIT 5;"
# Should display the first 5 units
```

### Option 2: Using PHP Script

Create a temporary migration script:

```php
<?php
require_once 'src/database/Database.php';

$database = new Database();
$db = $database->getConnection();

// Read migration file
$sql = file_get_contents('src/database/migrate_to_units.sql');

// Execute migration
try {
    $db->exec($sql);
    echo "Migration completed successfully!\n";
    
    // Verify units table
    $stmt = $db->query("SELECT COUNT(*) as count FROM units");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Units added: " . $result['count'] . "\n";
    
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    echo "Please restore from backup!\n";
}
?>
```

Save as `migrate.php` in your project root and run:
```bash
php migrate.php
```

## What the Migration Does

1. **Creates `units` table** - Adds a new table for configurable measurement units
2. **Inserts default units** - Adds 26 common units (cups, oz, lbs, etc.)
3. **Adds CHECK constraints** - Prevents negative quantities in `foods` and `ingredient_locations` tables
4. **Recreates tables** - SQLite requires table recreation to add constraints (data is preserved)
5. **Recreates views** - Updates database views to work with new table structure

## Verification

After migration, verify everything is working:

```bash
# Check units table exists and has data
sqlite3 database/food_inventory.db "SELECT COUNT(*) FROM units;"

# Check existing foods data is preserved
sqlite3 database/food_inventory.db "SELECT COUNT(*) FROM foods;"

# Check existing ingredients data is preserved
sqlite3 database/food_inventory.db "SELECT COUNT(*) FROM ingredients;"

# Test CHECK constraint (should fail)
sqlite3 database/food_inventory.db "INSERT INTO foods (name, quantity) VALUES ('Test', -5);"
# Expected: Error: CHECK constraint failed: foods
```

## Rollback (If Needed)

If something goes wrong, restore from your backup:

```bash
# Stop the web server if running
# Then restore the backup
cp database/food_inventory_backup_YYYYMMDD_HHMMSS.db database/food_inventory.db

# Restart the web server
```

## Post-Migration

After successful migration:

1. **Test the application** - Log in and verify all features work
2. **Check unit management** - Go to "📏 Units" in admin menu
3. **Test bulk search** - Unit dropdown should show database units
4. **Keep the backup** - Don't delete the backup file for at least a few days

## Troubleshooting

### "table units already exists"
The migration is safe to run multiple times. `CREATE TABLE IF NOT EXISTS` and `INSERT OR IGNORE` prevent duplicates.

### "no such table: foods"
This means the migration partially failed. Restore from backup and try again.

### "foreign key constraint failed"
Check that all referenced tables (users, groups) exist and have data before running migration.

## Support

If you encounter issues:
1. Check the error message carefully
2. Restore from backup
3. Ensure your database file has write permissions: `chmod 664 database/food_inventory.db`
4. Check the migration script for syntax errors

---

**Remember: Always backup before migrating!**
