<?php
require_once '../config/config.php';
require_once '../src/database/Database.php';
require_once '../src/models/Store.php';

$database = new Database();
$db = $database->getConnection();

echo "Testing Store readOne method:\n\n";

// Get a store ID from the database
$query = "SELECT id FROM stores LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "No stores found in database!\n";
    exit;
}

$store_id = $row['id'];
echo "Testing with store ID: $store_id\n\n";

// Test readOne
$store = new Store($db);
$store->id = $store_id;

echo "Before readOne():\n";
echo "- name: " . ($store->name ?? 'NULL') . "\n";
echo "- address: " . ($store->address ?? 'NULL') . "\n";
echo "- phone: " . ($store->phone ?? 'NULL') . "\n\n";

$result = $store->readOne();

echo "readOne() returned: " . ($result ? 'TRUE' : 'FALSE') . "\n\n";

echo "After readOne():\n";
echo "- name: " . ($store->name ?? 'NULL') . "\n";
echo "- address: " . ($store->address ?? 'NULL') . "\n";
echo "- phone: " . ($store->phone ?? 'NULL') . "\n";
echo "- website: " . ($store->website ?? 'NULL') . "\n";
echo "- notes: " . ($store->notes ?? 'NULL') . "\n";
echo "- is_active: " . ($store->is_active ?? 'NULL') . "\n";

// Direct query to verify data exists
echo "\n\nDirect database query:\n";
$query = "SELECT * FROM stores WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$store_id]);
$direct = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($direct);
?>
