# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is a PHP-based web application for managing food and ingredient inventory with expiry tracking, low stock alerts, and a responsive web interface. The application uses SQLite for data storage and follows a simple MVC architecture pattern.

## Common Development Commands

### Local Development Server
```bash
# Start PHP built-in development server (recommended for development)
php -S localhost:8000 -t public/

# Access the application at http://localhost:8000
```

### Database Operations
```bash
# Create/initialize SQLite database with schema
sqlite3 database/food_inventory.db < src/database/schema.sql

# Open SQLite database for direct queries
sqlite3 database/food_inventory.db

# Backup SQLite database
cp database/food_inventory.db backups/food_inventory_$(date +%Y%m%d_%H%M%S).db

# View database schema
sqlite3 database/food_inventory.db ".schema"

# Export database to SQL file
sqlite3 database/food_inventory.db ".dump" > backup.sql
```

### Testing Database Connection
```bash
# Quick test of database connectivity
php -r "require 'config/config.php'; require 'src/database/Database.php'; \$db = new Database(); \$conn = \$db->getConnection(); echo \$conn ? 'Connected successfully' : 'Connection failed';"

# Check if SQLite extension is available
php -m | grep sqlite
```

### File Permissions Setup
```bash
# Ensure proper permissions for auto-created directories
chmod 755 uploads/ backups/ logs/ database/ 2>/dev/null || true

# Ensure database file is writable (if it exists)
chmod 664 database/food_inventory.db 2>/dev/null || true
```

## Architecture Overview

### MVC Pattern Implementation
- **Models**: Located in `src/models/` - Handle data operations using PDO
- **Views**: Located in `src/views/` - Contain presentation logic with embedded PHP
- **Controllers**: Single controller `src/controllers/InventoryController.php` handles all business logic
- **Entry Point**: `public/index.php` provides simple routing based on `?action=` parameter

### Database Architecture
- **PDO-based Database Class**: Custom connection management in `src/database/Database.php` using SQLite
- **Active Record Pattern**: Models implement CRUD operations with methods like `create()`, `read()`, `update()`, `delete()`
- **Specialized Methods**: Models include domain-specific methods like `getExpiringItems()` and `getLowStockItems()`
- **Database Views**: Pre-built views for common queries:
  - `expiring_foods`: Foods expiring within 7 days
  - `ingredient_totals`: Ingredients with total quantities across all locations
  - `low_stock_ingredients`: Ingredients with total quantity <= threshold
  - `ingredient_location_details`: Detailed view of ingredients with location breakdown
  - `inventory_summary`: Overview statistics
- **Multi-Location Support**: Ingredients can be stored in multiple locations with specific quantities per location
- **File-based Storage**: SQLite database file stored in `database/` directory for portability

### Key Database Tables
- **foods**: Main inventory items with expiry tracking
- **ingredients**: Master ingredient information (name, category, supplier, etc.) - quantities stored separately
- **ingredient_locations**: Stores quantities of ingredients per location (supports multi-location storage)
- **categories**: Organizational categories for both foods and ingredients
- **recipes**: Future expansion for recipe management
- **food_ingredients**: Junction table linking recipes to ingredients/foods

### Configuration System
- **Centralized Config**: `config/config.php` contains all application settings
- **Environment Support**: Supports `config/local.php` for local overrides
- **Auto-directory Creation**: Automatically creates required directories (uploads, backups, logs)
- **Configurable Thresholds**: Expiry warnings and stock thresholds are adjustable

## API Endpoints

The application provides simple JSON API endpoints:
- `GET /public/index.php?action=api_foods` - Returns all foods as JSON
- `GET /public/index.php?action=api_ingredients` - Returns ingredients with total quantities as JSON
- `GET /public/index.php?action=api_ingredient_locations` - Returns ingredients with location breakdown as JSON
- `POST /public/index.php?action=update_ingredient_location` - Update quantity for a specific ingredient location

## Multi-Location Ingredient Storage

### Architecture Pattern
The application uses a master-detail pattern for ingredient storage:
- **ingredients** table: Contains master ingredient information (name, category, supplier, cost, etc.)
- **ingredient_locations** table: Contains location-specific quantities and notes
- Unique constraint ensures one record per ingredient-location pair

### Working with Multi-Location Data

