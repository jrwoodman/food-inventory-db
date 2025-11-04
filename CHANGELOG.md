# Changelog

All notable changes to the Food & Ingredient Inventory Management System will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0-rc3] - 2025-11-04

### Added
- **Fuzzy Search with Auto-Population**
  - Real-time fuzzy search in add food/ingredient forms (4+ character minimum)
  - Dropdown shows similar existing items as you type
  - Displays item details: category, brand/unit, quantities, locations
  - Auto-populates all fields when selecting from search results
  - Auto-loads existing location data with quantities
  - 300ms debounce for optimal performance
  - Warning message when selecting existing items

- **USDA FoodData Central Integration**
  - Nutrition lookup for foods and ingredients
  - Search modal with FoodData Central database
  - Nutrition facts display with per-100g values
  - Configurable metric/imperial unit conversion
  - Support for Foundation Foods, SR Legacy, and Branded Foods
  - Brand and category context sent for better search results
  - API rate limit detection and user warnings
  - Demo key warning banner
  - Configurable display mode (icon or clickable name)

- **Enhanced Search & Navigation**
  - Quick search widget now searches brands, suppliers, locations
  - Category search added to quick search
  - Real-time duplicate name warnings in add forms
  - Foods sorted alphabetically by name (matching ingredients)
  - Brand column added to foods dashboard table

### Changed
- **Dashboard Improvements**
  - All dashboard widget items now clickable links to edit pages
  - Ingredients table shows expiry date instead of purchase date
  - Cost/Unit column removed from ingredients dashboard
  - Brand column added to foods table
  - Purchase date removed from foods dashboard table

- **UI/UX Refinements**
  - Improved search result styling with lighter text colors
  - Nutrition link styling updated for better visibility
  - Username link in nav bar matches other nav items
  - Gravatar size increased to 40px for better visibility
  - Edit links styled as primary buttons in search results
  - Search results label updated to "item(s) found"

### Fixed
- **Data Quality & Validation**
  - Expiration widget excludes zero-quantity items
  - Invalid/null expiry dates not displayed (prevents 12-31-1969)
  - MM-DD-YYYY dates converted to YYYY-MM-DD in bulk add
  - Undefined POST keys prevented in bulk operations
  - Purchase date defaults to today in single add forms

- **Database & Model Issues**
  - Apostrophe and special character encoding fixed in all models
  - Ingredient location updates save correctly when only locations change
  - Unit column explicitly selected in food search queries
  - Low stock threshold filtering uses explicit REAL casting
  - Expiry date comparison fixed for SQLite date functions

- **Form & Configuration**
  - Application settings constants can be overridden in local.php
  - Constant redefinition warnings prevented
  - Missing "Contains Nuts" checkbox added to bulk add forms
  - Bulk add supplier field properly included
  - Decimal threshold values supported (e.g., 0.2)
  - Syntax errors in controller methods fixed
  - Success/error messages display correctly in single-add mode

### Technical Improvements
- New API endpoints: `search_foods`, `search_ingredients`
- USDAService class for nutrition API integration
- Structured location data returned in search responses
- Enhanced JavaScript for form auto-population
- Improved error handling and propagation
- Better date handling across bulk operations

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

- **1.0.0-rc3** (2025-11-04) - Release Candidate 3
  - Fuzzy search with auto-population
  - USDA nutrition integration
  - Enhanced search capabilities
  - Numerous bug fixes and UI improvements

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
