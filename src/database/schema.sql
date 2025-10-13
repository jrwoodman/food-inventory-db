-- Food & Ingredient Inventory Database Schema
-- Created: 2024

-- Create database (uncomment if creating from scratch)
-- CREATE DATABASE food_inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE food_inventory_db;

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS food_ingredients;
DROP TABLE IF EXISTS recipes;
DROP TABLE IF EXISTS ingredients;
DROP TABLE IF EXISTS foods;
DROP TABLE IF EXISTS categories;

-- Create categories table for better organization
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('food', 'ingredient') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create foods table
CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'pieces',
    expiry_date DATE,
    purchase_date DATE,
    location VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category),
    INDEX idx_expiry (expiry_date),
    INDEX idx_location (location)
);

-- Create ingredients table
CREATE TABLE ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'oz',
    cost_per_unit DECIMAL(10,2),
    supplier VARCHAR(255),
    purchase_date DATE,
    expiry_date DATE,
    location VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category),
    INDEX idx_expiry (expiry_date),
    INDEX idx_supplier (supplier),
    INDEX idx_quantity (quantity)
);

-- Create recipes table (for future expansion)
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    instructions TEXT,
    prep_time INT, -- in minutes
    cook_time INT, -- in minutes
    servings INT DEFAULT 1,
    difficulty ENUM('Easy', 'Medium', 'Hard') DEFAULT 'Medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_difficulty (difficulty)
);

-- Create junction table for recipe ingredients
CREATE TABLE food_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    ingredient_id INT,
    food_id INT,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    notes VARCHAR(255),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE SET NULL,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE SET NULL,
    INDEX idx_recipe (recipe_id)
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

-- Insert some sample data for demonstration
INSERT INTO foods (name, category, quantity, unit, expiry_date, purchase_date, location, notes) VALUES
('Bananas', 'Fruits', 6, 'pieces', DATE_ADD(CURDATE(), INTERVAL 5 DAY), CURDATE(), 'Counter', 'Yellow, ripe'),
('Milk', 'Dairy', 1, 'liter', DATE_ADD(CURDATE(), INTERVAL 7 DAY), CURDATE(), 'Refrigerator', '2% fat'),
('Bread', 'Grains', 1, 'loaf', DATE_ADD(CURDATE(), INTERVAL 3 DAY), CURDATE(), 'Pantry', 'Whole wheat');

INSERT INTO ingredients (name, category, quantity, unit, cost_per_unit, supplier, purchase_date, expiry_date, location, notes) VALUES
('Salt', 'Salt', 500, 'g', 0.002, 'Local Grocery', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), 'Pantry', 'Table salt'),
('Black Pepper', 'Spices', 50, 'g', 0.20, 'Spice Shop', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'Spice Rack', 'Freshly ground'),
('Olive Oil', 'Oils', 500, 'ml', 0.02, 'Market', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 730 DAY), 'Pantry', 'Extra virgin');

-- Create views for common queries
CREATE VIEW expiring_foods AS
SELECT 
    id, name, category, quantity, unit, expiry_date, location,
    DATEDIFF(expiry_date, CURDATE()) as days_until_expiry
FROM foods 
WHERE expiry_date IS NOT NULL 
    AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND expiry_date >= CURDATE()
ORDER BY expiry_date ASC;

CREATE VIEW low_stock_ingredients AS
SELECT 
    id, name, category, quantity, unit, supplier, location
FROM ingredients 
WHERE quantity <= 10
ORDER BY quantity ASC;

CREATE VIEW inventory_summary AS
SELECT 
    'Foods' as type,
    COUNT(*) as total_items,
    SUM(CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND expiry_date >= CURDATE() THEN 1 ELSE 0 END) as expiring_soon
FROM foods
UNION ALL
SELECT 
    'Ingredients' as type,
    COUNT(*) as total_items,
    SUM(CASE WHEN quantity <= 10 THEN 1 ELSE 0 END) as low_stock
FROM ingredients;