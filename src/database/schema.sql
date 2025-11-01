-- Food & Ingredient Inventory Database Schema
-- Created: 2024

-- Create database (uncomment if creating from scratch)
-- CREATE DATABASE food_inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE food_inventory_db;

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS food_ingredients;
DROP TABLE IF EXISTS recipes;
DROP TABLE IF EXISTS food_locations;
DROP TABLE IF EXISTS ingredient_locations;
DROP TABLE IF EXISTS ingredients;
DROP TABLE IF EXISTS foods;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS store_locations;
DROP TABLE IF EXISTS store_chains;
DROP TABLE IF EXISTS stores;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS user_groups;
DROP TABLE IF EXISTS groups;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS users;

-- Create users table for authentication
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role TEXT CHECK(role IN ('admin', 'user', 'viewer')) DEFAULT 'user',
    is_active BOOLEAN DEFAULT 1,
    default_group_id INTEGER,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (default_group_id) REFERENCES groups(id) ON DELETE SET NULL
);

-- Create user sessions table for session management
CREATE TABLE user_sessions (
    id TEXT PRIMARY KEY,
    user_id INTEGER NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create groups table for shared inventory management
CREATE TABLE groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user_groups junction table for many-to-many relationship
CREATE TABLE user_groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    group_id INTEGER NOT NULL,
    role TEXT CHECK(role IN ('owner', 'admin', 'member')) DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    UNIQUE(user_id, group_id)
);

-- Create locations table for storage locations
CREATE TABLE locations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create store_chains table for store brands/chains
CREATE TABLE store_chains (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    website VARCHAR(255),
    notes TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create store_locations table for physical store locations
CREATE TABLE store_locations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    chain_id INTEGER NOT NULL,
    location_name VARCHAR(255), -- e.g., "Downtown", "North Side"
    address TEXT,
    phone VARCHAR(20),
    hours TEXT,
    notes TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chain_id) REFERENCES store_chains(id) ON DELETE CASCADE,
    UNIQUE(chain_id, location_name)
);

