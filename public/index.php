<?php
// Start output buffering to prevent any header issues
ob_start();

// Load configuration first
require_once '../config/config.php';

// Start session before any output
session_start();

// Load application files
require_once '../src/database/Database.php';
require_once '../src/models/Food.php';
require_once '../src/models/Ingredient.php';
require_once '../src/models/User.php';
require_once '../src/models/Store.php';
require_once '../src/models/StoreChain.php';
require_once '../src/models/StoreLocation.php';
require_once '../src/models/Location.php';
require_once '../src/models/Group.php';
require_once '../src/models/Unit.php';
require_once '../src/models/Category.php';
require_once '../src/auth/Auth.php';
require_once '../src/controllers/InventoryController.php';
require_once '../src/controllers/UserController.php';

// Initialize database and auth
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Clean up expired sessions
$auth->cleanupExpiredSessions();

// Simple routing
$action = $_GET['action'] ?? 'dashboard';

// Check if user exists - if not, redirect to setup
$user_model = new User($db);
if ($user_model->getUsersCount() == 0 && $action !== 'register') {
    $action = 'register';
}

// Routes that don't require authentication
$public_routes = ['login', 'register'];

// Route to appropriate controller
if (in_array($action, ['login', 'logout', 'register', 'users', 'edit_user', 'delete_user', 'profile', 'revoke_session', 'revoke_all_sessions', 'access_denied', 'user_management'])) {
    $controller = new UserController();
} else {
    $controller = new InventoryController();
}

switch ($action) {
    // Authentication routes
    case 'login':
        $controller->login();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'register':
        $controller->register();
        break;
    case 'access_denied':
        $controller->accessDenied();
        break;
        
    // User management routes
    case 'users':
        $controller->users();
        break;
    case 'edit_user':
        $controller->editUser();
        break;
    case 'delete_user':
        $controller->deleteUser();
        break;
    case 'profile':
        $controller->profile();
        break;
    case 'revoke_session':
        $controller->revokeSession();
        break;
    case 'revoke_all_sessions':
        $controller->revokeAllSessions();
        break;
        
    // Inventory routes
    case 'dashboard':
        $controller->dashboard();
        break;
    case 'add_food':
        $controller->addFood();
        break;
    case 'edit_food':
        $controller->editFood();
        break;
    case 'delete_food':
        $controller->deleteFood();
        break;
    case 'add_ingredient':
        $controller->addIngredient();
        break;
    case 'edit_ingredient':
        $controller->editIngredient();
        break;
    case 'delete_ingredient':
        $controller->deleteIngredient();
        break;
    case 'update_ingredient_location':
        $controller->updateIngredientLocation();
        break;
    case 'api_foods':
        $controller->getFoodsJson();
        break;
    case 'api_ingredients':
        $controller->getIngredientsJson();
        break;
    case 'api_ingredient_locations':
        $controller->getIngredientLocationsJson();
        break;
    case 'check_food_duplicate':
        $controller->checkFoodDuplicate();
        break;
    case 'check_ingredient_duplicate':
        $controller->checkIngredientDuplicate();
        break;
        
    // Store management routes
    case 'manage_stores':
        $controller->manageStores();
        break;
    case 'add_store':
        $controller->addStore();
        break;
    case 'edit_store':
        $controller->editStore();
        break;
    case 'delete_store':
        $controller->deleteStore();
        break;
    case 'toggle_store_status':
        $controller->toggleStoreStatus();
        break;
    case 'add_store_location':
        $controller->addStoreLocation();
        break;
    case 'edit_store_location':
        $controller->editStoreLocation();
        break;
    case 'delete_store_location':
        $controller->deleteStoreLocation();
        break;
    case 'toggle_store_location_status':
        $controller->toggleStoreLocationStatus();
        break;
        
    // Location management routes
    case 'manage_locations':
        $controller->manageLocations();
        break;
    case 'add_location':
        $controller->addLocation();
        break;
    case 'edit_location':
        $controller->editLocation();
        break;
    case 'delete_location':
        $controller->deleteLocation();
        break;
    case 'toggle_location_status':
        $controller->toggleLocationStatus();
        break;
        
    // System settings (units and categories)
    case 'system_settings':
        $controller->systemSettings();
        break;
    case 'add_unit':
        $controller->addUnit();
        break;
    case 'edit_unit':
        $controller->editUnit();
        break;
    case 'delete_unit':
        $controller->deleteUnit();
        break;
    case 'toggle_unit_status':
        $controller->toggleUnitStatus();
        break;
    case 'add_category':
        $controller->addCategory();
        break;
    case 'edit_category':
        $controller->editCategory();
        break;
    case 'delete_category':
        $controller->deleteCategory();
        break;
        
    // Meal tracking routes
    case 'track_meal':
        $controller->trackMeal();
        break;
    case 'update_meal_items':
        $controller->updateMealItems();
        break;
        
    // User and group management routes
    case 'user_management':
        $controller->userManagement();
        break;
    case 'list_groups':
        $controller->listGroups();
        break;
    case 'create_group':
        $controller->createGroup();
        break;
    case 'edit_group':
        $controller->editGroup();
        break;
    case 'delete_group':
        $controller->deleteGroup();
        break;
    case 'manage_group_members':
        $controller->manageGroupMembers();
        break;
    case 'add_group_member':
        $controller->addGroupMember();
        break;
    case 'update_group_member_role':
        $controller->updateGroupMemberRole();
        break;
    case 'remove_group_member':
        $controller->removeGroupMember();
        break;
    case 'set_default_group':
        $controller->setDefaultGroup();
        break;
    
    // Bulk operations routes
    case 'bulk_search':
        $controller->bulkSearch();
        break;
    case 'bulk_update':
        $controller->bulkUpdate();
        break;
        
    default:
        // Check if user is logged in, otherwise redirect to login
        if (!$auth->isLoggedIn()) {
            header('Location: index.php?action=login');
            exit();
        }
        $controller->dashboard();
}
?>