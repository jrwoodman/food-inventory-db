-- Migration: Add multi-location support for foods
-- This migration converts the single-location model to a multi-location model
-- similar to how ingredients work.

-- Create food_locations table (similar to ingredient_locations)
CREATE TABLE IF NOT EXISTS food_locations (
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

-- Migrate existing data from foods.location to food_locations table
-- Only migrate records that have a location set
INSERT INTO food_locations (food_id, location, quantity, notes, created_at, updated_at)
SELECT 
    id as food_id,
    location,
    quantity,
    NULL as notes,
    created_at,
    updated_at
FROM foods
WHERE location IS NOT NULL AND location != '';

-- Update foods table: remove quantity and location columns
-- Note: SQLite doesn't support DROP COLUMN, so we need to recreate the table

-- Drop existing view that references foods table
DROP VIEW IF EXISTS expiring_foods;

-- Step 1: Create new foods table structure without quantity and location
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
    group_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

-- Step 2: Copy data from old table to new table (excluding location and quantity)
INSERT INTO foods_new (id, name, category, unit, expiry_date, purchase_date, purchase_location, notes, user_id, group_id, created_at, updated_at)
SELECT id, name, category, unit, expiry_date, purchase_date, purchase_location, notes, user_id, group_id, created_at, updated_at
FROM foods;

-- Step 3: Drop old table
DROP TABLE foods;

-- Step 4: Rename new table to original name
ALTER TABLE foods_new RENAME TO foods;

-- Create view for foods with total quantities (similar to ingredient_totals view)
CREATE VIEW IF NOT EXISTS food_totals AS
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
    f.group_id,
    COALESCE(SUM(fl.quantity), 0) as total_quantity,
    f.created_at,
    f.updated_at
FROM foods f
LEFT JOIN food_locations fl ON f.id = fl.food_id
GROUP BY f.id;

-- Create view for foods with location details (similar to ingredient_location_details)
CREATE VIEW IF NOT EXISTS food_location_details AS
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
    f.group_id,
    fl.location,
    fl.quantity,
    fl.notes as location_notes,
    f.created_at,
    f.updated_at
FROM foods f
LEFT JOIN food_locations fl ON f.id = fl.food_id;

-- Create view for expiring foods (update to use total quantities)
DROP VIEW IF EXISTS expiring_foods;
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
    f.group_id,
    COALESCE(SUM(fl.quantity), 0) as total_quantity,
    CAST((julianday(f.expiry_date) - julianday('now')) AS INTEGER) as days_until_expiry
FROM foods f
LEFT JOIN food_locations fl ON f.id = fl.food_id
WHERE f.expiry_date IS NOT NULL 
  AND julianday(f.expiry_date) - julianday('now') <= 7
  AND julianday(f.expiry_date) - julianday('now') >= 0
GROUP BY f.id
ORDER BY f.expiry_date ASC;
