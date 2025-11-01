# Changelog

All notable changes to the Food & Ingredient Inventory Management System will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0-rc2] - 2025-11-01

### Added
- **Duplicate Detection & Warnings**
  - API endpoints for checking food and ingredient duplicates
  - Client-side duplicate warning modal on single item add
  - Shows existing item details (category, brand/unit, locations with quantities)
  - User choice to cancel or proceed with quantity update
  - Automatic duplicate handling in bulk add mode

- **Phone Number Formatting**
  - Automatic formatting to (XXX) YYY-ZZZZ format on store location forms
  - Real-time formatting as user types
  - Cursor position preservation during formatting
  - Auto-formats existing phone numbers on page load

### Changed
- **Track Meal Feature**
  - Fixed to properly display location-specific quantities
  - Foods and ingredients now expand to show separate rows per location
  - Each location row displays its specific quantity
  - Updates apply to specific locations only
  - Improved form handling for multi-location updates

### Fixed
- Store location CRUD operations fully implemented
  - Delete and deactivate functionality working correctly
  - Proper redirects with anchor fragments
  - Admin permission checks in place
- Duplicate modal submission loop fixed
  - Proper flag handling to prevent infinite checks
  - Form submits correctly after user confirms update

### Technical Improvements
- Controller methods for duplicate checking (checkFoodDuplicate, checkIngredientDuplicate)
- Location-specific key format for tracking updates (`{id}_{location}`)
- Enhanced JavaScript for form validation and duplicate detection
- Modal styling with dark theme support

## [1.0.0-rc1] - 2025-10-29

### Added
- **Branding Customization**
  - Configurable app title via `APP_TITLE` constant
  - Optional app subtitle displayed below title
  - Optional 32x32px app icon/logo
  - Optional favicon for browser tabs
  - All branding elements configurable in `config/config.php`

- **Navigation & UI Improvements**
  - Unified navigation with all module icons visible across all pages
  - Active module highlighting with border and background
  - Consistent header navigation across all views
  - Gravatar support for user avatars with robohash fallback
  - Larger avatar display (32x32px) in navigation
  - Username display with improved sizing and spacing
  - Settings menu renamed from "System Settings" to "Settings"

- **Settings Organization**
  - Consolidated Settings page for all users
  - Admin users see: Users, Groups, Units, Categories, Stores, Locations tabs
  - Non-admin users see: Groups tab
  - Users & Groups management integrated into Settings
  - Tab state preservation via URL hash fragments

- **Bulk Operations**
  - Bulk add functionality for food items with per-line quantity and expiry dates
  - Bulk add functionality for ingredients with location support
  - Multi-search widget for quick inventory updates
  - CSV-style input parsing (name, quantity, expiry date, location)
  - Duplicate detection with automatic quantity updates

- **Multi-Location Support**
  - Multiple storage locations for single food items
  - Ingredient quantities tracked per location
  - Location-specific notes for ingredients
  - Multi-location entry in single add forms

- **User Experience**
  - Quick search & update widget on dashboard
  - Improved form validation and feedback
  - Better select dropdown theming for dark mode
  - Profile links with user avatar and username

### Changed
- Number input step increment changed from 0.01 to 0.1 for better usability
- Meal tracker now keeps inventory items at zero quantity instead of deleting
- System Settings reorganized into unified Settings page
- Navigation structure simplified and made consistent

### Fixed
- Category nameExists check now uses fetch() instead of rowCount()
- Unit duplicate detection fixed to use fetch() method
- Unit dropdown now correctly uses abbreviation instead of measurement
- Undefined variable $low_stock_foods in bulkSearch method
- Select dropdown contrast issues in dark theme

### Technical Improvements
- Consistent navigation implementation across all views
- URL hash-based tab state management
- Improved database query patterns for existence checks
- Enhanced form handling for multi-location entries
- Transaction support for multi-table operations

### Security
- PDO prepared statements used throughout
- Session-based authentication
- CSRF protection ready (tokens configured)
- Password hashing with PHP password_hash()

## [Unreleased]

### Planned for v1.0.0 Final
- Production environment configuration guide
- Email notification system activation
- API documentation
- User manual and getting started guide
- Database backup automation
- Import/export functionality

---

## Version History

- **1.0.0-rc2** (2025-11-01) - Release Candidate 2
  - Multi-location tracking fixes
  - Duplicate detection with user warnings
  - Phone number auto-formatting
  - Store location management complete
  
- **1.0.0-rc1** (2025-10-29) - Release Candidate 1
  - First release candidate
  - Feature complete for v1.0.0
  - Ready for production testing

---

For more information, see the [README.md](README.md) file.
