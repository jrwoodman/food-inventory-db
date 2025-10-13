#!/bin/bash

# Food Inventory System Server Startup Script
echo "🚀 Starting Food Inventory System Server..."
echo "==========================================="

# Check if we're in the right directory
if [ ! -f "public/index.php" ]; then
    echo "❌ Error: Please run this script from the food-inventory-db directory"
    exit 1
fi

# Check if database exists
if [ ! -f "database/food_inventory.db" ]; then
    echo "❌ Error: Database not found. Please run: sqlite3 database/food_inventory.db < src/database/schema.sql"
    exit 1
fi

# Check PHP availability
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP is not installed or not in PATH"
    echo "Please install PHP 7.4+ with the following extensions:"
    echo "- pdo, pdo_sqlite, json, session"
    exit 1
fi

# Get PHP version
PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1-2)
echo "📋 PHP Version: $PHP_VERSION"

# Check PHP extensions
echo "🔍 Checking PHP extensions..."
REQUIRED_EXTENSIONS=("pdo" "json" "session")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        echo "   ✅ $ext"
    else
        echo "   ❌ $ext (missing)"
        MISSING_EXTENSIONS+=("$ext")
    fi
done

# Check SQLite specifically
if php -r "new PDO('sqlite::memory:');" 2>/dev/null; then
    echo "   ✅ pdo_sqlite"
else
    echo "   ❌ pdo_sqlite (missing)"
    MISSING_EXTENSIONS+=("pdo_sqlite")
fi

if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
    echo ""
    echo "❌ Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
    echo "Please install the missing extensions and try again."
    echo ""
    echo "On Manjaro/Arch Linux, try:"
    echo "  sudo pacman -S php php-sqlite"
    echo ""
    echo "On Ubuntu/Debian, try:"
    echo "  sudo apt install php php-sqlite3 php-json"
    echo ""
    exit 1
fi

# Set permissions
echo "🔧 Setting file permissions..."
chmod 755 database/ uploads/ backups/ logs/ 2>/dev/null || true
chmod 664 database/food_inventory.db 2>/dev/null || true

# Start server
echo ""
echo "🌟 Starting PHP development server..."
echo "📱 Access your application at: http://localhost:8000"
echo "🔐 Default login: admin / admin123"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Try to start PHP server with error handling
if php -S localhost:8000 -t public/ 2>&1; then
    echo "Server stopped."
else
    echo "❌ Failed to start PHP server."
    echo ""
    echo "🔧 Alternative solutions:"
    echo "1. Try using a different port:"
    echo "   php -S localhost:8080 -t public/"
    echo ""
    echo "2. Use Docker if available:"
    echo "   docker run -p 8000:80 -v \$(pwd):/var/www/html php:8.1-apache"
    echo ""
    echo "3. Install PHP properly:"
    echo "   sudo pacman -S php php-sqlite  # Manjaro/Arch"
    echo "   sudo apt install php php-sqlite3  # Ubuntu/Debian"
    echo ""
    echo "4. Use Python simple server (limited functionality):"
    echo "   python3 -m http.server 8000 --directory public/"
fi