-- Create units table for measurement units
CREATE TABLE units (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    abbreviation VARCHAR(20) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table for better organization
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    type TEXT CHECK(type IN ('food', 'ingredient')) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create foods table (without location and quantity - now in food_locations)
CREATE TABLE foods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    brand VARCHAR(255),
    unit VARCHAR(50) DEFAULT 'pieces',
    expiry_date DATE,
    purchase_date DATE,
    purchase_location VARCHAR(255),
    notes TEXT,
    contains_gluten INTEGER DEFAULT 0 CHECK(contains_gluten IN (0,1)),
    contains_milk INTEGER DEFAULT 0 CHECK(contains_milk IN (0,1)),
    contains_soy INTEGER DEFAULT 0 CHECK(contains_soy IN (0,1)),
    user_id INTEGER,
    group_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

-- Create ingredients table (master ingredient info)
CREATE TABLE ingredients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    unit VARCHAR(50) DEFAULT 'oz',
    cost_per_unit DECIMAL(10,2),
    supplier VARCHAR(255),
    purchase_date DATE,
    purchase_location VARCHAR(255),
    expiry_date DATE,
    notes TEXT,
    contains_gluten INTEGER DEFAULT 0 CHECK(contains_gluten IN (0,1)),
    contains_milk INTEGER DEFAULT 0 CHECK(contains_milk IN (0,1)),
    contains_soy INTEGER DEFAULT 0 CHECK(contains_soy IN (0,1)),
    user_id INTEGER,
    group_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

-- Create ingredient_locations table (quantities per location)
CREATE TABLE ingredient_locations (
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

-- Create food_locations table (quantities per location for foods)
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

-- Create recipes table (for future expansion)
CREATE TABLE recipes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    instructions TEXT,
    prep_time INTEGER, -- in minutes
    cook_time INTEGER, -- in minutes
    servings INTEGER DEFAULT 1,
    difficulty TEXT CHECK(difficulty IN ('Easy', 'Medium', 'Hard')) DEFAULT 'Medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create junction table for recipe ingredients
CREATE TABLE food_ingredients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    recipe_id INTEGER NOT NULL,
    ingredient_id INTEGER,
    food_id INTEGER,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    notes VARCHAR(255),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE SET NULL,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE SET NULL
);

-- Insert default categories
INSERT INTO categories (name, type, description) VALUES
-- Food categories
('Fruits', 'food', 'Fresh and dried fruits'),
('Vegetables', 'food', 'Fresh and frozen vegetables'),
('Meat', 'food', 'All types of meat and poultry'),
('Dairy', 'food', 'Milk, cheese, yogurt, and dairy products'),
('Grains', 'food', 'Rice, pasta, bread, and grain products'),
('Beverages', 'food', 'Drinks and liquid refreshments'),
('Snacks', 'food', 'Chips, crackers, and snack foods'),
('Frozen', 'food', 'Frozen foods and ice cream'),
('Canned', 'food', 'Canned and preserved foods'),
('Condiments', 'food', 'Sauces, dressings, and condiments'),

-- Ingredient categories
('Spices', 'ingredient', 'Dried spices and seasonings'),
('Herbs', 'ingredient', 'Fresh and dried herbs'),
('Oils', 'ingredient', 'Cooking oils and fats'),
('Vinegars', 'ingredient', 'Various types of vinegar'),
('Flour', 'ingredient', 'All purpose and specialty flours'),
('Sugar', 'ingredient', 'Sugar and sweeteners'),
('Salt', 'ingredient', 'Table salt and specialty salts'),
('Baking', 'ingredient', 'Baking powder, soda, and baking ingredients'),
('Sauces', 'ingredient', 'Cooking sauces and liquid seasonings'),
('Extracts', 'ingredient', 'Vanilla and other flavor extracts');

-- Insert default locations
INSERT INTO locations (name, description, is_active) VALUES
('Refrigerator', 'Main refrigerator for perishables', 1),
('Freezer', 'Freezer for long-term frozen storage', 1),
('Pantry', 'Dry goods and non-perishables', 1),
('Counter', 'Kitchen counter and countertop storage', 1),
('Cupboard', 'Kitchen cupboards and cabinets', 1),
('Basement', 'Basement storage area', 1),
('Spice Rack', 'Dedicated spice and herb storage', 1),
('Wine Rack', 'Wine and beverage storage', 1),
('Other', 'Miscellaneous storage locations', 1);

-- Insert default store chains
INSERT INTO store_chains (name, website, notes, is_active) VALUES
('Walmart', 'https://walmart.com', 'Large grocery chain', 1),
('Target', 'https://target.com', 'Department store with groceries', 1),
('Kroger', 'https://kroger.com', 'Grocery store chain', 1),
('Whole Foods', 'https://wholefoodsmarket.com', 'Organic and natural foods', 1),
('Costco', 'https://costco.com', 'Warehouse club for bulk shopping', 1),
('Local Market', '', 'Small local grocery store', 1),
('Farmers Market', '', 'Weekly farmers market', 1),
('Online Order', '', 'Various online grocery delivery services', 1);

-- Insert default store locations
INSERT INTO store_locations (chain_id, location_name, address, phone, hours, notes, is_active) VALUES
(1, 'Main Street', '123 Main St, Anytown, ST 12345', '(555) 123-4567', 'Mon-Sun 8am-10pm', 'Main location', 1),
(2, 'Oak Avenue', '456 Oak Ave, Anytown, ST 12345', '(555) 234-5678', 'Mon-Sun 8am-11pm', 'Near downtown', 1),
(3, 'Pine Road', '789 Pine Rd, Anytown, ST 12345', '(555) 345-6789', 'Mon-Sun 7am-11pm', '24-hour pharmacy', 1),
(4, 'Elm Street', '321 Elm St, Anytown, ST 12345', '(555) 456-7890', 'Mon-Sun 8am-9pm', 'Organic selection', 1),
(5, 'Cedar Boulevard', '654 Cedar Blvd, Anytown, ST 12345', '(555) 567-8901', 'Mon-Sat 9am-8:30pm, Sun 10am-6pm', 'Membership required', 1),
(6, 'Main Location', '987 Maple Dr, Anytown, ST 12345', '(555) 678-9012', 'Mon-Sat 7am-9pm', 'Family owned', 1),
(7, 'Downtown Square', 'Downtown Square, Anytown, ST 12345', '', 'Saturdays 8am-2pm', 'Seasonal', 1);

-- Insert default units
INSERT INTO units (name, abbreviation, description, is_active) VALUES
-- Volume
('Cups', 'cups', 'Standard measuring cups', 1),
('Tablespoons', 'tbsp', 'Tablespoon measurement', 1),
('Teaspoons', 'tsp', 'Teaspoon measurement', 1),
('Fluid Ounces', 'fl oz', 'Fluid ounces', 1),
('Milliliters', 'ml', 'Milliliters', 1),
('Liters', 'l', 'Liters', 1),
('Gallons', 'gal', 'Gallons', 1),
('Pints', 'pt', 'Pints', 1),
('Quarts', 'qt', 'Quarts', 1),

-- Weight
('Ounces', 'oz', 'Ounces (weight)', 1),
('Pounds', 'lbs', 'Pounds', 1),
('Grams', 'g', 'Grams', 1),
('Kilograms', 'kg', 'Kilograms', 1),

-- Count
('Pieces', 'pcs', 'Individual pieces or items', 1),
('Dozen', 'doz', 'Dozen (12 items)', 1),
('Cans', 'cans', 'Canned items', 1),
('Boxes', 'boxes', 'Boxed items', 1),
('Bags', 'bags', 'Bagged items', 1),
('Bottles', 'bottles', 'Bottled items', 1),
('Jars', 'jars', 'Jarred items', 1),
('Packages', 'pkgs', 'Packaged items', 1),
('Loaves', 'loaves', 'Loaves (bread, etc.)', 1),
('Bunches', 'bunches', 'Bunches (vegetables, etc.)', 1);

-- Insert default admin user (password: admin123)
-- Note: This is a demo password and should be changed immediately in production
INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active) VALUES
('admin', 'admin@foodinventory.local', '$2y$12$r9Zh0vIN0EEgBCQMCS5CquUUbaICCnEhBelNMe.K0TAtmag7xVWrO', 'System', 'Administrator', 'admin', 1);

