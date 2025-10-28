-- Food & Ingredient Inventory Database Schema
-- Created: 2024

-- Create database (uncomment if creating from scratch)
-- CREATE DATABASE food_inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE food_inventory_db;

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS food_ingredients;
DROP TABLE IF EXISTS recipes;
DROP TABLE IF EXISTS ingredient_locations;
DROP TABLE IF EXISTS ingredients;
DROP TABLE IF EXISTS foods;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS stores;
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
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

-- Create stores table for purchase locations
CREATE TABLE stores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    address TEXT,
    phone VARCHAR(20),
    website VARCHAR(255),
    notes TEXT,
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

-- Create foods table
CREATE TABLE foods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
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
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    UNIQUE(ingredient_id, location)
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

-- Insert default stores
INSERT INTO stores (name, address, phone, website, notes, is_active) VALUES
('Walmart', '123 Main St, Anytown, ST 12345', '(555) 123-4567', 'https://walmart.com', 'Large grocery chain', 1),
('Target', '456 Oak Ave, Anytown, ST 12345', '(555) 234-5678', 'https://target.com', 'Department store with groceries', 1),
('Kroger', '789 Pine Rd, Anytown, ST 12345', '(555) 345-6789', 'https://kroger.com', 'Grocery store chain', 1),
('Whole Foods', '321 Elm St, Anytown, ST 12345', '(555) 456-7890', 'https://wholefoodsmarket.com', 'Organic and natural foods', 1),
('Costco', '654 Cedar Blvd, Anytown, ST 12345', '(555) 567-8901', 'https://costco.com', 'Warehouse club for bulk shopping', 1),
('Local Market', '987 Maple Dr, Anytown, ST 12345', '(555) 678-9012', '', 'Small local grocery store', 1),
('Farmers Market', 'Downtown Square, Anytown, ST 12345', '', '', 'Weekly farmers market', 1),
('Online Order', '', '', '', 'Various online grocery delivery services', 1);

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
INSERT INTO foods (name, category, quantity, unit, expiry_date, purchase_date, purchase_location, location, notes, group_id) VALUES
('Bananas', 'Fruits', 6, 'pieces', date('now', '+5 days'), date('now'), 'Walmart', 'Counter', 'Yellow, ripe', 1),
('Milk', 'Dairy', 1, 'liter', date('now', '+7 days'), date('now'), 'Kroger', 'Refrigerator', '2% fat', 1),
('Bread', 'Grains', 1, 'loaf', date('now', '+3 days'), date('now'), 'Local Market', 'Pantry', 'Whole wheat', 1);

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
CREATE VIEW expiring_foods AS
SELECT 
    id, name, category, quantity, unit, expiry_date, location,
    CAST(julianday(expiry_date) - julianday('now') AS INTEGER) as days_until_expiry
FROM foods 
WHERE expiry_date IS NOT NULL 
    AND date(expiry_date) <= date('now', '+7 days')
    AND date(expiry_date) >= date('now')
ORDER BY expiry_date ASC;

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
