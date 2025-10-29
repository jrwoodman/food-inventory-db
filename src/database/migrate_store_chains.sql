-- Migration: Convert stores to store_chains + store_locations structure
-- This migration splits stores into chains and locations

-- Step 1: Create store_chains table
CREATE TABLE IF NOT EXISTS store_chains (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    website VARCHAR(255),
    notes TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 2: Create store_locations table
CREATE TABLE IF NOT EXISTS store_locations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    chain_id INTEGER NOT NULL,
    location_name VARCHAR(255), -- e.g., "Downtown", "Main Street"
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

-- Step 3: Migrate data from stores to store_chains
-- Extract unique store names as chains
INSERT INTO store_chains (name, website, notes, is_active, created_at, updated_at)
SELECT 
    name,
    website,
    'Migrated from stores table' as notes,
    is_active,
    created_at,
    updated_at
FROM stores;

-- Step 4: Migrate detailed store info to store_locations
-- Each existing store becomes a location under its chain
INSERT INTO store_locations (chain_id, location_name, address, phone, notes, is_active, created_at, updated_at)
SELECT 
    sc.id as chain_id,
    CASE 
        WHEN s.address IS NOT NULL AND s.address != '' THEN 'Main Location'
        ELSE NULL
    END as location_name,
    s.address,
    s.phone,
    s.notes,
    s.is_active,
    s.created_at,
    s.updated_at
FROM stores s
JOIN store_chains sc ON s.name = sc.name
WHERE s.address IS NOT NULL AND s.address != '';

-- Step 5: Drop old stores table
DROP TABLE IF EXISTS stores;

-- Step 6: Create view for backward compatibility
CREATE VIEW stores AS
SELECT 
    sc.id,
    sc.name,
    sc.website,
    sl.address,
    sl.phone,
    sc.notes,
    sc.is_active,
    sc.created_at,
    sc.updated_at
FROM store_chains sc
LEFT JOIN store_locations sl ON sc.id = sl.chain_id AND sl.id = (
    SELECT MIN(id) FROM store_locations WHERE chain_id = sc.id
);
