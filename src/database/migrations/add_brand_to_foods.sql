-- Migration: Add brand column to foods table
-- Date: 2025-10-29

-- Add brand field to foods table
ALTER TABLE foods ADD COLUMN brand VARCHAR(255);

-- Update the updated_at timestamp
-- (SQLite will handle this automatically on next update)
