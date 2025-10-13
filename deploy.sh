#!/bin/bash

# Food Inventory System - Production Deployment Script
echo "🚀 Food Inventory System - Production Deployment"
echo "================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if we're in the right directory
if [ ! -f "public/index.php" ]; then
    print_error "Please run this script from the food-inventory-db directory"
    exit 1
fi

echo "🔍 Checking environment..."

# Check if PHP is available
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed or not in PATH"
    echo "Please install PHP 7.4+ with the following extensions:"
    echo "  - Ubuntu/Debian: sudo apt install php php-sqlite3 php-json"
    echo "  - CentOS/RHEL: sudo yum install php php-pdo php-sqlite"
    exit 1
fi

PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1-2)
print_status "PHP Version: $PHP_VERSION"

# Check PHP extensions
echo "🔍 Checking PHP extensions..."
REQUIRED_EXTENSIONS=("pdo" "json" "session")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        print_status "$ext extension available"
    else
        print_error "$ext extension missing"
        MISSING_EXTENSIONS+=("$ext")
    fi
done

# Check SQLite specifically
if php -r "new PDO('sqlite::memory:');" 2>/dev/null; then
    print_status "PDO SQLite extension available"
else
    print_error "PDO SQLite extension missing"
    MISSING_EXTENSIONS+=("pdo_sqlite")
fi

if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
    print_error "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
    echo ""
    echo "Installation commands:"
    echo "  Ubuntu/Debian: sudo apt install php-sqlite3 php-json"
    echo "  CentOS/RHEL: sudo yum install php-pdo php-sqlite"
    echo "  Manjaro/Arch: sudo pacman -S php php-sqlite"
    exit 1
fi

# Create required directories
echo "📁 Creating required directories..."
mkdir -p database uploads backups logs
print_status "Directories created"

# Set up database
echo "🗄️ Setting up database..."
if [ ! -f "database/food_inventory.db" ]; then
    if [ -f "src/database/schema.sql" ]; then
        sqlite3 database/food_inventory.db < src/database/schema.sql
        print_status "Database initialized from schema"
    else
        print_error "Schema file not found: src/database/schema.sql"
        exit 1
    fi
else
    print_warning "Database already exists, skipping initialization"
fi

# Set file permissions
echo "🔧 Setting file permissions..."
chmod 755 database/ uploads/ backups/ logs/ 2>/dev/null || true
chmod 664 database/food_inventory.db 2>/dev/null || true
print_status "File permissions set"

# Check if config exists
echo "⚙️ Checking configuration..."
if [ -f "config/config.php" ]; then
    print_status "Configuration file found"
else
    print_error "Configuration file missing: config/config.php"
    exit 1
fi

# Test database connection
echo "🧪 Testing database connection..."
if php -r "
require_once 'config/config.php';
require_once 'src/database/Database.php';
\$db = new Database();
\$conn = \$db->getConnection();
if (\$conn) {
    echo 'Database connection successful';
} else {
    echo 'Database connection failed';
    exit(1);
}
" 2>/dev/null; then
    print_status "Database connection test passed"
else
    print_error "Database connection test failed"
    exit 1
fi

# Verify admin user exists
echo "👤 Checking admin user..."
ADMIN_COUNT=$(sqlite3 database/food_inventory.db "SELECT COUNT(*) FROM users WHERE username='admin' AND role='admin';")
if [ "$ADMIN_COUNT" -eq 1 ]; then
    print_status "Admin user found"
else
    print_warning "Admin user not found or not properly configured"
    echo "You may need to create an admin user manually"
fi

# Production security recommendations
echo ""
echo "🔒 Production Security Checklist:"
echo "=================================="
echo "📋 TODO - Manual Steps Required:"
echo ""
echo "1. 🔐 Change default admin password (admin/admin123)"
echo "2. 🌐 Configure web server (Apache/Nginx) to serve from public/ directory"
echo "3. 🔒 Enable HTTPS/SSL for production"
echo "4. 🚫 Ensure database/ directory is not web-accessible"
echo "5. 📝 Set up log rotation for logs/ directory"
echo "6. 🔄 Configure automated backups for database/"
echo "7. 🔧 Review config/config.php for production settings"
echo "8. 📊 Set up monitoring for disk usage and performance"
echo ""

# Test with built-in server
echo "🌟 Deployment Complete!"
echo "======================="
echo ""
echo "📱 Quick Test (Development Server):"
echo "   php -S localhost:8000 -t public/"
echo "   Then visit: http://localhost:8000"
echo ""
echo "🔐 Default Login Credentials:"
echo "   Username: admin"
echo "   Password: admin123"
echo ""
echo "⚠️  IMPORTANT: Change the default password immediately!"
echo ""

# Web server configuration examples
echo "🌐 Web Server Configuration Examples:"
echo "====================================="
echo ""
echo "Apache (.htaccess already included):"
echo "  DocumentRoot /path/to/food-inventory-db/public"
echo ""
echo "Nginx:"
echo "  root /path/to/food-inventory-db/public;"
echo "  index index.php;"
echo "  location ~ \.php$ {"
echo "    fastcgi_pass php-fpm;"
echo "    fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;"
echo "    include fastcgi_params;"
echo "  }"
echo ""

print_status "Deployment script completed successfully!"
echo "Ready for production use! 🎉"