<?php
require_once '../src/database/Database.php';
require_once '../src/models/Food.php';
require_once '../src/models/Ingredient.php';
require_once '../src/controllers/InventoryController.php';
require_once '../config/config.php';

// Start session
session_start();

// Simple routing
$action = $_GET['action'] ?? 'dashboard';
$controller = new InventoryController();

switch ($action) {
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
    case 'api_foods':
        $controller->getFoodsJson();
        break;
    case 'api_ingredients':
        $controller->getIngredientsJson();
        break;
    default:
        $controller->dashboard();
}
?>