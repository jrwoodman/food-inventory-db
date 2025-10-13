# 🍽️ Food & Ingredient Inventory Management System

A comprehensive PHP-based web application for managing food and ingredient inventory with user authentication, expiry tracking, low stock alerts, purchase location management, and a responsive web interface.

## ✨ Features

### 🔐 User Management
- **User Authentication**: Secure login/logout with session management
- **Role-based Access Control**: Admin, User, and Viewer roles
- **User Registration**: Admin can create new user accounts
- **Profile Management**: Users can update their profiles and passwords
- **Session Security**: Automatic session cleanup and security features

### 🏪 Store Management
- **Purchase Location Tracking**: Track where items were purchased
- **Store Database**: Pre-populated with common stores (Walmart, Target, Kroger, etc.)
- **Store CRUD**: Admins can add, edit, activate/deactivate stores
- **Dropdown Integration**: Stores populate purchase location dropdowns

### 📦 Inventory Management
- **Food Items**: Track food with expiry dates, quantities, and storage locations
- **Ingredients**: Manage ingredients with multi-location storage support
- **Purchase Tracking**: Record purchase date and location for all items
- **Expiry Alerts**: Visual alerts for items expiring within 7 days
- **Low Stock Alerts**: Automatic notifications for low inventory levels

### 💻 User Interface
- **Responsive Design**: Mobile-friendly interface
- **Dark/Light Theme**: Toggle between themes with persistent preference
- **Modern UI**: Clean, intuitive design with card-based layout
- **Real-time Updates**: Dynamic content updates and alerts

## 🚀 Quick Start

### Requirements
- PHP 7.4+ with SQLite extension
- Web server (Apache, Nginx, or built-in PHP server)
- SQLite 3.0+

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/jrwoodman/food-inventory-db.git
   cd food-inventory-db
   ```

2. **Set up the database:**
   ```bash
   sqlite3 database/food_inventory.db < src/database/schema.sql
   ```

3. **Set file permissions:**
   ```bash
   chmod 755 database/ uploads/ backups/ logs/
   chmod 664 database/food_inventory.db
   ```

4. **Start the development server:**
   ```bash
   ./start_server.sh
   ```
   Or manually:
   ```bash
   php -S localhost:8000 -t public/
   ```

5. **Access the application:**
   - URL: `http://localhost:8000`
   - Default credentials: `admin` / `admin123`

## Technology Stack

- **Backend**: PHP 8.4+ (compatible with 7.4+)
- **Database**: SQLite 3.0+ (file-based, portable)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Modern CSS Grid and Flexbox with theme support
- **Authentication**: Custom session-based authentication
- **Database ORM**: Custom PDO-based models

## Project Structure

```
food-inventory-db/
├── public/
│   └── index.php              # Main application entry point
├── src/
│   ├── controllers/
│   │   └── InventoryController.php
│   ├── models/
│   │   ├── Food.php
│   │   └── Ingredient.php
│   ├── views/
│   │   ├── dashboard.php
│   │   ├── add_food.php
│   │   └── add_ingredient.php
│   └── database/
│       ├── Database.php       # Database connection class
│       └── schema.sql         # Database schema
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── js/
│       └── app.js             # JavaScript functionality
├── config/
│   └── config.php             # Application configuration
└── README.md
```

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache, Nginx, or PHP built-in server)

### Setup Steps

1. **Clone or Download**: Get the project files
   ```bash
   git clone <repository-url>
   cd food-inventory-db
   ```

2. **Database Setup**:
   - Create a MySQL database named `food_inventory_db`
   - Import the schema: `mysql -u root -p food_inventory_db < src/database/schema.sql`
   - Or use the auto-initialization feature in the Database class

3. **Configuration**:
   - Edit `config/config.php` with your database credentials
   - Adjust timezone and other settings as needed

4. **Web Server**:
   - **Apache/Nginx**: Point document root to the `public/` directory
   - **PHP Built-in Server**: 
     ```bash
     php -S localhost:8000 -t public/
     ```

5. **File Permissions**: Ensure write permissions for uploads and logs directories

### Database Configuration

Update the database settings in `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'food_inventory_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

## Usage

1. **Access the Application**: Navigate to your web server URL
2. **Dashboard**: View all inventory items and alerts
3. **Add Items**: Use the "Add Food" and "Add Ingredient" buttons
4. **Manage Inventory**: Edit or delete items using table actions
5. **Monitor Alerts**: Keep track of expiring foods and low stock ingredients

## 👥 User Roles

### Admin
- Full system access
- User management (create, edit, delete users)
- Store management (add, edit, activate/deactivate stores)
- All inventory operations
- System configuration

### User
- Add, edit, and delete their own inventory items
- View all inventory (if configured)
- Profile management
- Standard inventory operations

### Viewer
- Read-only access to inventory
- Profile viewing only
- No edit/delete permissions

### Key Features Usage

- **Purchase Tracking**: Record where and when items were purchased
- **Expiry Tracking**: Set expiry dates for foods to receive timely warnings
- **Stock Management**: Monitor ingredient quantities with automatic low-stock alerts  
- **Multi-location Storage**: Track ingredients across multiple storage locations
- **Store Management**: Maintain database of purchase locations
- **User Authentication**: Secure access with role-based permissions

## Database Schema

### Tables

- **foods**: Main food items inventory
- **ingredients**: Cooking ingredients inventory  
- **categories**: Predefined categories for organization
- **recipes**: Recipe management (future expansion)
- **food_ingredients**: Recipe-ingredient relationships

### Key Views

- **expiring_foods**: Items expiring within 7 days
- **low_stock_ingredients**: Ingredients below threshold
- **inventory_summary**: Overview statistics

## API Endpoints

- `GET /public/index.php?action=api_foods` - Returns all foods as JSON
- `GET /public/index.php?action=api_ingredients` - Returns ingredients with quantities
- `GET /public/index.php?action=api_ingredient_locations` - Ingredient location breakdown
- `POST /public/index.php?action=update_ingredient_location` - Update ingredient quantities

## Customization

### Thresholds

Modify alert thresholds in `config/config.php`:

```php
define('EXPIRY_WARNING_DAYS', 7);      // Expiry warning days
define('LOW_STOCK_THRESHOLD', 10);     // Low stock threshold
define('CRITICAL_STOCK_THRESHOLD', 5); // Critical stock threshold
```

### Categories and Locations

Update the arrays in `config/config.php` to customize dropdown options:

```php
$food_categories = ['Fruits', 'Vegetables', ...];
$storage_locations = ['Refrigerator', 'Freezer', ...];
```

## Development

### Adding New Features

1. Create new models in `src/models/`
2. Add controller methods in `src/controllers/InventoryController.php`
3. Create views in `src/views/`
4. Update routing in `public/index.php`

### Database Changes

1. Update `src/database/schema.sql`
2. Modify model classes as needed
3. Test with sample data

## Troubleshooting

### Common Issues

1. **Database Connection**: Verify credentials in config.php
2. **File Permissions**: Ensure uploads/ and logs/ directories are writable
3. **PHP Version**: Requires PHP 7.4+
4. **Missing Extensions**: Ensure PDO MySQL extension is installed

### Debug Mode

Enable detailed error reporting in `config/config.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Future Enhancements

- Recipe management integration
- Barcode scanning support
- Mobile app companion
- Email notifications for expiring items
- Export/import functionality
- Advanced reporting and analytics
- Multi-user support with authentication

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes and test thoroughly
4. Submit a pull request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For questions or issues, please:
- Check the troubleshooting section
- Review the code documentation
- Create an issue with detailed information