# Changes Summary - Nuts Allergen Addition

**Date**: 2025-11-01  
**Version**: 1.0.0-rc2

## Overview

Added support for tracking "Nuts" as a fourth allergen category alongside the existing Gluten, Milk, and Soy allergens.

## Files Changed

### Database Schema
1. **src/database/schema.sql**
   - Added `contains_nuts` column to `foods` table (line 147)
   - Added `contains_nuts` column to `ingredients` table (line 171)

### Migration Files
2. **src/database/migrations/006_add_nuts_allergen.sql** (NEW)
   - Migration script to add `contains_nuts` column to existing databases
   - Safe to run multiple times (idempotent)

### Views - Add/Edit Forms
3. **src/views/add_food.php**
   - Added "Contains Nuts" checkbox to allergen section (lines 246-249)

4. **src/views/add_ingredient.php**
   - Added "Contains Nuts" checkbox to allergen section (lines 255-258)

5. **src/views/edit_food.php**
   - Added "Contains Nuts" checkbox to allergen section (lines 229-232)

6. **src/views/edit_ingredient.php**
   - Added "Contains Nuts" checkbox to allergen section (lines 166-169)

### Views - Display/Filter
7. **src/views/dashboard.php**
   - Added Nuts badge display in ingredients table (line 317)
   - Added Nuts badge display in foods table (line 386)
   - Badge color: Red (#d32f2f)

8. **src/views/track_meal.php**
   - Added "Exclude Nuts" filter checkbox (lines 92-95)
   - Added Nuts badge display in search results (line 144)

### Controller Logic
9. **src/controllers/InventoryController.php**
   - Added `contains_nuts` handling in `addFood()` method (lines 207, 303, 364)
   - Added `contains_nuts` handling in `addIngredient()` method (lines 512, 790)
   - Added `contains_nuts` handling in `editFood()` method (line 364)
   - Added `contains_nuts` handling in `editIngredient()` method (line 790)
   - Added `$exclude_nuts` filter in `trackMeal()` method (lines 1496, 1512, 1551)

### Migration Tools
10. **migrate.sh** (NEW)
    - Automated migration script
    - Creates backups automatically
    - Verifies schema changes
    - Safe to run multiple times

11. **MIGRATIONS.md** (NEW)
    - Complete migration documentation
    - Instructions for manual and automated migrations
    - Troubleshooting guide

12. **CHANGES_SUMMARY.md** (NEW - this file)
    - Summary of all changes made

## Database Changes

### Foods Table
```sql
ALTER TABLE foods ADD COLUMN contains_nuts INTEGER DEFAULT 0 CHECK(contains_nuts IN (0,1));
```

### Ingredients Table
```sql
ALTER TABLE ingredients ADD COLUMN contains_nuts INTEGER DEFAULT 0 CHECK(contains_nuts IN (0,1));
```

## UI Changes

### Add/Edit Forms
- New checkbox: "Contains Nuts" in the Allergens section
- Positioned after "Contains Soy" checkbox
- Works with both single-add and bulk-add modes

### Dashboard & Track Meal
- New allergen badge: **Nuts** with red background (#d32f2f)
- Consistent with other allergen badges (Gluten, Milk, Soy)
- Displays alongside other allergen indicators

### Filtering
- New filter option in Track Meal: "Exclude Nuts"
- Filters out items containing nuts from search results
- Works in combination with other allergen filters

## Migrating Your Live Database

### Option 1: Automated (Recommended)
```bash
./migrate.sh
```

This will:
1. Backup your database to `backups/food_inventory_TIMESTAMP.db`
2. Apply the migration
3. Verify the changes
4. Show a summary

### Option 2: Manual
```bash
# Create backup
cp database/food_inventory.db backups/food_inventory_$(date +%Y%m%d_%H%M%S).db

# Apply migration
sqlite3 database/food_inventory.db < src/database/migrations/006_add_nuts_allergen.sql
```

## Verification

After migration, verify the changes:

```bash
# Check foods table
sqlite3 database/food_inventory.db "PRAGMA table_info(foods);" | grep contains_nuts

# Check ingredients table
sqlite3 database/food_inventory.db "PRAGMA table_info(ingredients);" | grep contains_nuts
```

Expected output:
```
12|contains_nuts|INTEGER|0|0|1
```

## Testing Checklist

After applying the migration, test the following:

- [ ] Add new food item with "Contains Nuts" checked
- [ ] Edit existing food item and set "Contains Nuts"
- [ ] Add new ingredient with "Contains Nuts" checked
- [ ] Edit existing ingredient and set "Contains Nuts"
- [ ] Verify Nuts badge displays on dashboard for marked items
- [ ] Use "Exclude Nuts" filter in Track Meal
- [ ] Verify filtered results don't show items with nuts
- [ ] Check that existing data still displays correctly

## Rollback

If you need to rollback:

```bash
# Stop web server first
# Then restore from backup
cp backups/food_inventory_TIMESTAMP.db database/food_inventory.db
# Restart web server
```

## Notes

- **Default Value**: New `contains_nuts` column defaults to `0` (false) for all existing records
- **Backward Compatible**: Existing data and queries continue to work
- **Safe Migration**: Migration script checks if column exists before adding it
- **Automatic Backups**: Migration script creates timestamped backups automatically

## Support

For issues or questions:
1. Check `MIGRATIONS.md` for detailed migration instructions
2. Review backup files in `backups/` directory
3. Verify web server has stopped before manual migrations
4. Check database file permissions: `ls -la database/food_inventory.db`
