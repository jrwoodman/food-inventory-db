-- Fix categories table unique constraint
-- The constraint should be on (name, type) not just name
-- This allows the same category name for both food and ingredient types

-- Step 1: Create new table with correct constraint
CREATE TABLE categories_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    type TEXT CHECK(type IN ('food', 'ingredient')) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(name, type)
);

-- Step 2: Copy data from old table
INSERT INTO categories_new (id, name, type, description, created_at, updated_at)
SELECT id, name, type, description, created_at, updated_at
FROM categories;

-- Step 3: Drop old table
DROP TABLE categories;

-- Step 4: Rename new table
ALTER TABLE categories_new RENAME TO categories;
