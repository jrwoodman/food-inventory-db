-- Migration: Add units and categories tables
-- Date: 2025-10-28
-- Description: Adds units and categories management tables to existing database

-- Create units table for measurement units
CREATE TABLE IF NOT EXISTS units (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    abbreviation VARCHAR(20) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table for better organization
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    type TEXT CHECK(type IN ('food', 'ingredient')) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default units (only if table is empty)
INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Cups', 'cups', 'Standard measuring cups', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Cups');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Tablespoons', 'tbsp', 'Tablespoon measurement', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Tablespoons');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Teaspoons', 'tsp', 'Teaspoon measurement', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Teaspoons');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Fluid Ounces', 'fl oz', 'Fluid ounces', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Fluid Ounces');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Milliliters', 'ml', 'Milliliters', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Milliliters');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Liters', 'l', 'Liters', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Liters');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Gallons', 'gal', 'Gallons', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Gallons');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Pints', 'pt', 'Pints', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Pints');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Quarts', 'qt', 'Quarts', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Quarts');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Ounces', 'oz', 'Ounces (weight)', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Ounces');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Pounds', 'lbs', 'Pounds', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Pounds');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Grams', 'g', 'Grams', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Grams');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Kilograms', 'kg', 'Kilograms', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Kilograms');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Pieces', 'pcs', 'Individual pieces or items', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Pieces');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Dozen', 'doz', 'Dozen (12 items)', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Dozen');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Cans', 'cans', 'Canned items', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Cans');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Boxes', 'boxes', 'Boxed items', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Boxes');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Bags', 'bags', 'Bagged items', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Bags');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Bottles', 'bottles', 'Bottled items', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Bottles');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Jars', 'jars', 'Jarred items', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Jars');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Packages', 'pkgs', 'Packaged items', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Packages');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Loaves', 'loaves', 'Loaves (bread, etc.)', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Loaves');

INSERT INTO units (name, abbreviation, description, is_active)
SELECT 'Bunches', 'bunches', 'Bunches (vegetables, etc.)', 1
WHERE NOT EXISTS (SELECT 1 FROM units WHERE name = 'Bunches');

-- Insert default categories (only if table is empty)
-- Food categories
INSERT INTO categories (name, type, description)
SELECT 'Fruits', 'food', 'Fresh and dried fruits'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Fruits' AND type = 'food');

INSERT INTO categories (name, type, description)
SELECT 'Vegetables', 'food', 'Fresh and frozen vegetables'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Vegetables' AND type = 'food');

INSERT INTO categories (name, type, description)
SELECT 'Meat', 'food', 'All types of meat and poultry'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Meat' AND type = 'food');

INSERT INTO categories (name, type, description)
SELECT 'Dairy', 'food', 'Milk, cheese, yogurt, and dairy products'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Dairy' AND type = 'food');

INSERT INTO categories (name, type, description)
SELECT 'Grains', 'food', 'Rice, pasta, bread, and grain products'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Grains' AND type = 'food');

INSERT INTO categories (name, type, description)
SELECT 'Beverages', 'food', 'Drinks and liquid refreshments'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Beverages' AND type = 'food');

INSERT INTO categories (name, type, description)
SELECT 'Snacks', 'food', 'Chips, crackers, and snack foods'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Snacks' AND type = 'food');

INSERT INTO categories (name, type, description)
SELECT 'Frozen', 'food', 'Frozen foods and ice cream'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Frozen' AND type = 'food');

INSERT INTO categories (name, type, description)
SELECT 'Canned', 'food', 'Canned and preserved foods'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Canned' AND type = 'food');

INSERT INTO categories (name, type, description)
SELECT 'Condiments', 'food', 'Sauces, dressings, and condiments'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Condiments' AND type = 'food');

-- Ingredient categories
INSERT INTO categories (name, type, description)
SELECT 'Spices', 'ingredient', 'Dried spices and seasonings'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Spices' AND type = 'ingredient');

INSERT INTO categories (name, type, description)
SELECT 'Herbs', 'ingredient', 'Fresh and dried herbs'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Herbs' AND type = 'ingredient');

INSERT INTO categories (name, type, description)
SELECT 'Oils', 'ingredient', 'Cooking oils and fats'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Oils' AND type = 'ingredient');

INSERT INTO categories (name, type, description)
SELECT 'Vinegars', 'ingredient', 'Various types of vinegar'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Vinegars' AND type = 'ingredient');

INSERT INTO categories (name, type, description)
SELECT 'Flour', 'ingredient', 'All purpose and specialty flours'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Flour' AND type = 'ingredient');

INSERT INTO categories (name, type, description)
SELECT 'Sugar', 'ingredient', 'Sugar and sweeteners'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Sugar' AND type = 'ingredient');

INSERT INTO categories (name, type, description)
SELECT 'Salt', 'ingredient', 'Table salt and specialty salts'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Salt' AND type = 'ingredient');

INSERT INTO categories (name, type, description)
SELECT 'Baking', 'ingredient', 'Baking powder, soda, and baking ingredients'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Baking' AND type = 'ingredient');

INSERT INTO categories (name, type, description)
SELECT 'Sauces', 'ingredient', 'Cooking sauces and liquid seasonings'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Sauces' AND type = 'ingredient');

INSERT INTO categories (name, type, description)
SELECT 'Extracts', 'ingredient', 'Vanilla and other flavor extracts'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Extracts' AND type = 'ingredient');
