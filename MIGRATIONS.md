# Database Migrations Guide

This document explains how to apply database schema changes to your live Food Inventory database.

## Quick Start

To apply all pending migrations to your database:

```bash
./migrate.sh
```

The script will:
1. Create a timestamped backup of your database
2. Apply any pending schema changes
3. Verify the changes were applied correctly
4. Display a summary of what was changed

## Recent Migrations

### Migration 006: Add Nuts Allergen (2025-11-01)
- **File**: `src/database/migrations/006_add_nuts_allergen.sql`
- **Changes**:
  - Adds `contains_nuts` column to `foods` table
  - Adds `contains_nuts` column to `ingredients` table
- **Impact**: Allows tracking of nut allergens in food items and ingredients

### Migration 005: Add Allergen Tracking (2025-11-01)
- **File**: `src/database/migrations/005_add_allergens.sql`
- **Changes**:
  - Adds `contains_gluten` column to `foods` and `ingredients` tables
  - Adds `contains_milk` column to `foods` and `ingredients` tables
  - Adds `contains_soy` column to `foods` and `ingredients` tables
- **Impact**: Enables allergen tracking and filtering

## Manual Migration

If you prefer to apply migrations manually:

```bash
# Create a backup first
cp database/food_inventory.db backups/food_inventory_$(date +%Y%m%d_%H%M%S).db

# Apply a specific migration
sqlite3 database/food_inventory.db < src/database/migrations/006_add_nuts_allergen.sql
```

## Verifying Migrations

To check if a migration has been applied:

```bash
# Check foods table structure
sqlite3 database/food_inventory.db "PRAGMA table_info(foods);"

# Check ingredients table structure
sqlite3 database/food_inventory.db "PRAGMA table_info(ingredients);"

# Check for specific columns
sqlite3 database/food_inventory.db "PRAGMA table_info(foods);" | grep contains_nuts
```

## Rollback

If you need to rollback a migration:

1. Stop your web server
2. Restore from the backup created by the migration script:
   ```bash
   cp backups/food_inventory_YYYYMMDD_HHMMSS.db database/food_inventory.db
   ```
3. Restart your web server

## Migration File Format

Migration files follow this naming convention: `XXX_description.sql`

- `XXX`: Three-digit sequence number (001, 002, etc.)
- `description`: Brief description using underscores

Example:
```sql
-- Migration: Add nuts allergen tracking column
-- Date: 2025-11-01
-- Description: Adds boolean column for tracking Nuts allergen

ALTER TABLE foods ADD COLUMN contains_nuts INTEGER DEFAULT 0 CHECK(contains_nuts IN (0,1));
ALTER TABLE ingredients ADD COLUMN contains_nuts INTEGER DEFAULT 0 CHECK(contains_nuts IN (0,1));
```

## Best Practices

1. **Always backup before migrating**: The migration script does this automatically
2. **Test migrations on a copy first**: If you're unsure, test on a database copy
3. **Apply migrations in sequence**: Don't skip migration numbers
4. **Keep backups**: Don't delete backup files until you've verified everything works
5. **Check the application**: After migrating, test the web interface to ensure everything works

## Troubleshooting

### Error: "table foods has no column named contains_nuts"
This means the migration hasn't been applied yet. Run `./migrate.sh`.

### Error: "duplicate column name: contains_nuts"
The migration has already been applied. This is safe to ignore.

### Error: "unable to open database file"
Check that:
- The database file exists at `database/food_inventory.db`
- You have write permissions to the database file
- The database isn't locked by another process (stop your web server first)

## Schema Reference

After all migrations, your tables should have these allergen columns:

**foods table:**
- `contains_gluten` (INTEGER, 0 or 1)
- `contains_milk` (INTEGER, 0 or 1)
- `contains_soy` (INTEGER, 0 or 1)
- `contains_nuts` (INTEGER, 0 or 1)

**ingredients table:**
- `contains_gluten` (INTEGER, 0 or 1)
- `contains_milk` (INTEGER, 0 or 1)
- `contains_soy` (INTEGER, 0 or 1)
- `contains_nuts` (INTEGER, 0 or 1)

## Support

If you encounter issues with migrations:

1. Check the backup files in `backups/` directory
2. Review the migration file contents in `src/database/migrations/`
3. Verify database file permissions: `ls -la database/food_inventory.db`
4. Check if the web server has the database locked: `lsof database/food_inventory.db`
