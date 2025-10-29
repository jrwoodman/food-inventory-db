-- Complete migration script with proper cleanup
-- This script can be run multiple times safely

-- Step 1: Clean up any partial migration artifacts
DROP TABLE IF EXISTS food_locations;
DROP TABLE IF EXISTS foods_new;
DROP VIEW IF EXISTS food_totals;
DROP VIEW IF EXISTS food_location_details;
DROP VIEW IF EXISTS expiring_foods;
DROP VIEW IF EXISTS inventory_summary;

-- Step 2: Create food_locations table
CREATE TABLE food_locations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    food_id INTEGER NOT NULL,
    location VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK(quantity >= 0),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE,
    UNIQUE(food_id, location)
);

-- Step 3: Migrate existing data from foods table to food_locations
INSERT INTO food_locations (food_id, location, quantity, notes, created_at, updated_at)
SELECT 
    id as food_id,
    COALESCE(location, 'Unknown') as location,
    quantity,
    NULL as notes,
    created_at,
    updated_at
FROM foods;

-- Step 4: Create new foods table without location and quantity columns
CREATE TABLE foods_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    unit VARCHAR(50) DEFAULT 'pieces',
    expiry_date DATE,
    purchase_date DATE,
    purchase_location VARCHAR(255),
    notes TEXT,
    user_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Step 5: Copy data to new table (excluding location and quantity)
INSERT INTO foods_new (id, name, category, unit, expiry_date, purchase_date, purchase_location, notes, user_id, created_at, updated_at)
SELECT id, name, category, unit, expiry_date, purchase_date, purchase_location, notes, user_id, created_at, updated_at
FROM foods;

-- Step 6: Drop old table and rename new one
DROP TABLE foods;
ALTER TABLE foods_new RENAME TO foods;

-- Step 7: Recreate views with new structure

-- View: food_totals - Foods with total quantities across all locations
CREATE VIEW food_totals AS
SELECT 
    f.id,
    f.name,
    f.category,
    f.unit,
    f.expiry_date,
    f.purchase_date,
    f.purchase_location,
    f.notes,
    f.user_id,
    COALESCE(SUM(fl.quantity), 0) as total_quantity,
    f.created_at,
    f.updated_at
FROM foods f
LEFT JOIN food_locations fl ON f.id = fl.food_id
GROUP BY f.id;

-- View: food_location_details - Foods with individual location breakdown
CREATE VIEW food_location_details AS
SELECT 
    f.id,
    f.name,
    f.category,
    f.unit,
    f.expiry_date,
    f.purchase_date,
    f.purchase_location,
    f.notes,
    f.user_id,
    fl.location,
    fl.quantity,
    fl.notes as location_notes,
    f.created_at,
    f.updated_at
FROM foods f
LEFT JOIN food_locations fl ON f.id = fl.food_id;

-- View: expiring_foods - Foods expiring within 7 days
CREATE VIEW expiring_foods AS
SELECT 
    f.id,
    f.name,
    f.category,
    f.unit,
    f.expiry_date,
    f.purchase_date,
    f.purchase_location,
    f.notes,
    f.user_id,
    COALESCE(SUM(fl.quantity), 0) as total_quantity,
    CAST((julianday(f.expiry_date) - julianday('now')) AS INTEGER) as days_until_expiry
FROM foods f
LEFT JOIN food_locations fl ON f.id = fl.food_id
WHERE f.expiry_date IS NOT NULL 
  AND julianday(f.expiry_date) - julianday('now') <= 7
  AND julianday(f.expiry_date) - julianday('now') >= 0
GROUP BY f.id
ORDER BY f.expiry_date ASC;

-- View: inventory_summary - Recreate with updated structure
CREATE VIEW inventory_summary AS
SELECT 
    (SELECT COUNT(*) FROM foods) as total_foods,
    (SELECT COUNT(*) FROM ingredients) as total_ingredients,
    (SELECT COUNT(*) FROM expiring_foods) as expiring_soon,
    (SELECT COUNT(*) FROM low_stock_ingredients) as low_stock_items;
