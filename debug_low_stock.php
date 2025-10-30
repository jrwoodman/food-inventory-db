<?php
// Debug script to test low stock query
require_once 'config/config.php';
require_once 'src/database/Database.php';
require_once 'src/models/Ingredient.php';

$database = new Database();
$db = $database->getConnection();

echo "LOW_STOCK_THRESHOLD constant: " . LOW_STOCK_THRESHOLD . "\n\n";

$ingredient = new Ingredient($db);

echo "Testing getLowStockItems with threshold " . LOW_STOCK_THRESHOLD . ":\n";
echo "Type of threshold: " . gettype(LOW_STOCK_THRESHOLD) . "\n";
echo "Value of threshold: " . var_export(LOW_STOCK_THRESHOLD, true) . "\n\n";
$stmt = $ingredient->getLowStockItems(LOW_STOCK_THRESHOLD);

$count = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $count++;
    echo "- " . $row['name'] . ": " . $row['total_quantity'] . " " . $row['unit'] . "\n";
}

if ($count == 0) {
    echo "No low stock items found (correct if all quantities > " . LOW_STOCK_THRESHOLD . ")\n";
}

echo "\n\nTesting direct SQL query:\n";
$query = "SELECT i.*, COALESCE(SUM(il.quantity), 0) as total_quantity
          FROM ingredients i
          LEFT JOIN ingredient_locations il ON i.id = il.ingredient_id
          GROUP BY i.id
          ORDER BY i.name";
$stmt2 = $db->prepare($query);
$stmt2->execute();

while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    $low = $row['total_quantity'] <= LOW_STOCK_THRESHOLD ? " [LOW STOCK]" : "";
    echo "- " . $row['name'] . ": " . $row['total_quantity'] . " " . $row['unit'] . $low . "\n";
}
?>
