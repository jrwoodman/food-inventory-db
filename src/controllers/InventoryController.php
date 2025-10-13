<?php
class InventoryController {
    private $database;
    private $db;

    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
    }

    public function dashboard() {
        $food = new Food($this->db);
        $ingredient = new Ingredient($this->db);
        
        $foods = $food->read();
        $ingredients = $ingredient->read();
        $expiring_foods = $food->getExpiringItems(7);
        $low_stock_ingredients = $ingredient->getLowStockItems(10);

        include '../src/views/dashboard.php';
    }

    public function addFood() {
        if ($_POST) {
            $food = new Food($this->db);
            
            $food->name = $_POST['name'];
            $food->category = $_POST['category'];
            $food->quantity = $_POST['quantity'];
            $food->unit = $_POST['unit'];
            $food->expiry_date = $_POST['expiry_date'];
            $food->purchase_date = $_POST['purchase_date'];
            $food->location = $_POST['location'];
            $food->notes = $_POST['notes'];

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

        if ($_POST) {
            $food->name = $_POST['name'];
            $food->category = $_POST['category'];
            $food->quantity = $_POST['quantity'];
            $food->unit = $_POST['unit'];
            $food->expiry_date = $_POST['expiry_date'];
            $food->purchase_date = $_POST['purchase_date'];
            $food->location = $_POST['location'];
            $food->notes = $_POST['notes'];

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
        if ($_POST) {
            $ingredient = new Ingredient($this->db);
            
            $ingredient->name = $_POST['name'];
            $ingredient->category = $_POST['category'];
            $ingredient->quantity = $_POST['quantity'];
            $ingredient->unit = $_POST['unit'];
            $ingredient->cost_per_unit = $_POST['cost_per_unit'];
            $ingredient->supplier = $_POST['supplier'];
            $ingredient->purchase_date = $_POST['purchase_date'];
            $ingredient->expiry_date = $_POST['expiry_date'];
            $ingredient->location = $_POST['location'];
            $ingredient->notes = $_POST['notes'];

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
}
?>