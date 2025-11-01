#!/bin/bash

# Food Inventory Database Migration Script
# This script applies all pending database schema changes to your live SQLite database

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
DB_PATH="database/food_inventory.db"
BACKUP_DIR="backups"
MIGRATIONS_DIR="src/database/migrations"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Food Inventory Database Migration Tool${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if database exists
if [ ! -f "$DB_PATH" ]; then
    echo -e "${RED}Error: Database file not found at $DB_PATH${NC}"
    exit 1
fi

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Create backup
BACKUP_FILE="$BACKUP_DIR/food_inventory_$(date +%Y%m%d_%H%M%S).db"
echo -e "${YELLOW}Creating backup: $BACKUP_FILE${NC}"
cp "$DB_PATH" "$BACKUP_FILE"
echo -e "${GREEN}✓ Backup created successfully${NC}"
echo ""

# Function to apply a migration
apply_migration() {
    local migration_file=$1
    local migration_name=$(basename "$migration_file")
    
    echo -e "${YELLOW}Applying migration: $migration_name${NC}"
    
    if sqlite3 "$DB_PATH" < "$migration_file"; then
        echo -e "${GREEN}✓ Migration applied successfully: $migration_name${NC}"
        return 0
    else
        echo -e "${RED}✗ Migration failed: $migration_name${NC}"
        return 1
    fi
}

# Apply migrations in order
echo -e "${YELLOW}Checking for pending migrations...${NC}"
echo ""

# Migration 006: Add nuts allergen column
MIGRATION_006="$MIGRATIONS_DIR/006_add_nuts_allergen.sql"
if [ -f "$MIGRATION_006" ]; then
    # Check if column already exists
    if sqlite3 "$DB_PATH" "PRAGMA table_info(foods);" | grep -q "contains_nuts"; then
        echo -e "${YELLOW}⊘ Migration 006 already applied (contains_nuts column exists)${NC}"
    else
        if apply_migration "$MIGRATION_006"; then
            echo -e "${GREEN}✓ Added contains_nuts allergen column to foods and ingredients${NC}"
        else
            echo -e "${RED}✗ Failed to add contains_nuts column${NC}"
            echo -e "${YELLOW}You can restore from backup: $BACKUP_FILE${NC}"
            exit 1
        fi
    fi
else
    echo -e "${YELLOW}⊘ Migration 006 not found (006_add_nuts_allergen.sql)${NC}"
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Migration Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Summary:"
echo "- Database: $DB_PATH"
echo "- Backup: $BACKUP_FILE"
echo ""
echo "Verifying database schema..."

# Verify migrations
echo ""
echo "Foods table columns:"
sqlite3 "$DB_PATH" "PRAGMA table_info(foods);" | grep "contains_" || echo "No allergen columns found"

echo ""
echo "Ingredients table columns:"
sqlite3 "$DB_PATH" "PRAGMA table_info(ingredients);" | grep "contains_" || echo "No allergen columns found"

echo ""
echo -e "${GREEN}✓ All migrations completed successfully!${NC}"
echo -e "${YELLOW}Note: Keep the backup file ($BACKUP_FILE) until you verify everything works correctly.${NC}"
