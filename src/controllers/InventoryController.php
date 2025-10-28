<?php
class InventoryController {
    private $database;
    private $db;
    private $auth;
    private $current_user;

    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->auth = new Auth($this->db);
        
        // Require login for all inventory operations
        $this->auth->requireLogin();
        $this->current_user = $this->auth->getCurrentUser();
    }

    public function dashboard() {
        $food = new Food($this->db);
        $ingredient = new Ingredient($this->db);
        
        // Get dropdown options for the bulk update form
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        $units = Unit::getUnitOptions($this->db, true);
        
        // Get categories from database
        $categories_query = "SELECT DISTINCT name FROM categories ORDER BY name";
        $categories_stmt = $this->db->prepare($categories_query);
        $categories_stmt->execute();
        $categories = [];
        while ($row = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row['name'];
        }
        
        // Filter by current user's groups
        $group_ids = $this->current_user->getGroupIds();
        
        // Get group filter for admins
        $filter_group_id = null;
        $show_all_groups = false;
        
        if ($this->current_user->isAdmin()) {
            // Check if admin has selected a specific group filter or "all groups"
            if (isset($_GET['group_filter'])) {
                if ($_GET['group_filter'] === 'all') {
                    $show_all_groups = true;
                } else {
                    $filter_group_id = intval($_GET['group_filter']);
                }
            } else {
                // Default to user's default group if set
                $filter_group_id = $this->current_user->getDefaultGroupId();
            }
            
            // Get all groups for the filter dropdown
            $group_model = new Group($this->db);
            $all_groups_stmt = $group_model->read();
            $all_groups = [];
            while ($row = $all_groups_stmt->fetch(PDO::FETCH_ASSOC)) {
                $all_groups[] = $row;
            }
        }
        
        if ($this->current_user->isAdmin()) {
            if ($show_all_groups) {
                // Admin viewing all groups - show all items with group names
                $foods = $food->read();
                $ingredients = $ingredient->read();
                $expiring_foods = $food->getExpiringItems(7);
                $low_stock_ingredients = $ingredient->getLowStockItems(10);
                $low_stock_foods = $food->getLowStockItems(10);
            } else if ($filter_group_id) {
                // Admin viewing specific group
                $foods = $food->readByGroups([$filter_group_id]);
                $ingredients = $ingredient->readByGroups([$filter_group_id]);
                $expiring_foods = $food->getExpiringItemsByGroups([$filter_group_id], 7);
                $low_stock_ingredients = $ingredient->getLowStockItemsByGroups([$filter_group_id], 10);
                $low_stock_foods = $food->getLowStockItemsByGroups([$filter_group_id], 10);
            } else {
                // Admin not in any group and no filter selected
                $foods = $food->read();
                $ingredients = $ingredient->read();
                $expiring_foods = $food->getExpiringItems(7);
                $low_stock_ingredients = $ingredient->getLowStockItems(10);
                $low_stock_foods = $food->getLowStockItems(10);
            }
        } else if (!empty($group_ids)) {
            // Regular users see items from their groups
            $foods = $food->readByGroups($group_ids);
            $ingredients = $ingredient->readByGroups($group_ids);
            $expiring_foods = $food->getExpiringItemsByGroups($group_ids, 7);
            $low_stock_ingredients = $ingredient->getLowStockItemsByGroups($group_ids, 10);
            $low_stock_foods = $food->getLowStockItemsByGroups($group_ids, 10);
        } else {
            // User not in any group - show empty results
            $foods = false;
            $ingredients = false;
            $expiring_foods = false;
            $low_stock_ingredients = false;
            $low_stock_foods = false;
        }

        $current_user = $this->current_user;
        include '../src/views/dashboard.php';
    }

    public function addFood() {
        // Check if user can edit
        if (!$this->current_user->canEdit()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        // Get stores, locations, units and categories for dropdowns
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        $units = Unit::getUnitOptions($this->db, true);
        $food_categories = Category::getCategoryOptions($this->db, 'food');
        
        // Get user's groups for group selection
        $user_groups = [];
        $stmt = $this->current_user->getGroups();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_groups[] = $row;
        }
        $default_group_id = $this->current_user->getDefaultGroupId();
        
        if ($_POST) {
            $mode = $_POST['mode'] ?? 'single';
            
            if ($mode === 'bulk' && !empty($_POST['bulk_names'])) {
                // Bulk add mode
                $lines = array_filter(array_map('trim', explode("\n", $_POST['bulk_names'])));
                $success_count = 0;
                $error_count = 0;
                $default_quantity = $_POST['default_quantity'] ?? 1;
                
                foreach ($lines as $line) {
                    // Parse CSV format: Name, Quantity, Expiry Date
                    $parts = array_map('trim', str_getcsv($line));
                    $name = $parts[0] ?? '';
                    $quantity = !empty($parts[1]) ? $parts[1] : $default_quantity;
                    $expiry_date = !empty($parts[2]) ? $parts[2] : null;
                    
                    if (empty($name)) continue; // Skip empty lines
                    
                    // Check if item already exists (case-insensitive) in the same group
                    $group_id = $_POST['group_id'] ?? null;
                    $check_query = "SELECT id, quantity FROM foods WHERE LOWER(name) = LOWER(?) AND group_id = ?";
                    $check_stmt = $this->db->prepare($check_query);
                    $check_stmt->execute([$name, $group_id]);
                    $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existing) {
                        // Update existing item - add to quantity
                        $food = new Food($this->db);
                        $food->id = $existing['id'];
                        if ($food->readOne()) {
                            $food->quantity = $existing['quantity'] + $quantity;
                            // Update expiry date if provided
                            if ($expiry_date) {
                                $food->expiry_date = $expiry_date;
                            }
                            // Update purchase date
                            if (!empty($_POST['purchase_date'])) {
                                $food->purchase_date = $_POST['purchase_date'];
                            }
                            $food->user_id = $this->current_user->id;
                            
                            if ($food->update()) {
                                $success_count++;
                            } else {
                                $error_count++;
                            }
                        }
                    } else {
                        // Create new item
                        $food = new Food($this->db);
                        $food->name = $name;
                        $food->category = $_POST['category'];
                        $food->quantity = $quantity;
                        $food->unit = $_POST['unit'];
                        $food->expiry_date = $expiry_date;
                        $food->purchase_date = $_POST['purchase_date'] ?: null;
                        $food->purchase_location = $_POST['purchase_location'];
                        $food->location = $_POST['location'];
                        $food->notes = $_POST['notes'];
                        $food->user_id = $this->current_user->id;
                        $food->group_id = $group_id;
                        
                        if ($food->create()) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
                
                if ($success_count > 0) {
                    $message = $success_count . ' item(s) added successfully';
                    if ($error_count > 0) {
                        $message .= ' (' . $error_count . ' failed)';
                    }
                    header('Location: index.php?action=dashboard&message=' . urlencode($message));
                    exit();
                } else {
                    $error = "Unable to add any food items.";
                }
            } else {
                // Single add mode - check for duplicates
                $name = $_POST['name'];
                $location = $_POST['location'];
                $group_id = $_POST['group_id'] ?? null;
                
                // Check if item with same name and location already exists in this group
                $check_query = "SELECT id, quantity FROM foods WHERE LOWER(name) = LOWER(?) AND location = ? AND group_id = ?";
                $check_stmt = $this->db->prepare($check_query);
                $check_stmt->execute([$name, $location, $group_id]);
                $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    // Update existing item - add to quantity
                    $food = new Food($this->db);
                    $food->id = $existing['id'];
                    if ($food->readOne()) {
                        $food->quantity = $existing['quantity'] + $_POST['quantity'];
                        // Update other fields
                        if (!empty($_POST['expiry_date'])) {
                            $food->expiry_date = $_POST['expiry_date'];
                        }
                        if (!empty($_POST['purchase_date'])) {
                            $food->purchase_date = $_POST['purchase_date'];
                        }
                        $food->user_id = $this->current_user->id;
                        
                        if ($food->update()) {
                            header('Location: index.php?action=dashboard&message=Food quantity updated successfully');
                            exit();
                        } else {
                            $error = "Unable to update food item.";
                        }
                    }
                } else {
                    // Create new item
                    $food = new Food($this->db);
                    
                    $food->name = $name;
                    $food->category = $_POST['category'];
                    $food->quantity = $_POST['quantity'];
                    $food->unit = $_POST['unit'];
                    $food->expiry_date = $_POST['expiry_date'];
                    $food->purchase_date = $_POST['purchase_date'];
                    $food->purchase_location = $_POST['purchase_location'];
                    $food->location = $location;
                    $food->notes = $_POST['notes'];
                    $food->user_id = $this->current_user->id;
                    $food->group_id = $group_id;

                    if($food->create()) {
                        header('Location: index.php?action=dashboard&message=Food added successfully');
                        exit();
                    } else {
                        $error = "Unable to add food item.";
                    }
                }
            }
        }
        $current_user = $this->current_user;
        include '../src/views/add_food.php';
    }

    public function editFood() {
        $food = new Food($this->db);
        $food->id = $_GET['id'] ?? 0;
        
        // Get stores, locations, units and categories for dropdowns
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        $units = Unit::getUnitOptions($this->db, true);
        $food_categories = Category::getCategoryOptions($this->db, 'food');
        
        // Get user's groups for group selection
        $user_groups = [];
        $stmt = $this->current_user->getGroups();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_groups[] = $row;
        }
        
        if ($_POST) {
            $food->name = $_POST['name'];
            $food->category = $_POST['category'];
            $food->quantity = $_POST['quantity'];
            $food->unit = $_POST['unit'];
            $food->expiry_date = $_POST['expiry_date'];
            $food->purchase_date = $_POST['purchase_date'];
            $food->purchase_location = $_POST['purchase_location'];
            $food->location = $_POST['location'];
            $food->notes = $_POST['notes'];
            $food->user_id = $this->current_user->id;
            $food->group_id = $_POST['group_id'] ?? null;

            if($food->update()) {
                header('Location: index.php?action=dashboard&message=Food updated successfully');
                exit();
            } else {
                $error = "Unable to update food item.";
            }
        } else {
            $food->readOne();
        }
        
        $current_user = $this->current_user;
        include '../src/views/edit_food.php';
    }

    public function deleteFood() {
        $food = new Food($this->db);
        $food->id = $_GET['id'] ?? 0;

        if($food->delete()) {
            header('Location: index.php?action=dashboard&message=Food deleted successfully');
        } else {
            header('Location: index.php?action=dashboard&error=Unable to delete food item');
        }
        exit();
    }

    public function addIngredient() {
        // Check if user can edit
        if (!$this->current_user->canEdit()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        // Get stores, locations, units and categories for dropdowns
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        $units = Unit::getUnitOptions($this->db, true);
        $ingredient_categories = Category::getCategoryOptions($this->db, 'ingredient');
        
        // Get user's groups for group selection
        $user_groups = [];
        $stmt = $this->current_user->getGroups();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_groups[] = $row;
        }
        $default_group_id = $this->current_user->getDefaultGroupId();
        
        if ($_POST) {
            $mode = $_POST['mode'] ?? 'single';
            
            if ($mode === 'bulk' && !empty($_POST['bulk_names'])) {
                // Bulk add mode
                $lines = array_filter(array_map('trim', explode("\n", $_POST['bulk_names'])));
                $success_count = 0;
                $error_count = 0;
                $default_quantity = $_POST['default_quantity'] ?? 1;
                $default_location = $_POST['default_location'] ?? '';
                
                foreach ($lines as $line) {
                    // Parse CSV format: Name, Quantity, Expiry Date, Location
                    $parts = array_map('trim', str_getcsv($line));
                    $name = $parts[0] ?? '';
                    $quantity = !empty($parts[1]) ? $parts[1] : $default_quantity;
                    $expiry_date = !empty($parts[2]) ? $parts[2] : null;
                    $location = !empty($parts[3]) ? $parts[3] : $default_location;
                    
                    if (empty($name) || empty($location)) continue; // Skip if missing required fields
                    
                    // Check if ingredient already exists (case-insensitive) in the same group
                    $group_id = $_POST['group_id'] ?? null;
                    $check_query = "SELECT id FROM ingredients WHERE LOWER(name) = LOWER(?) AND group_id = ?";
                    $check_stmt = $this->db->prepare($check_query);
                    $check_stmt->execute([$name, $group_id]);
                    $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existing) {
                        // Update existing ingredient - add to quantity at the specified location
                        $ingredient = new Ingredient($this->db);
                        $ingredient->id = $existing['id'];
                        if ($ingredient->readOne()) {
                            // Check if location already exists for this ingredient
                            $location_exists = false;
                            foreach ($ingredient->locations as &$loc) {
                                if ($loc['location'] === $location) {
                                    $loc['quantity'] += $quantity;
                                    $location_exists = true;
                                    break;
                                }
                            }
                            
                            if (!$location_exists) {
                                // Add new location for this ingredient
                                $ingredient->locations[] = [
                                    'location' => $location,
                                    'quantity' => $quantity,
                                    'notes' => ''
                                ];
                            }
                            
                            // Update expiry date if provided
                            if ($expiry_date) {
                                $ingredient->expiry_date = $expiry_date;
                            }
                            // Update purchase date
                            if (!empty($_POST['purchase_date'])) {
                                $ingredient->purchase_date = $_POST['purchase_date'];
                            }
                            $ingredient->user_id = $this->current_user->id;
                            
                            if ($ingredient->update()) {
                                $success_count++;
                            } else {
                                $error_count++;
                            }
                        }
                    } else {
                        // Create new ingredient
                        $ingredient = new Ingredient($this->db);
                        $ingredient->name = $name;
                        $ingredient->category = $_POST['category'];
                        $ingredient->unit = $_POST['unit'];
                        $ingredient->cost_per_unit = null;
                        $ingredient->supplier = null;
                        $ingredient->purchase_date = $_POST['purchase_date'] ?: null;
                        $ingredient->purchase_location = $_POST['purchase_location'];
                        $ingredient->expiry_date = $expiry_date;
                        $ingredient->notes = $_POST['notes'];
                        $ingredient->user_id = $this->current_user->id;
                        $ingredient->group_id = $group_id;
                        
                        // Add location
                        $ingredient->locations = [[
                            'location' => $location,
                            'quantity' => $quantity,
                            'notes' => ''
                        ]];
                        
                        if ($ingredient->create()) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
                
                if ($success_count > 0) {
                    $message = $success_count . ' item(s) added successfully';
                    if ($error_count > 0) {
                        $message .= ' (' . $error_count . ' failed)';
                    }
                    header('Location: index.php?action=dashboard&message=' . urlencode($message));
                    exit();
                } else {
                    $error = "Unable to add any ingredient items.";
                }
            } else {
                // Single add mode
                $ingredient = new Ingredient($this->db);
                
                $ingredient->name = $_POST['name'];
                $ingredient->category = $_POST['category'];
                $ingredient->unit = $_POST['unit'];
                $ingredient->cost_per_unit = $_POST['cost_per_unit'];
                $ingredient->supplier = $_POST['supplier'];
                $ingredient->purchase_date = $_POST['purchase_date'];
                $ingredient->purchase_location = $_POST['purchase_location'];
                $ingredient->expiry_date = $_POST['expiry_date'];
                $ingredient->notes = $_POST['notes'];
                $ingredient->user_id = $this->current_user->id;
                $ingredient->group_id = $_POST['group_id'] ?? null;
                
                // Handle multiple locations
                if (isset($_POST['locations']) && is_array($_POST['locations'])) {
                    $ingredient->locations = [];
                    foreach ($_POST['locations'] as $location_data) {
                        if (!empty($location_data['location']) && !empty($location_data['quantity'])) {
                            $ingredient->locations[] = [
                                'location' => $location_data['location'],
                                'quantity' => floatval($location_data['quantity']),
                                'notes' => $location_data['notes'] ?? ''
                            ];
                        }
                    }
                }

                if($ingredient->create()) {
                    header('Location: index.php?action=dashboard&message=Ingredient added successfully');
                    exit();
                } else {
                    $error = "Unable to add ingredient.";
                }
            }
        }
        $current_user = $this->current_user;
        include '../src/views/add_ingredient.php';
    }

    public function getFoodsJson() {
        header('Content-Type: application/json');
        
        $food = new Food($this->db);
        $stmt = $food->read();
        $foods_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $foods_arr[] = $row;
        }
        
        echo json_encode($foods_arr);
        exit();
    }

    public function getIngredientsJson() {
        header('Content-Type: application/json');
        
        $ingredient = new Ingredient($this->db);
        $stmt = $ingredient->read();
        $ingredients_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ingredients_arr[] = $row;
        }
        
        echo json_encode($ingredients_arr);
        exit();
    }
    
    public function getIngredientLocationsJson() {
        header('Content-Type: application/json');
        
        $ingredient = new Ingredient($this->db);
        $stmt = $ingredient->readWithLocations();
        $ingredients_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ingredients_arr[] = $row;
        }
        
        echo json_encode($ingredients_arr);
        exit();
    }
    
    public function editIngredient() {
        $ingredient = new Ingredient($this->db);
        $ingredient->id = $_GET['id'] ?? 0;
        
        // Get stores, locations, units and categories for dropdowns
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        $units = Unit::getUnitOptions($this->db, true);
        $ingredient_categories = Category::getCategoryOptions($this->db, 'ingredient');
        
        // Get user's groups for group selection
        $user_groups = [];
        $stmt = $this->current_user->getGroups();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_groups[] = $row;
        }
        
        if ($_POST) {
            $ingredient->name = $_POST['name'];
            $ingredient->category = $_POST['category'];
            $ingredient->unit = $_POST['unit'];
            $ingredient->cost_per_unit = $_POST['cost_per_unit'];
            $ingredient->supplier = $_POST['supplier'];
            $ingredient->purchase_date = $_POST['purchase_date'];
            $ingredient->purchase_location = $_POST['purchase_location'];
            $ingredient->expiry_date = $_POST['expiry_date'];
            $ingredient->notes = $_POST['notes'];
            $ingredient->user_id = $this->current_user->id;
            $ingredient->group_id = $_POST['group_id'] ?? null;
            
            // Handle multiple locations
            if (isset($_POST['locations']) && is_array($_POST['locations'])) {
                $ingredient->locations = [];
                foreach ($_POST['locations'] as $location_data) {
                    if (!empty($location_data['location']) && isset($location_data['quantity'])) {
                        $ingredient->locations[] = [
                            'location' => $location_data['location'],
                            'quantity' => floatval($location_data['quantity']),
                            'notes' => $location_data['notes'] ?? ''
                        ];
                    }
                }
            }
            
            if($ingredient->update()) {
                header('Location: index.php?action=dashboard&message=Ingredient updated successfully');
                exit();
            } else {
                $error = "Unable to update ingredient.";
            }
        } else {
            $ingredient->readOne();
        }
        
        $current_user = $this->current_user;
        include '../src/views/edit_ingredient.php';
    }
    
    public function deleteIngredient() {
        $ingredient = new Ingredient($this->db);
        $ingredient->id = $_GET['id'] ?? 0;
        
        if($ingredient->delete()) {
            header('Location: index.php?action=dashboard&message=Ingredient deleted successfully');
        } else {
            header('Location: index.php?action=dashboard&error=Unable to delete ingredient');
        }
        exit();
    }
    
    public function updateIngredientLocation() {
        if ($_POST && isset($_POST['ingredient_id'], $_POST['location'], $_POST['quantity'])) {
            $ingredient = new Ingredient($this->db);
            $ingredient->id = $_POST['ingredient_id'];
            
            if($ingredient->updateLocationQuantity($_POST['location'], $_POST['quantity'])) {
                echo json_encode(['success' => true, 'message' => 'Location quantity updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Unable to update location quantity']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
        }
        exit();
    }
    
    // Store Management Methods
    public function manageStores() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $store = new Store($this->db);
        $stores = $store->read();
        
        $current_user = $this->current_user;
        include '../src/views/manage_stores.php';
    }
    
    public function addStore() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($_POST) {
            $store = new Store($this->db);
            
            $store->name = $_POST['name'];
            $store->address = $_POST['address'];
            $store->phone = $_POST['phone'];
            $store->website = $_POST['website'];
            $store->notes = $_POST['notes'];
            $store->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if ($store->nameExists()) {
                $error = "A store with this name already exists.";
            } else if ($store->create()) {
                header('Location: index.php?action=system_settings&message=Store added successfully');
                exit();
            } else {
                $error = "Unable to add store.";
            }
        }
        
        $current_user = $this->current_user;
        include '../src/views/add_store.php';
    }
    
    public function editStore() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $store = new Store($this->db);
        $store->id = $_GET['id'] ?? 0;
        
        if ($_POST) {
            // Load original data to check if name changed
            $original_store = new Store($this->db);
            $original_store->id = $store->id;
            $original_store->readOne();
            
            $store->name = $_POST['name'];
            $store->address = $_POST['address'];
            $store->phone = $_POST['phone'];
            $store->website = $_POST['website'];
            $store->notes = $_POST['notes'];
            $store->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Only check for duplicate name if the name changed
            if ($store->name !== $original_store->name && $store->nameExists($store->id)) {
                $error = "A store with this name already exists.";
            } else if ($store->update()) {
                header('Location: index.php?action=system_settings&message=Store updated successfully');
                exit();
            } else {
                $error = "Unable to update store.";
            }
        } else {
            $store->readOne();
        }
        
        $current_user = $this->current_user;
        include '../src/views/edit_store.php';
    }
    
    public function deleteStore() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $store = new Store($this->db);
        $store->id = $_GET['id'] ?? 0;
        
        if ($store->hardDelete()) {
            header('Location: index.php?action=system_settings&message=Store deleted successfully');
        } else {
            header('Location: index.php?action=system_settings&error=Unable to delete store');
        }
        exit();
    }
    
    public function toggleStoreStatus() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $store = new Store($this->db);
        $store->id = $_GET['id'] ?? 0;
        
        if ($store->toggleActive()) {
            header('Location: index.php?action=system_settings&message=Store status updated successfully');
        } else {
            header('Location: index.php?action=system_settings&error=Unable to update store status');
        }
        exit();
    }
    
    // Location Management Methods
    public function manageLocations() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $location = new Location($this->db);
        $locations = $location->read();
        
        $current_user = $this->current_user;
        include '../src/views/manage_locations.php';
    }
    
    public function addLocation() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($_POST) {
            $location = new Location($this->db);
            
            $location->name = $_POST['name'];
            $location->description = $_POST['description'];
            $location->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if ($location->nameExists()) {
                $error = "A location with this name already exists.";
            } else if ($location->create()) {
                header('Location: index.php?action=system_settings&message=Location added successfully');
                exit();
            } else {
                $error = "Unable to add location.";
            }
        }
        
        $current_user = $this->current_user;
        include '../src/views/add_location.php';
    }
    
    public function editLocation() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $location = new Location($this->db);
        $location->id = $_GET['id'] ?? 0;
        
        if ($_POST) {
            $location->name = $_POST['name'];
            $location->description = $_POST['description'];
            $location->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if ($location->nameExists($location->id)) {
                $error = "A location with this name already exists.";
            } else if ($location->update()) {
                header('Location: index.php?action=system_settings&message=Location updated successfully');
                exit();
            } else {
                $error = "Unable to update location.";
            }
        } else {
            $location->readOne();
        }
        
        $current_user = $this->current_user;
        include '../src/views/edit_location.php';
    }
    
    public function deleteLocation() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $location = new Location($this->db);
        $location->id = $_GET['id'] ?? 0;
        $location->readOne();
        
        // Check if location is in use
        $food_count = $location->getFoodCount();
        $ingredient_count = $location->getIngredientLocationCount();
        
        if ($food_count > 0 || $ingredient_count > 0) {
            // Show migration form
            if ($_POST && isset($_POST['migrate_to'])) {
                if ($location->migrateToLocation($_POST['migrate_to'])) {
                    if ($location->delete()) {
                        header('Location: index.php?action=system_settings&message=Location deleted and items migrated successfully');
                        exit();
                    }
                }
                $error = "Unable to migrate items and delete location.";
            }
            
            // Get other locations for migration dropdown
            $other_locations = Location::getLocationOptions($this->db);
            $current_user = $this->current_user;
            include '../src/views/delete_location.php';
        } else {
            // No items using this location, safe to delete
            if ($location->delete()) {
                header('Location: index.php?action=system_settings&message=Location deleted successfully');
            } else {
                header('Location: index.php?action=system_settings&error=Unable to delete location');
            }
            exit();
        }
    }
    
    public function toggleLocationStatus() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $location = new Location($this->db);
        $location->id = $_GET['id'] ?? 0;
        
        if ($location->toggleActive()) {
            header('Location: index.php?action=system_settings&message=Location status updated successfully');
        } else {
            header('Location: index.php?action=system_settings&error=Unable to update location status');
        }
        exit();
    }
    
    // Unit Management Methods
    public function manageUnits() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $unit = new Unit($this->db);
        $units_stmt = $unit->read();
        
        $units = [];
        while ($row = $units_stmt->fetch(PDO::FETCH_ASSOC)) {
            $units[] = $row;
        }
        
        $current_user = $this->current_user;
        include '../src/views/manage_units.php';
    }
    
    public function addUnit() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($_POST) {
            $unit = new Unit($this->db);
            
            $unit->name = $_POST['name'];
            $unit->abbreviation = $_POST['abbreviation'];
            $unit->description = $_POST['description'];
            $unit->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if ($unit->nameExists()) {
                $error = "A unit with this name already exists.";
            } else if ($unit->abbreviationExists()) {
                $error = "A unit with this abbreviation already exists.";
            } else if ($unit->create()) {
                header('Location: index.php?action=system_settings&message=Unit added successfully');
                exit();
            } else {
                $error = "Unable to add unit.";
            }
        }
        
        $current_user = $this->current_user;
        include '../src/views/add_unit.php';
    }
    
    public function editUnit() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $unit = new Unit($this->db);
        $unit->id = $_GET['id'] ?? 0;
        
        if ($_POST) {
            $unit->name = $_POST['name'];
            $unit->abbreviation = $_POST['abbreviation'];
            $unit->description = $_POST['description'];
            $unit->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if ($unit->nameExists($unit->id)) {
                $error = "A unit with this name already exists.";
            } else if ($unit->abbreviationExists($unit->id)) {
                $error = "A unit with this abbreviation already exists.";
            } else if ($unit->update()) {
                header('Location: index.php?action=system_settings&message=Unit updated successfully');
                exit();
            } else {
                $error = "Unable to update unit.";
            }
        } else {
            $unit->readOne();
        }
        
        $current_user = $this->current_user;
        include '../src/views/edit_unit.php';
    }
    
    public function deleteUnit() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $unit = new Unit($this->db);
        $unit->id = $_GET['id'] ?? 0;
        
        if ($unit->delete()) {
            header('Location: index.php?action=system_settings&message=Unit deleted successfully');
        } else {
            header('Location: index.php?action=system_settings&error=Unable to delete unit');
        }
        exit();
    }
    
    public function toggleUnitStatus() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $unit = new Unit($this->db);
        $unit->id = $_GET['id'] ?? 0;
        
        if ($unit->toggleActive()) {
            header('Location: index.php?action=system_settings&message=Unit status updated successfully');
        } else {
            header('Location: index.php?action=system_settings&error=Unable to update unit status');
        }
        exit();
    }
    
    // System Settings (Combined Units, Categories, Stores, and Locations)
    public function systemSettings() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        // Get units
        $unit = new Unit($this->db);
        $units_stmt = $unit->read();
        $units = [];
        while ($row = $units_stmt->fetch(PDO::FETCH_ASSOC)) {
            $units[] = $row;
        }
        
        // Get categories
        $category = new Category($this->db);
        $food_categories_stmt = $category->read('food');
        $food_categories = [];
        while ($row = $food_categories_stmt->fetch(PDO::FETCH_ASSOC)) {
            $food_categories[] = $row;
        }
        
        $ingredient_categories_stmt = $category->read('ingredient');
        $ingredient_categories = [];
        while ($row = $ingredient_categories_stmt->fetch(PDO::FETCH_ASSOC)) {
            $ingredient_categories[] = $row;
        }
        
        // Get stores
        $store = new Store($this->db);
        $stores_stmt = $store->read();
        $stores = [];
        while ($row = $stores_stmt->fetch(PDO::FETCH_ASSOC)) {
            $stores[] = $row;
        }
        
        // Get locations
        $location = new Location($this->db);
        $locations_stmt = $location->read();
        $locations = [];
        $location_model = new Location($this->db);
        while ($row = $locations_stmt->fetch(PDO::FETCH_ASSOC)) {
            $location_model->id = $row['id'];
            $row['food_count'] = $location_model->getFoodCount();
            $row['ingredient_count'] = $location_model->getIngredientLocationCount();
            $locations[] = $row;
        }
        
        $current_user = $this->current_user;
        $db = $this->db; // For location views that need it
        include '../src/views/system_settings.php';
    }
    
    // Category Management Methods
    public function addCategory() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($_POST) {
            $category = new Category($this->db);
            
            $category->name = $_POST['name'];
            $category->type = $_POST['type'];
            $category->description = $_POST['description'];
            
            if ($category->nameExists($category->type)) {
                $error = "A category with this name already exists for this type.";
            } else if ($category->create()) {
                header('Location: index.php?action=system_settings&message=Category added successfully');
                exit();
            } else {
                $error = "Unable to add category.";
            }
        }
        
        $current_user = $this->current_user;
        include '../src/views/add_category.php';
    }
    
    public function editCategory() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $category = new Category($this->db);
        $category->id = $_GET['id'] ?? 0;
        
        if ($_POST) {
            $category->name = $_POST['name'];
            $category->type = $_POST['type'];
            $category->description = $_POST['description'];
            
            if ($category->nameExists($category->type, $category->id)) {
                $error = "A category with this name already exists for this type.";
            } else if ($category->update()) {
                header('Location: index.php?action=system_settings&message=Category updated successfully');
                exit();
            } else {
                $error = "Unable to update category.";
            }
        } else {
            $category->readOne();
        }
        
        $current_user = $this->current_user;
        include '../src/views/edit_category.php';
    }
    
    public function deleteCategory() {
        // Check if user is admin
        if (!$this->current_user->isAdmin()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $category = new Category($this->db);
        $category->id = $_GET['id'] ?? 0;
        
        if ($category->delete()) {
            header('Location: index.php?action=system_settings&message=Category deleted successfully');
        } else {
            header('Location: index.php?action=system_settings&error=Unable to delete category');
        }
        exit();
    }
    
    // Meal Tracking Methods
    public function trackMeal() {
        // Check if user can edit
        if (!$this->current_user->canEdit()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $search_results = [];
        $search_query = '';
        
        if ($_GET['search'] ?? '') {
            $search_query = $_GET['search'];
            $search_terms = array_map('trim', explode(',', $search_query));
            
            // Search foods
            $food = new Food($this->db);
            foreach ($search_terms as $term) {
                if (!empty($term)) {
                    $stmt = $food->search($term);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $search_results[] = array_merge($row, ['type' => 'food']);
                    }
                }
            }
            
            // Search ingredients
            $ingredient = new Ingredient($this->db);
            foreach ($search_terms as $term) {
                if (!empty($term)) {
                    $stmt = $ingredient->search($term);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $search_results[] = array_merge($row, ['type' => 'ingredient']);
                    }
                }
            }
        }
        
        $current_user = $this->current_user;
        $db = $this->db; // Pass database connection to view
        include '../src/views/track_meal.php';
    }
    
    public function updateMealItems() {
        // Check if user can edit
        if (!$this->current_user->canEdit()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($_POST) {
            $success_count = 0;
            $error_count = 0;
            
            // Process foods
            if (isset($_POST['food_updates'])) {
                foreach ($_POST['food_updates'] as $food_id => $data) {
                    $food = new Food($this->db);
                    $food->id = $food_id;
                    
                    if ($food->readOne()) {
                        if (isset($data['delete'])) {
                            // Delete item
                            if ($food->delete()) {
                                $success_count++;
                            } else {
                                $error_count++;
                            }
                        } else if (isset($data['decrement'])) {
                            // Decrement quantity (never goes below 0)
                            $decrement_by = floatval($data['decrement']);
                            $new_quantity = max(0, $food->quantity - $decrement_by);
                            $food->quantity = $new_quantity;
                            
                            // Update item (keep at 0 instead of deleting)
                            if ($food->update()) {
                                $success_count++;
                            } else {
                                $error_count++;
                            }
                        }
                    }
                }
            }
            
            // Process ingredients
            if (isset($_POST['ingredient_updates'])) {
                foreach ($_POST['ingredient_updates'] as $ingredient_id => $data) {
                    $ingredient = new Ingredient($this->db);
                    $ingredient->id = $ingredient_id;
                    
                    if ($ingredient->readOne()) {
                        if (isset($data['delete'])) {
                            // Delete item
                            if ($ingredient->delete()) {
                                $success_count++;
                            } else {
                                $error_count++;
                            }
                        } else if (isset($data['decrement']) && isset($data['location'])) {
                            // Decrement quantity at specific location (never goes below 0)
                            $decrement_by = floatval($data['decrement']);
                            $location = $data['location'];
                            
                            // Find current quantity at location
                            $current_qty = 0;
                            foreach ($ingredient->locations as $loc) {
                                if ($loc['location'] === $location) {
                                    $current_qty = $loc['quantity'];
                                    break;
                                }
                            }
                            
                            $new_quantity = max(0, $current_qty - $decrement_by);
                            
                            // Update location quantity (keep at 0 instead of removing)
                            if ($ingredient->updateLocationQuantity($location, $new_quantity)) {
                                $success_count++;
                            } else {
                                $error_count++;
                            }
                        }
                    }
                }
            }
            
            if ($success_count > 0) {
                header('Location: index.php?action=track_meal&message=' . urlencode($success_count . ' item(s) updated successfully'));
            } else if ($error_count > 0) {
                header('Location: index.php?action=track_meal&error=' . urlencode('Failed to update ' . $error_count . ' item(s)'));
            } else {
                header('Location: index.php?action=track_meal');
            }
        } else {
            header('Location: index.php?action=track_meal');
        }
        exit();
    }
    
    // Group Management Methods
    public function listGroups() {
        try {
            $group = new Group($this->db);
            $stmt = $this->current_user->getGroups();
            $groups = [];
            
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $group->id = $row['id'];
                    $row['member_count'] = $group->getMemberCount();
                    $row['inventory_counts'] = $group->getInventoryCounts();
                    $groups[] = $row;
                }
            }
            
            $current_user = $this->current_user;
            include '../src/views/groups/list_groups.php';
        } catch (Exception $e) {
            echo "Error loading groups: " . $e->getMessage();
            error_log("Groups error: " . $e->getMessage());
        }
    }
    
    public function createGroup() {
        // Check if user can create groups (admin or user role)
        if (!$this->current_user->canEdit()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($_POST) {
            $group = new Group($this->db);
            
            $group->name = $_POST['name'];
            $group->description = $_POST['description'];
            
            if ($group->create()) {
                // Add the creator as owner
                $group->addMember($this->current_user->id, 'owner');
                
                header('Location: index.php?action=list_groups&message=Group created successfully');
                exit();
            } else {
                $error = "Unable to create group.";
            }
        }
        
        $current_user = $this->current_user;
        include '../src/views/groups/create_group.php';
    }
    
    public function editGroup() {
        $group = new Group($this->db);
        $group->id = $_GET['id'] ?? 0;
        
        // Check if user is group owner or admin
        $membership = $this->current_user->isMemberOfGroup($group->id);
        if (!$this->current_user->isAdmin() && (!$membership || !in_array($membership['role'], ['owner', 'admin']))) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($_POST) {
            $group->name = $_POST['name'];
            $group->description = $_POST['description'];
            
            if ($group->update()) {
                header('Location: index.php?action=list_groups&message=Group updated successfully');
                exit();
            } else {
                $error = "Unable to update group.";
            }
        } else {
            $group->readOne();
        }
        
        $current_user = $this->current_user;
        include '../src/views/groups/edit_group.php';
    }
    
    public function deleteGroup() {
        $group = new Group($this->db);
        $group->id = $_GET['id'] ?? 0;
        
        // Check if user is group owner or admin
        $membership = $this->current_user->isMemberOfGroup($group->id);
        if (!$this->current_user->isAdmin() && (!$membership || $membership['role'] !== 'owner')) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($group->delete()) {
            header('Location: index.php?action=list_groups&message=Group deleted successfully');
        } else {
            header('Location: index.php?action=list_groups&error=Unable to delete group');
        }
        exit();
    }
    
    public function manageGroupMembers() {
        $group = new Group($this->db);
        $group->id = $_GET['id'] ?? 0;
        $group->readOne();
        
        // Check if user is group owner/admin or system admin
        $membership = $this->current_user->isMemberOfGroup($group->id);
        if (!$this->current_user->isAdmin() && (!$membership || !in_array($membership['role'], ['owner', 'admin']))) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $members_stmt = $group->getMembers();
        $members = [];
        while ($row = $members_stmt->fetch(PDO::FETCH_ASSOC)) {
            $members[] = $row;
        }
        
        // Get all users for add member dropdown
        $user = new User($this->db);
        $users_stmt = $user->read();
        $all_users = [];
        while ($row = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
            // Exclude users who are already members
            $is_member = false;
            foreach ($members as $member) {
                if ($member['id'] == $row['id']) {
                    $is_member = true;
                    break;
                }
            }
            if (!$is_member) {
                $all_users[] = $row;
            }
        }
        
        $current_user = $this->current_user;
        include '../src/views/groups/manage_members.php';
    }
    
    public function addGroupMember() {
        if ($_POST) {
            $group = new Group($this->db);
            $group->id = $_POST['group_id'] ?? 0;
            
            // Check if user is group owner/admin or system admin
            $membership = $this->current_user->isMemberOfGroup($group->id);
            if (!$this->current_user->isAdmin() && (!$membership || !in_array($membership['role'], ['owner', 'admin']))) {
                header('Location: index.php?action=access_denied');
                exit();
            }
            
            $user_id = $_POST['user_id'];
            $role = $_POST['role'] ?? 'member';
            
            if ($group->addMember($user_id, $role)) {
                header('Location: index.php?action=manage_group_members&id=' . $group->id . '&message=Member added successfully');
            } else {
                header('Location: index.php?action=manage_group_members&id=' . $group->id . '&error=Unable to add member');
            }
        }
        exit();
    }
    
    public function updateGroupMemberRole() {
        if ($_POST) {
            $group = new Group($this->db);
            $group->id = $_POST['group_id'] ?? 0;
            
            // Check if user is group owner/admin or system admin
            $membership = $this->current_user->isMemberOfGroup($group->id);
            if (!$this->current_user->isAdmin() && (!$membership || !in_array($membership['role'], ['owner', 'admin']))) {
                header('Location: index.php?action=access_denied');
                exit();
            }
            
            $user_id = $_POST['user_id'];
            $role = $_POST['role'];
            
            if ($group->updateMemberRole($user_id, $role)) {
                header('Location: index.php?action=manage_group_members&id=' . $group->id . '&message=Role updated successfully');
            } else {
                header('Location: index.php?action=manage_group_members&id=' . $group->id . '&error=Unable to update role');
            }
        }
        exit();
    }
    
    public function removeGroupMember() {
        $group = new Group($this->db);
        $group->id = $_GET['group_id'] ?? 0;
        $user_id = $_GET['user_id'] ?? 0;
        
        // Check if user is group owner/admin or system admin
        $membership = $this->current_user->isMemberOfGroup($group->id);
        if (!$this->current_user->isAdmin() && (!$membership || !in_array($membership['role'], ['owner', 'admin']))) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($group->removeMember($user_id)) {
            header('Location: index.php?action=manage_group_members&id=' . $group->id . '&message=Member removed successfully');
        } else {
            header('Location: index.php?action=manage_group_members&id=' . $group->id . '&error=Unable to remove member');
        }
        exit();
    }
    
    public function setDefaultGroup() {
        if ($_POST) {
            $group_id = $_POST['group_id'] ?? null;
            
            if ($this->current_user->setDefaultGroup($group_id)) {
                header('Location: index.php?action=list_groups&message=Default group updated successfully');
            } else {
                header('Location: index.php?action=list_groups&error=Unable to set default group');
            }
        } else {
            header('Location: index.php?action=list_groups');
        }
        exit();
    }
    
    public function bulkSearch() {
        // Check if user can edit
        if (!$this->current_user->canEdit()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        $search_items = $_GET['search_items'] ?? '';
        $search_results = [];
        
        if (!empty($search_items)) {
            // Split by comma and trim each item
            $search_terms = array_map('trim', explode(',', $search_items));
            $search_terms = array_filter($search_terms); // Remove empty items
            
            if (!empty($search_terms)) {
                $food = new Food($this->db);
                $ingredient = new Ingredient($this->db);
                
                // Get user's group IDs for filtering
                $group_ids = $this->current_user->getGroupIds();
                
                foreach ($search_terms as $term) {
                    // Search foods
                    $query = "SELECT f.*, 'food' as type, g.name as group_name 
                              FROM foods f
                              LEFT JOIN groups g ON f.group_id = g.id
                              WHERE LOWER(f.name) LIKE LOWER(?)
                              " . (!$this->current_user->isAdmin() && !empty($group_ids) ? 
                                  "AND f.group_id IN (" . implode(',', array_fill(0, count($group_ids), '?')) . ")" : "") . "
                              ORDER BY f.name";
                    
                    $params = ['%' . $term . '%'];
                    if (!$this->current_user->isAdmin() && !empty($group_ids)) {
                        $params = array_merge($params, $group_ids);
                    }
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->execute($params);
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $search_results[] = $row;
                    }
                    
                    // Search ingredients
                    $query = "SELECT i.*, 'ingredient' as type, g.name as group_name,
                              COALESCE(SUM(il.quantity), 0) as quantity
                              FROM ingredients i
                              LEFT JOIN ingredient_locations il ON i.id = il.ingredient_id
                              LEFT JOIN groups g ON i.group_id = g.id
                              WHERE LOWER(i.name) LIKE LOWER(?)
                              " . (!$this->current_user->isAdmin() && !empty($group_ids) ? 
                                  "AND i.group_id IN (" . implode(',', array_fill(0, count($group_ids), '?')) . ")" : "") . "
                              GROUP BY i.id
                              ORDER BY i.name";
                    
                    $params = ['%' . $term . '%'];
                    if (!$this->current_user->isAdmin() && !empty($group_ids)) {
                        $params = array_merge($params, $group_ids);
                    }
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->execute($params);
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $search_results[] = $row;
                    }
                }
            }
        }
        
        // Get dropdown options for the form
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        $units = Unit::getUnitOptions($this->db, true);
        
        // Get categories from database
        $categories_query = "SELECT DISTINCT name FROM categories ORDER BY name";
        $categories_stmt = $this->db->prepare($categories_query);
        $categories_stmt->execute();
        $categories = [];
        while ($row = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row['name'];
        }
        
        // Re-run dashboard with search results
        $food = new Food($this->db);
        $ingredient = new Ingredient($this->db);
        
        // Filter by current user's groups
        $group_ids = $this->current_user->getGroupIds();
        
        // Get group filter for admins
        $filter_group_id = null;
        $show_all_groups = false;
        
        if ($this->current_user->isAdmin()) {
            // Check if admin has selected a specific group filter or "all groups"
            if (isset($_GET['group_filter'])) {
                if ($_GET['group_filter'] === 'all') {
                    $show_all_groups = true;
                } else {
                    $filter_group_id = intval($_GET['group_filter']);
                }
            } else {
                // Default to user's default group if set
                $filter_group_id = $this->current_user->getDefaultGroupId();
            }
            
            // Get all groups for the filter dropdown
            $group_model = new Group($this->db);
            $all_groups_stmt = $group_model->read();
            $all_groups = [];
            while ($row = $all_groups_stmt->fetch(PDO::FETCH_ASSOC)) {
                $all_groups[] = $row;
            }
        }
        
        if ($this->current_user->isAdmin()) {
            if ($show_all_groups) {
                // Admin viewing all groups - show all items with group names
                $foods = $food->read();
                $ingredients = $ingredient->read();
                $expiring_foods = $food->getExpiringItems(7);
                $low_stock_ingredients = $ingredient->getLowStockItems(10);
                $low_stock_foods = $food->getLowStockItems(10);
            } else if ($filter_group_id) {
                // Admin viewing specific group
                $foods = $food->readByGroups([$filter_group_id]);
                $ingredients = $ingredient->readByGroups([$filter_group_id]);
                $expiring_foods = $food->getExpiringItemsByGroups([$filter_group_id], 7);
                $low_stock_ingredients = $ingredient->getLowStockItemsByGroups([$filter_group_id], 10);
                $low_stock_foods = $food->getLowStockItemsByGroups([$filter_group_id], 10);
            } else {
                // Admin not in any group and no filter selected
                $foods = $food->read();
                $ingredients = $ingredient->read();
                $expiring_foods = $food->getExpiringItems(7);
                $low_stock_ingredients = $ingredient->getLowStockItems(10);
                $low_stock_foods = $food->getLowStockItems(10);
            }
        } else if (!empty($group_ids)) {
            // Regular users see items from their groups
            $foods = $food->readByGroups($group_ids);
            $ingredients = $ingredient->readByGroups($group_ids);
            $expiring_foods = $food->getExpiringItemsByGroups($group_ids, 7);
            $low_stock_ingredients = $ingredient->getLowStockItemsByGroups($group_ids, 10);
            $low_stock_foods = $food->getLowStockItemsByGroups($group_ids, 10);
        } else {
            // User not in any group - show empty results
            $foods = false;
            $ingredients = false;
            $expiring_foods = false;
            $low_stock_ingredients = false;
            $low_stock_foods = false;
        }

        $current_user = $this->current_user;
        include '../src/views/dashboard.php';
    }
    
    public function bulkUpdate() {
        // Check if user can edit
        if (!$this->current_user->canEdit()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        if ($_POST && isset($_POST['items'])) {
            $updated_count = 0;
            $error_count = 0;
            
            foreach ($_POST['items'] as $key => $item_data) {
                $type = $item_data['type'];
                $id = $item_data['id'];
                
                if ($type === 'food') {
                    $food = new Food($this->db);
                    $food->id = $id;
                    
                    if ($food->readOne()) {
                        $food->name = $food->name; // Keep existing name
                        $food->category = $item_data['category'] ?? $food->category;
                        $food->quantity = $item_data['quantity'] ?? $food->quantity;
                        $food->unit = $item_data['unit'] ?? $food->unit;
                        $food->expiry_date = !empty($item_data['expiry_date']) ? $item_data['expiry_date'] : null;
                        $food->purchase_date = !empty($item_data['purchase_date']) ? $item_data['purchase_date'] : null;
                        $food->purchase_location = $item_data['purchase_location'] ?? $food->purchase_location;
                        $food->location = $item_data['location'] ?? $food->location;
                        $food->user_id = $this->current_user->id;
                        $food->group_id = $food->group_id; // Keep existing group
                        
                        if ($food->update()) {
                            $updated_count++;
                        } else {
                            $error_count++;
                        }
                    }
                } else if ($type === 'ingredient') {
                    $ingredient = new Ingredient($this->db);
                    $ingredient->id = $id;
                    
                    if ($ingredient->readOne()) {
                        $ingredient->name = $ingredient->name; // Keep existing name
                        $ingredient->category = $item_data['category'] ?? $ingredient->category;
                        $ingredient->unit = $item_data['unit'] ?? $ingredient->unit;
                        $ingredient->cost_per_unit = !empty($item_data['cost_per_unit']) ? $item_data['cost_per_unit'] : null;
                        $ingredient->supplier = $item_data['supplier'] ?? $ingredient->supplier;
                        $ingredient->purchase_date = !empty($item_data['purchase_date']) ? $item_data['purchase_date'] : null;
                        $ingredient->purchase_location = $item_data['purchase_location'] ?? $ingredient->purchase_location;
                        $ingredient->expiry_date = !empty($item_data['expiry_date']) ? $item_data['expiry_date'] : null;
                        $ingredient->user_id = $this->current_user->id;
                        $ingredient->group_id = $ingredient->group_id; // Keep existing group
                        
                        // Note: For ingredients, quantity is handled separately in ingredient_locations
                        // This bulk update focuses on the main ingredient properties
                        
                        if ($ingredient->update()) {
                            $updated_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
            }
            
            if ($error_count > 0) {
                header('Location: index.php?action=dashboard&message=' . $updated_count . ' items updated&error=' . $error_count . ' items failed to update');
            } else {
                header('Location: index.php?action=dashboard&message=' . $updated_count . ' items updated successfully');
            }
        } else {
            header('Location: index.php?action=dashboard');
        }
        exit();
    }
}
?>
