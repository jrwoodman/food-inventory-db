# 🎉 v0.9.0-beta - Initial Beta Release

This is the first beta release of the **Food & Ingredient Inventory Management System** - a comprehensive web application for tracking food items, ingredients, and their locations with expiry tracking, low stock alerts, and multi-user group collaboration.

## 🌟 Highlights

### Inventory Management
- Track **food items** with quantities, locations, expiry dates, and purchase information
- Manage **ingredients** with multi-location storage support
- Automatic **expiry alerts** and **low stock warnings**
- **Purchase history tracking** for all items

### Bulk Operations
- **Multi-search widget**: Search multiple items at once using comma-separated values
- **Bulk update tool**: Edit multiple items simultaneously with smart dropdown menus
- Purchase dates default to today for quick updates

### Group Collaboration
- Create and manage multiple inventory **groups**
- Three role types: **owner**, **admin**, and **member**
- **Admin group filtering**: View specific groups or all groups at once
- Set **default groups** for faster data entry

### User Management
- Multi-user support with **session management**
- Role-based access control (**admin**, **user**, **viewer**)
- View and revoke active sessions
- Secure authentication and password management

### Additional Features
- **Meal tracking tool** with smart quantity deduction
- **Store and location management**
- **Dark theme** responsive interface
- **Mobile-friendly** design

## 🚀 Quick Start

```bash
# Start development server
php -S localhost:8000 -t public/

# Initialize database
sqlite3 database/food_inventory.db < src/database/schema.sql
```

Then navigate to `http://localhost:8000` and register your first user!

## 📋 Requirements

- PHP 7.4 or higher
- SQLite 3.0+ with PDO extension
- Web server (Apache, Nginx, or PHP built-in server)

## 🐛 Known Issues

- Ingredient quantity updates in bulk edit don't modify location-specific quantities
- Some forms may need additional client-side validation
- API endpoints are basic and could be expanded

## 📖 Documentation

See [RELEASE_NOTES.md](RELEASE_NOTES.md) for complete details and [WARP.md](WARP.md) for development documentation.

## 🔮 What's Next?

- Recipe management
- Barcode scanning
- Shopping list generation
- Reports and analytics
- Import/export functionality
- Email notifications

---

**Full Changelog**: Initial Release
