# Release Notes

## v0.9.0-beta (2025-10-28)

### 🎉 Initial Beta Release

This is the first beta release of the Food & Ingredient Inventory Management System - a comprehensive web application for tracking food items, ingredients, and their locations with expiry tracking, low stock alerts, and multi-user group collaboration.

### ✨ Core Features

#### Inventory Management
- **Food Tracking**: Track food items with quantities, locations, expiry dates, and purchase information
- **Ingredient Management**: Manage ingredients with multi-location storage support
- **Multi-Location Storage**: Store ingredients across multiple locations with individual quantity tracking per location
- **Expiry Tracking**: Automatic alerts for items expiring within 7 days
- **Low Stock Alerts**: Configurable thresholds for ingredient stock warnings
- **Purchase History**: Track purchase dates and locations for all items

#### Search & Bulk Operations
- **Multi-Search Widget**: Comma-delimited search for multiple items simultaneously
- **Bulk Update**: Update multiple items at once with editable forms including:
  - Quantity, unit, category
  - Purchase date and location (dropdown menus)
  - Storage location (for foods)
  - Supplier and cost per unit (for ingredients)
  - Expiry dates
- **Smart Defaults**: Purchase dates automatically default to today's date in bulk updates

#### Group & Collaboration
- **Multi-Group Support**: Create and manage multiple inventory groups
- **Group Roles**: Three role types (owner, admin, member) with appropriate permissions
- **Group Member Management**: Add, remove, and manage member roles
- **Default Group Selection**: Set preferred default group for quick inventory entry
- **Group Filtering**: Admins can filter dashboard by specific group or view all groups
- **Group Indicators**: Group names displayed in tables when viewing all groups

#### User Management
- **Multi-User Support**: Full authentication system with session management
- **User Roles**: Three user types (admin, user, viewer) with granular permissions
- **User Profile Management**: Update profile information and change passwords
- **Session Management**: View active sessions, revoke individual sessions or all other sessions
- **Access Control**: Role-based access control for all features and pages

#### Meal Tracking
- **Meal Tracking Tool**: Search for and update inventory based on meals consumed
- **Quick Deduction**: Easily deduct quantities used in meals
- **Location-Aware**: For ingredients, select which storage location to deduct from
- **Auto-Cleanup**: Automatically removes items when quantity reaches zero

#### Store & Location Management
- **Store Management**: Maintain a list of purchase locations/stores
- **Storage Location Management**: Define and manage physical storage locations
- **Active/Inactive Toggle**: Enable/disable stores and locations without deletion
- **Migration Support**: Safely delete locations by migrating items to another location

#### User Interface
- **Dark Theme**: Modern, eye-friendly dark theme interface
- **Responsive Design**: Mobile-friendly layout that adapts to all screen sizes
- **Intuitive Navigation**: Clear navigation with role-appropriate menu items
- **Visual Indicators**: Color-coded badges for item types, roles, and statuses
- **Smart Forms**: Dropdown menus populated from database for consistent data entry
- **Dashboard Overview**: Quick view of expiring foods and low stock ingredients

### 🔧 Technical Features

#### Architecture
- **MVC Pattern**: Clean separation of models, views, and controllers
- **SQLite Database**: Lightweight, file-based database for easy deployment
- **PDO with Prepared Statements**: Protection against SQL injection
- **Transaction Support**: ACID-compliant operations for data integrity
- **Database Views**: Pre-built views for common queries and reporting

#### Security
- **Password Hashing**: Secure password storage using PHP's password_hash()
- **Session Management**: Secure session handling with expiration
- **CSRF Protection**: Form token validation (where implemented)
- **Role-Based Access**: Granular permission checks on all operations
- **Input Sanitization**: All user input sanitized and validated

#### Data Management
- **Multi-Location Ingredients**: Ingredients can be stored across multiple locations
- **Group Isolation**: Data properly isolated between groups
- **Soft Deletes**: Graceful handling of dependencies and relationships
- **Cascading Operations**: Proper foreign key constraints and cascading deletes

### 📋 Database Schema

- **Users**: User accounts with roles and authentication
- **Groups**: Organizational units for shared inventory
- **User Groups**: Many-to-many relationship with roles
- **Foods**: Individual food items with expiry tracking
- **Ingredients**: Master ingredient data
- **Ingredient Locations**: Quantity tracking per location per ingredient
- **Categories**: Organized categorization for foods and ingredients
- **Stores**: Purchase location tracking
- **Locations**: Physical storage location management
- **User Sessions**: Active session tracking for security

### 🚀 Getting Started

#### Requirements
- PHP 7.4 or higher
- SQLite 3.0+ with PDO extension
- Web server (Apache, Nginx, or PHP built-in server)

#### Quick Setup
```bash
# Start development server
php -S localhost:8000 -t public/

# Initialize database (if needed)
sqlite3 database/food_inventory.db < src/database/schema.sql

# Set proper permissions
chmod 755 uploads/ backups/ logs/ database/
chmod 664 database/food_inventory.db
```

#### First Use
1. Navigate to `http://localhost:8000`
2. Register the first user (automatically becomes admin)
3. Create your first group
4. Start adding inventory items!

### 📝 Configuration

Key configuration options in `config/config.php`:
- **EXPIRY_WARNING_DAYS**: Days before expiry to show warnings (default: 7)
- **LOW_STOCK_THRESHOLD**: Quantity threshold for low stock alerts (default: 10)
- **CRITICAL_STOCK_THRESHOLD**: Critical stock level (default: 5)

### 🐛 Known Issues

- Ingredient quantity updates in bulk edit don't modify location-specific quantities
- Some forms may need additional client-side validation
- API endpoints are basic and could be expanded

### 🔮 Future Enhancements

- Recipe management and tracking
- Barcode scanning support
- Shopping list generation
- Inventory reports and analytics
- Import/export functionality
- Email notifications for expiring items
- Mobile app integration
- Advanced search filters
- Inventory cost tracking

### 🙏 Acknowledgments

Built with modern web technologies and best practices for inventory management.

### 📄 License

See LICENSE file for details.

---

**Full Changelog**: Initial Release
