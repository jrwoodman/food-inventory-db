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
- **Dark Theme**: Modern dark theme with Web 2.0 styling
- **Clean UI**: Intuitive design with card-based layout
- **Real-time Updates**: Dynamic content updates and alerts

## 🚀 Quick Start

### Requirements
- PHP 7.4+ with PDO SQLite extension
- Web server (Apache, Nginx) for production
- SQLite 3.0+

### Development Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/jrwoodman/food-inventory-db.git
   cd food-inventory-db
   ```

2. **Set up the database:**
   ```bash
   mkdir -p database backups uploads logs
   sqlite3 database/food_inventory.db < src/database/schema.sql
   ```

3. **Set file permissions:**
   ```bash
   chmod 755 database/ uploads/ backups/ logs/
   chmod 664 database/food_inventory.db
   ```

4. **Start the development server:**
   ```bash
   php -S localhost:8000 -t public/
   ```

5. **Access the application:**
   - URL: `http://localhost:8000`
   - Default credentials: `admin` / `admin123`
   - **Important**: Change the admin password after first login!

### Production Installation

#### Apache Setup

1. **Clone to web server directory:**
   ```bash
   cd /var/www
   git clone https://github.com/jrwoodman/food-inventory-db.git
   cd food-inventory-db
   ```

2. **Set up database and permissions:**
   ```bash
   mkdir -p database backups uploads logs
   sqlite3 database/food_inventory.db < src/database/schema.sql
   
   # Set ownership to web server user
   chown -R www-data:www-data database/ backups/ uploads/ logs/
   chmod 755 database/ backups/ uploads/ logs/
   chmod 664 database/food_inventory.db
   ```

3. **Configure Apache virtual host:**
   Create `/etc/apache2/sites-available/food-inventory.conf`:
   ```apache
   <VirtualHost *:80>
       ServerName food.example.com
       DocumentRoot /var/www/food-inventory-db/public
       
       <Directory /var/www/food-inventory-db/public>
           AllowOverride All
           Require all granted
           
           # Redirect all requests to index.php
           <IfModule mod_rewrite.c>
               RewriteEngine On
               RewriteCond %{REQUEST_FILENAME} !-f
               RewriteCond %{REQUEST_FILENAME} !-d
               RewriteRule ^ index.php [L]
           </IfModule>
       </Directory>
       
       # Deny access to sensitive directories
       <Directory /var/www/food-inventory-db/config>
           Require all denied
       </Directory>
       <Directory /var/www/food-inventory-db/src>
           Require all denied
       </Directory>
       <Directory /var/www/food-inventory-db/database>
           Require all denied
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/food-inventory-error.log
       CustomLog ${APACHE_LOG_DIR}/food-inventory-access.log combined
   </VirtualHost>
   ```

4. **Enable site and modules:**
   ```bash
   a2enmod rewrite
   a2ensite food-inventory
   systemctl restart apache2
   ```

#### Nginx Setup

1. **Configure Nginx:**
   Create `/etc/nginx/sites-available/food-inventory`:
   ```nginx
   server {
       listen 80;
       server_name food.example.com;
       root /var/www/food-inventory-db/public;
       index index.php;
       
       # Deny access to sensitive files
       location ~ ^/(config|src|database|backups|logs)/ {
           deny all;
           return 404;
       }
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
       
       # Deny access to .htaccess files
       location ~ /\.ht {
           deny all;
       }
   }
   ```

2. **Enable site:**
   ```bash
   ln -s /etc/nginx/sites-available/food-inventory /etc/nginx/sites-enabled/
   nginx -t
   systemctl restart nginx
   ```

#### SSL/HTTPS Setup (Recommended)

Use Let's Encrypt for free SSL certificates:
```bash
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

sudo certbot --apache -d food.example.com        # For Apache
# OR
sudo certbot --nginx -d food.example.com         # For Nginx
```

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ (tested on PHP 8.2+)
- **Database**: SQLite 3.0+ (file-based, zero-configuration, portable)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Modern CSS with dark theme and responsive design
- **Authentication**: Secure session-based authentication with password hashing
- **Database Access**: Custom PDO-based models with prepared statements
- **Web Server**: Apache 2.4+ or Nginx 1.18+

### Why SQLite?

- **Zero Configuration**: No database server to install or configure
- **Portable**: Single file database can be easily backed up or moved
- **Reliable**: ACID-compliant, tested extensively
- **Perfect for Small Teams**: Handles hundreds of concurrent users
- **Low Maintenance**: No database server to maintain or update

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

## 📝 Configuration

### Database Location

The SQLite database file is located at:
```
database/food_inventory.db
```

No additional configuration needed - it works out of the box!

### Application Settings

Edit `config/config.php` to customize:
- Session timeout duration
- Timezone settings
- Alert thresholds (expiry warnings, low stock levels)
- File upload limits

### Security Considerations

1. **Change Default Password**: The default admin password is `admin123` - change it immediately!
2. **Database Security**: Ensure the `database/` directory is not web-accessible
3. **File Permissions**: Keep database file permissions at 664 (readable by web server only)
4. **HTTPS**: Always use HTTPS in production
5. **Backups**: Regularly backup the `database/food_inventory.db` file

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

1. **Database Locked Error**: 
   - SQLite databases can lock during writes
   - Check file permissions (must be writable by web server)
   - Ensure only one process is writing at a time

2. **File Permissions**: 
   - Database directory must be writable: `chmod 755 database/`
   - Database file must be writable: `chmod 664 database/food_inventory.db`
   - Uploads, backups, logs directories must be writable

3. **PHP Version**: 
   - Requires PHP 7.4+
   - Check version: `php -v`

4. **Missing Extensions**: 
   - Ensure PDO SQLite extension is installed
   - Check: `php -m | grep sqlite`
   - Install if missing: `sudo apt install php-sqlite3` (Ubuntu/Debian)

5. **404 Errors**:
   - Verify web server document root points to `public/` directory
   - Check Apache/Nginx rewrite rules are configured

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

## 📄 License

This project is free software licensed under the GNU General Public License v3.0 (GPLv3).

You are free to:
- Use this software for any purpose
- Study how it works and modify it
- Redistribute copies
- Distribute modified versions

Under the following terms:
- You must include the license and copyright notice
- You must state changes made to the code
- You must make source code available when distributing
- Modified versions must also be licensed under GPLv3

See the [LICENSE](LICENSE) file for full details.

## Support

For questions or issues, please:
- Check the troubleshooting section
- Review the code documentation
- Create an issue with detailed information