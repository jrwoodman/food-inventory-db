# Food & Ingredient Inventory Database

A comprehensive PHP-based web application for managing food and ingredient inventory with a graphical user interface.

## Features

- **Food Inventory Management**: Track food items with expiry dates, quantities, and storage locations
- **Ingredient Inventory Management**: Manage cooking ingredients with supplier information and cost tracking
- **Expiry Alerts**: Visual warnings for items expiring within 7 days
- **Low Stock Alerts**: Notifications when ingredient quantities fall below threshold
- **Responsive Design**: Mobile-friendly interface that works on all devices
- **Interactive Tables**: Sortable inventory tables with intuitive controls
- **Form Validation**: Client-side validation for data integrity
- **Sample Data**: Pre-populated with example items for immediate testing

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Modern CSS Grid and Flexbox
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

### Key Features Usage

- **Expiry Tracking**: Set expiry dates for foods to receive timely warnings
- **Stock Management**: Monitor ingredient quantities with automatic low-stock alerts  
- **Categories**: Organize items using predefined categories
- **Locations**: Track where items are stored (Refrigerator, Pantry, etc.)
- **Notes**: Add custom notes for any special information

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

- `GET /public/index.php?action=api_foods` - Get all foods as JSON
- `GET /public/index.php?action=api_ingredients` - Get all ingredients as JSON

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