-- Insert default group for existing users and data
INSERT INTO groups (name, description) VALUES
('Default Group', 'Default group for existing users and inventory items');

-- Assign admin user to default group as owner
INSERT INTO user_groups (user_id, group_id, role) VALUES
(1, 1, 'owner');

-- Insert some sample data for demonstration (assigned to default group)
INSERT INTO foods (name, category, unit, expiry_date, purchase_date, purchase_location, notes, group_id) VALUES
('Bananas', 'Fruits', 'pieces', date('now', '+5 days'), date('now'), 'Walmart', 'Yellow, ripe', 1),
('Milk', 'Dairy', 'liter', date('now', '+7 days'), date('now'), 'Kroger', '2% fat', 1),
('Bread', 'Grains', 'loaf', date('now', '+3 days'), date('now'), 'Local Market', 'Whole wheat', 1);

-- Insert food location data
INSERT INTO food_locations (food_id, location, quantity, notes) VALUES
(1, 'Counter', 6, 'Main bunch'),
(2, 'Refrigerator', 1, 'Top shelf'),
(3, 'Pantry', 1, 'Bread box');

INSERT INTO ingredients (name, category, unit, cost_per_unit, supplier, purchase_date, purchase_location, expiry_date, notes, group_id) VALUES
('Salt', 'Salt', 'g', 0.002, 'Local Grocery', date('now'), 'Local Market', date('now', '+365 days'), 'Table salt', 1),
('Black Pepper', 'Spices', 'g', 0.20, 'Spice Shop', date('now'), 'Whole Foods', date('now', '+180 days'), 'Freshly ground', 1),
('Olive Oil', 'Oils', 'ml', 0.02, 'Market', date('now'), 'Costco', date('now', '+730 days'), 'Extra virgin', 1);

-- Insert ingredient location data
INSERT INTO ingredient_locations (ingredient_id, location, quantity, notes) VALUES
(1, 'Pantry', 500, 'Main storage'),
(1, 'Spice Rack', 50, 'Easy access'),
(2, 'Spice Rack', 30, 'Primary location'),
(2, 'Pantry', 20, 'Backup supply'),
(3, 'Pantry', 400, 'Large bottle'),
(3, 'Counter', 100, 'Small bottle for daily use');

-- Create views for common queries

-- View for foods with total quantities across all locations
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
    f.group_id,
    COALESCE(SUM(fl.quantity), 0) as total_quantity,
    f.created_at,
    f.updated_at
FROM foods f
LEFT JOIN food_locations fl ON f.id = fl.food_id
GROUP BY f.id;

-- View for foods with location details
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
    f.group_id,
    fl.location,
    fl.quantity,
    fl.notes as location_notes,
    f.created_at,
    f.updated_at
FROM foods f
LEFT JOIN food_locations fl ON f.id = fl.food_id;

-- View for expiring foods (using total quantities)
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

-- View for ingredients with their total quantities across all locations
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

-- View for low stock ingredients (total quantity <= 10)
CREATE VIEW low_stock_ingredients AS
SELECT 
    id, name, category, total_quantity, unit, supplier, location_breakdown
FROM ingredient_totals
WHERE total_quantity <= 10
ORDER BY total_quantity ASC;

-- View for ingredient locations with details
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
