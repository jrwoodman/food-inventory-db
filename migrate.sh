#!/bin/bash

# Migration script for Food Inventory Database
# This script applies database migrations to your existing SQLite database

set -e  # Exit on error

# Configuration
DB_PATH="database/food_inventory.db"
MIGRATIONS_DIR="src/database/migrations"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "================================================"
echo "Food Inventory Database Migration Script"
echo "================================================"
echo ""

# Check if database exists
if [ ! -f "$DB_PATH" ]; then
    echo -e "${RED}Error: Database file not found at $DB_PATH${NC}"
    echo "Please ensure you're running this script from the project root directory."
    exit 1
fi

# Check if sqlite3 is installed
if ! command -v sqlite3 &> /dev/null; then
    echo -e "${RED}Error: sqlite3 command not found${NC}"
    echo "Please install SQLite3:"
    echo "  Ubuntu/Debian: sudo apt-get install sqlite3"
    echo "  Manjaro/Arch: sudo pacman -S sqlite"
    exit 1
fi

# Create backup before migration
BACKUP_DIR="backups"
mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/food_inventory_$(date +%Y%m%d_%H%M%S).db"

echo -e "${YELLOW}Creating backup...${NC}"
cp "$DB_PATH" "$BACKUP_FILE"
echo -e "${GREEN}✓ Backup created: $BACKUP_FILE${NC}"
echo ""

# Apply migrations
echo -e "${YELLOW}Applying migrations...${NC}"
echo ""

# Check if migrations directory exists
if [ ! -d "$MIGRATIONS_DIR" ]; then
    echo -e "${RED}Error: Migrations directory not found at $MIGRATIONS_DIR${NC}"
    exit 1
fi

# Count migrations
MIGRATION_COUNT=$(ls -1 "$MIGRATIONS_DIR"/*.sql 2>/dev/null | wc -l)

if [ "$MIGRATION_COUNT" -eq 0 ]; then
    echo -e "${YELLOW}No migration files found in $MIGRATIONS_DIR${NC}"
    exit 0
fi

# Apply each migration file
for migration_file in "$MIGRATIONS_DIR"/*.sql; do
    if [ -f "$migration_file" ]; then
        migration_name=$(basename "$migration_file")
        echo -e "${YELLOW}Applying: $migration_name${NC}"
        
        if sqlite3 "$DB_PATH" < "$migration_file"; then
            echo -e "${GREEN}✓ Successfully applied: $migration_name${NC}"
        else
            echo -e "${RED}✗ Failed to apply: $migration_name${NC}"
            echo -e "${YELLOW}Database has been restored from backup${NC}"
            cp "$BACKUP_FILE" "$DB_PATH"
            exit 1
        fi
        echo ""
    fi
done

echo "================================================"
echo -e "${GREEN}Migration completed successfully!${NC}"
echo "================================================"
echo ""
echo "Summary:"
echo "  - Migrations applied: $MIGRATION_COUNT"
echo "  - Backup location: $BACKUP_FILE"
echo ""
echo "You can now start your application."
