-- Migration Script for Units Table and CHECK Constraints
-- This script safely adds the units table and updates constraints without losing data
-- Run this on your existing database

-- Step 1: Create units table
CREATE TABLE IF NOT EXISTS units (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    abbreviation VARCHAR(20) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 2: Insert default units (only if table is empty)
INSERT OR IGNORE INTO units (name, abbreviation, description, is_active) VALUES
-- Volume
('Cups', 'cups', 'Standard measuring cups', 1),
('Tablespoons', 'tbsp', 'Tablespoon measurement', 1),
('Teaspoons', 'tsp', 'Teaspoon measurement', 1),
('Fluid Ounces', 'fl oz', 'Fluid ounces', 1),
('Milliliters', 'ml', 'Milliliters', 1),
('Liters', 'l', 'Liters', 1),
('Liter', 'liter', 'Liter (alternate)', 1),
('Gallons', 'gal', 'Gallons', 1),
('Pints', 'pt', 'Pints', 1),
('Quarts', 'qt', 'Quarts', 1),

-- Weight
('Ounces', 'oz', 'Ounces (weight)', 1),
('Pounds', 'lbs', 'Pounds', 1),
('Grams', 'g', 'Grams', 1),
('Kilograms', 'kg', 'Kilograms', 1),

-- Count
('Pieces', 'pieces', 'Individual pieces or items', 1),
('Piece', 'piece', 'Individual piece (singular)', 1),
('Dozen', 'doz', 'Dozen (12 items)', 1),
('Cans', 'cans', 'Canned items', 1),
('Boxes', 'boxes', 'Boxed items', 1),
('Bags', 'bags', 'Bagged items', 1),
('Bottles', 'bottles', 'Bottled items', 1),
('Jars', 'jars', 'Jarred items', 1),
('Packages', 'pkgs', 'Packaged items', 1),
('Loaves', 'loaves', 'Loaves (bread, etc.)', 1),
('Loaf', 'loaf', 'Loaf (singular)', 1),
('Bunches', 'bunches', 'Bunches (vegetables, etc.)', 1);

-- Step 3: Drop any existing views that depend on tables we're about to modify
-- This prevents errors during table recreation
DROP VIEW IF EXISTS expiring_foods;
DROP VIEW IF EXISTS inventory_summary;

-- Step 4: Add CHECK constraints to foods table
-- SQLite requires recreating the table to add CHECK constraints
-- This preserves all existing data

-- Create temporary table with new constraints
CREATE TABLE foods_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK(quantity >= 0),
    unit VARCHAR(50) DEFAULT 'pieces',
    expiry_date DATE,
    purchase_date DATE,
    purchase_location VARCHAR(255),
    location VARCHAR(255),
    notes TEXT,
    user_id INTEGER,
    group_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

-- Copy data from old table to new table
INSERT INTO foods_new SELECT * FROM foods;

-- Drop old table
DROP TABLE foods;

-- Rename new table to original name
ALTER TABLE foods_new RENAME TO foods;

-- Step 5: Drop views that depend on ingredient_locations
DROP VIEW IF EXISTS ingredient_totals;
DROP VIEW IF EXISTS low_stock_ingredients;
DROP VIEW IF EXISTS ingredient_location_details;

-- Step 6: Add CHECK constraints to ingredient_locations table
-- Create temporary table with new constraints
CREATE TABLE ingredient_locations_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ingredient_id INTEGER NOT NULL,
    location VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK(quantity >= 0),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    UNIQUE(ingredient_id, location)
);

-- Copy data from old table to new table
INSERT INTO ingredient_locations_new SELECT * FROM ingredient_locations;

-- Drop old table
DROP TABLE ingredient_locations;

-- Rename new table to original name
ALTER TABLE ingredient_locations_new RENAME TO ingredient_locations;

-- Step 7: Recreate all views with new table structure

CREATE VIEW expiring_foods AS
SELECT 
    id, name, category, quantity, unit, expiry_date, location,
    CAST(julianday(expiry_date) - julianday('now') AS INTEGER) as days_until_expiry
FROM foods 
WHERE expiry_date IS NOT NULL 
    AND date(expiry_date) <= date('now', '+7 days')
    AND date(expiry_date) >= date('now')
ORDER BY expiry_date ASC;

CREATE VIEW ingredient_totals AS
SELECT 
    i.id,
    i.name,
    i.category,
    i.unit,
    i.supplier,
    COALESCE(SUM(il.quantity), 0) as total_quantity,
    GROUP_CONCAT(il.location || ': ' || il.quantity) as location_breakdown
FROM ingredients i
LEFT JOIN ingredient_locations il ON i.id = il.ingredient_id
GROUP BY i.id, i.name, i.category, i.unit, i.supplier;

CREATE VIEW low_stock_ingredients AS
SELECT 
    id, name, category, total_quantity, unit, supplier, location_breakdown
FROM ingredient_totals
WHERE total_quantity <= 10
ORDER BY total_quantity ASC;

CREATE VIEW ingredient_location_details AS
SELECT 
    i.id as ingredient_id,
    i.name as ingredient_name,
    i.category,
    i.unit,
    il.location,
    il.quantity,
    il.notes as location_notes,
    i.supplier,
    i.cost_per_unit
FROM ingredients i
LEFT JOIN ingredient_locations il ON i.id = il.ingredient_id
ORDER BY i.name, il.location;

CREATE VIEW inventory_summary AS
SELECT 
    'Foods' as type,
    COUNT(*) as total_items,
    SUM(CASE WHEN date(expiry_date) <= date('now', '+7 days') AND date(expiry_date) >= date('now') THEN 1 ELSE 0 END) as expiring_soon
FROM foods
UNION ALL
SELECT 
    'Ingredients' as type,
    COUNT(*) as total_items,
    SUM(CASE WHEN total_quantity <= 10 THEN 1 ELSE 0 END) as low_stock
FROM ingredient_totals;

-- Migration complete!
-- Your data has been preserved and the new schema has been applied.