#### Creating Ingredients with Locations
```php
$ingredient = new Ingredient($db);
$ingredient->name = "Flour";
$ingredient->category = "Baking";
$ingredient->unit = "kg";

// Set multiple locations
$ingredient->locations = [
    ['location' => 'Pantry', 'quantity' => 5.0, 'notes' => 'Main storage'],
    ['location' => 'Kitchen Counter', 'quantity' => 1.0, 'notes' => 'Daily use']
];

$ingredient->create(); // Uses transaction to create both records
```

#### Updating Location Quantities
```php
$ingredient = new Ingredient($db);
$ingredient->id = 1;
$ingredient->updateLocationQuantity('Pantry', 3.5); // Update specific location
```

#### Database Views for Queries
- Use `ingredient_totals` view for summary data with total quantities
- Use `ingredient_location_details` view for detailed location breakdown
- Use `low_stock_ingredients` view for items below threshold (total quantity)

## Key Development Patterns

### Adding New Features
1. **Models**: Extend existing models or create new ones in `src/models/`
2. **Controllers**: Add new methods to `InventoryController.php`
3. **Views**: Create corresponding view files in `src/views/`
4. **Routing**: Update the switch statement in `public/index.php`

### Database Schema Changes
1. Update `src/database/schema.sql` with new table structures (SQLite syntax)
2. Modify corresponding model classes to handle new fields
3. The `Database::initializeDatabase()` method can recreate the entire schema
4. Note: SQLite has different syntax for some operations compared to MySQL (e.g., AUTO_INCREMENT, data types)

### Configuration Changes
- Modify `config/config.php` for application-wide settings
- Use `config/local.php` for environment-specific overrides (not tracked in git)
- Alert thresholds and categories are easily configurable through arrays

### Model Development
- Follow the established pattern: extend models with public properties matching database columns
- Implement standard CRUD operations: `create()`, `read()`, `readOne()`, `update()`, `delete()`
- Add domain-specific methods for business logic (like expiry checking, stock alerts)
- **Ingredient Model Specifics**: 
  - Supports multi-location storage through `$locations` array property
  - Includes location management methods: `addLocation()`, `updateLocationQuantity()`, `removeLocation()`
  - Use transactions for create/update operations involving multiple tables

## Development Environment Notes

### PHP Requirements
- PHP 7.4+ required
- PDO MySQL extension must be enabled
- Error reporting enabled in development mode

### Database Requirements
- SQLite 3.0+ (included with most PHP installations)
- PDO SQLite extension enabled in PHP
- UTF-8 character set for proper Unicode support
- Database auto-initialization available through `Database::initializeDatabase()`
- Database file automatically created in `database/` directory

### File Structure Conventions
- Public assets served from document root: `public/`
- Application source code: `src/`
- Configuration files: `config/`
- SQLite database: `database/`
- Auto-generated directories: `uploads/`, `backups/`, `logs/`

### Security Considerations
- PDO prepared statements used throughout for SQL injection prevention
- Session-based architecture (though authentication not implemented)
- File upload directory outside web root
- SQLite database file should have proper file permissions (not web-accessible)
- Error reporting should be disabled in production

## SQLite Configuration Notes

### Database Connection
The application is configured to use SQLite instead of MySQL. Key configuration points:
- Database file path: `database/food_inventory.db` (relative to project root)
- No database server required - SQLite is file-based
- The `Database.php` class should use `sqlite:` PDO DSN instead of `mysql:`
- Connection string format: `sqlite:database/food_inventory.db`

### Schema Differences
When working with SQLite vs MySQL schemas:
- Use `INTEGER PRIMARY KEY` instead of `INT AUTO_INCREMENT PRIMARY KEY`
- `AUTOINCREMENT` keyword available but not always needed
- No `ENUM` type - use `CHECK` constraints or `TEXT` with validation
- Date functions differ: use `date('now')` instead of `NOW()` or `CURDATE()`
- `DATEDIFF()` function not available - use `julianday()` for date arithmetic

## Customization Points

### Alert Thresholds
Modify in `config/config.php`:
```php
define('EXPIRY_WARNING_DAYS', 7);      // Days before expiry to show warnings
define('LOW_STOCK_THRESHOLD', 10);      // Quantity threshold for low stock
define('CRITICAL_STOCK_THRESHOLD', 5);  // Critical stock level
```

### Categories and Units
Update the arrays in `config/config.php`:
- `$food_categories`: Available food categories
- `$ingredient_categories`: Available ingredient categories  
- `$storage_locations`: Storage location options
- `$units`: Available measurement units

### Sample Data
The schema includes sample data for testing. Remove the INSERT statements in `schema.sql` for production deployment.