-- Migration: Add nuts allergen tracking column
-- Date: 2025-11-01
-- Description: Adds boolean column for tracking Nuts allergen to foods and ingredients tables

-- Add nuts allergen column to foods table
ALTER TABLE foods ADD COLUMN contains_nuts INTEGER DEFAULT 0 CHECK(contains_nuts IN (0,1));

-- Add nuts allergen column to ingredients table
ALTER TABLE ingredients ADD COLUMN contains_nuts INTEGER DEFAULT 0 CHECK(contains_nuts IN (0,1));
