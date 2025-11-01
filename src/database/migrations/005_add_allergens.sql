-- Migration: Add allergen tracking columns
-- Date: 2025-11-01
-- Description: Adds boolean columns for tracking Gluten, Milk, and Soy allergens

-- Add allergen columns to foods table
ALTER TABLE foods ADD COLUMN contains_gluten INTEGER DEFAULT 0 CHECK(contains_gluten IN (0,1));
ALTER TABLE foods ADD COLUMN contains_milk INTEGER DEFAULT 0 CHECK(contains_milk IN (0,1));
ALTER TABLE foods ADD COLUMN contains_soy INTEGER DEFAULT 0 CHECK(contains_soy IN (0,1));

-- Add allergen columns to ingredients table
ALTER TABLE ingredients ADD COLUMN contains_gluten INTEGER DEFAULT 0 CHECK(contains_gluten IN (0,1));
ALTER TABLE ingredients ADD COLUMN contains_milk INTEGER DEFAULT 0 CHECK(contains_milk IN (0,1));
ALTER TABLE ingredients ADD COLUMN contains_soy INTEGER DEFAULT 0 CHECK(contains_soy IN (0,1));
