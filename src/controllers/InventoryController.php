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
        
        // Filter by current user's groups
        $group_ids = $this->current_user->getGroupIds();
        
        if ($this->current_user->isAdmin()) {
            // Admin sees all items
            $foods = $food->read();
            $ingredients = $ingredient->read();
            $expiring_foods = $food->getExpiringItems(7);
            $low_stock_ingredients = $ingredient->getLowStockItems(10);
        } else if (!empty($group_ids)) {
            // Regular users see items from their groups
            $foods = $food->readByGroups($group_ids);
            $ingredients = $ingredient->readByGroups($group_ids);
            $expiring_foods = $food->getExpiringItemsByGroups($group_ids, 7);
            $low_stock_ingredients = $ingredient->getLowStockItemsByGroups($group_ids, 10);
        } else {
            // User not in any group - show empty results
            $foods = false;
            $ingredients = false;
            $expiring_foods = false;
            $low_stock_ingredients = false;
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
        
        // Get stores and locations for dropdowns
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        
        // Get user's groups for group selection
        $user_groups = [];
        $stmt = $this->current_user->getGroups();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_groups[] = $row;
        }
        
        if ($_POST) {
            $food = new Food($this->db);
            
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

            if($food->create()) {
                header('Location: index.php?action=dashboard&message=Food added successfully');
                exit();
            } else {
                $error = "Unable to add food item.";
            }
        }
        $current_user = $this->current_user;
        include '../src/views/add_food.php';
    }

    public function editFood() {
        $food = new Food($this->db);
        $food->id = $_GET['id'] ?? 0;
        
        // Get stores and locations for dropdowns
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        
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
        
        // Get stores and locations for dropdowns
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        
        // Get user's groups for group selection
        $user_groups = [];
        $stmt = $this->current_user->getGroups();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_groups[] = $row;
        }
        
        if ($_POST) {
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
        
        // Get stores and locations for dropdowns
        $stores = Store::getStoreOptions($this->db);
        $locations = Location::getLocationOptions($this->db, true);
        
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
                header('Location: index.php?action=manage_stores&message=Store added successfully');
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
                header('Location: index.php?action=manage_stores&message=Store updated successfully');
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
            header('Location: index.php?action=manage_stores&message=Store deleted successfully');
        } else {
            header('Location: index.php?action=manage_stores&error=Unable to delete store');
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
            header('Location: index.php?action=manage_stores&message=Store status updated successfully');
        } else {
            header('Location: index.php?action=manage_stores&error=Unable to update store status');
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
                header('Location: index.php?action=manage_locations&message=Location added successfully');
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
                header('Location: index.php?action=manage_locations&message=Location updated successfully');
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
                        header('Location: index.php?action=manage_locations&message=Location deleted and items migrated successfully');
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
                header('Location: index.php?action=manage_locations&message=Location deleted successfully');
            } else {
                header('Location: index.php?action=manage_locations&error=Unable to delete location');
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
            header('Location: index.php?action=manage_locations&message=Location status updated successfully');
        } else {
            header('Location: index.php?action=manage_locations&error=Unable to update location status');
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
                            // Decrement quantity
                            $decrement_by = floatval($data['decrement']);
                            $new_quantity = max(0, $food->quantity - $decrement_by);
                            $food->quantity = $new_quantity;
                            
                            if ($new_quantity == 0) {
                                // Delete if quantity reaches 0
                                if ($food->delete()) {
                                    $success_count++;
                                } else {
                                    $error_count++;
                                }
                            } else {
                                if ($food->update()) {
                                    $success_count++;
                                } else {
                                    $error_count++;
                                }
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
                            // Decrement quantity at specific location
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
                            
                            if ($new_quantity == 0) {
                                // Remove location if quantity reaches 0
                                if ($ingredient->removeLocation($location)) {
                                    $success_count++;
                                } else {
                                    $error_count++;
                                }
                                
                                // Check if ingredient has any locations left
                                $ingredient->loadLocations();
                                if (empty($ingredient->locations)) {
                                    $ingredient->delete();
                                }
                            } else {
                                if ($ingredient->updateLocationQuantity($location, $new_quantity)) {
                                    $success_count++;
                                } else {
                                    $error_count++;
                                }
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
}
?>
