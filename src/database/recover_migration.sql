-- Recovery script for partially failed migration
-- This will restore the database to a working state

-- Drop the broken view
DROP VIEW IF EXISTS expiring_foods;

-- Check if foods_new table exists (migration was interrupted)
-- If it exists, we need to complete the migration
-- If it doesn't exist, we need to restore the original structure

-- First, let's recreate the expiring_foods view to work with current state
-- This assumes foods table still exists with location and quantity columns
CREATE VIEW IF NOT EXISTS expiring_foods AS
SELECT 
    id,
    name,
    category,
    location,
    quantity,
    unit,
    expiry_date,
    purchase_date,
    purchase_location,
    notes,
    user_id,
    group_id,
    CAST((julianday(expiry_date) - julianday('now')) AS INTEGER) as days_until_expiry
FROM foods
WHERE expiry_date IS NOT NULL 
  AND julianday(expiry_date) - julianday('now') <= 7
  AND julianday(expiry_date) - julianday('now') >= 0
ORDER BY expiry_date ASC;
