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
        
        // Filter by current user if not admin
        if ($this->current_user->isAdmin()) {
            $foods = $food->read();
            $ingredients = $ingredient->read();
            $expiring_foods = $food->getExpiringItems(7);
            $low_stock_ingredients = $ingredient->getLowStockItems(10);
        } else {
            $foods = $food->readByUser($this->current_user->id);
            $ingredients = $ingredient->readByUser($this->current_user->id);
            $expiring_foods = $food->getExpiringItemsByUser($this->current_user->id, 7);
            $low_stock_ingredients = $ingredient->getLowStockItemsByUser($this->current_user->id, 10);
        }

        include '../src/views/dashboard.php';
    }

    public function addFood() {
        // Check if user can edit
        if (!$this->current_user->canEdit()) {
            header('Location: index.php?action=access_denied');
            exit();
        }
        
        // Get stores for dropdown
        $stores = Store::getStoreOptions($this->db);
        
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

            if($food->create()) {
                header('Location: index.php?action=dashboard&message=Food added successfully');
                exit();
            } else {
                $error = "Unable to add food item.";
            }
        }
        include '../src/views/add_food.php';
    }

    public function editFood() {
        $food = new Food($this->db);
        $food->id = $_GET['id'] ?? 0;
        
        // Get stores for dropdown
        $stores = Store::getStoreOptions($this->db);

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

            if($food->update()) {
                header('Location: index.php?action=dashboard&message=Food updated successfully');
                exit();
            } else {
                $error = "Unable to update food item.";
            }
        } else {
            $food->readOne();
        }
        
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
        
        // Get stores for dropdown
        $stores = Store::getStoreOptions($this->db);
        
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
        
        // Get stores for dropdown
        $stores = Store::getStoreOptions($this->db);
        
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
            $store->name = $_POST['name'];
            $store->address = $_POST['address'];
            $store->phone = $_POST['phone'];
            $store->website = $_POST['website'];
            $store->notes = $_POST['notes'];
            $store->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if ($store->nameExists($store->id)) {
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
        
        if ($store->delete()) {
            header('Location: index.php?action=manage_stores&message=Store deactivated successfully');
        } else {
            header('Location: index.php?action=manage_stores&error=Unable to deactivate store');
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
}
?>