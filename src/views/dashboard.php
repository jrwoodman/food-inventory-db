<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Food & Ingredient Inventory Database</h1>
            <nav>
                <a href="index.php?action=dashboard" class="btn btn-primary">Dashboard</a>
                <a href="index.php?action=add_food" class="btn btn-success">Add Food</a>
                <a href="index.php?action=add_ingredient" class="btn btn-success">Add Ingredient</a>
            </nav>
        </header>

        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Expiring Foods Alert -->
            <div class="card alert-card">
                <h3>⚠️ Expiring Soon (Next 7 Days)</h3>
                <div class="expiring-items">
                    <?php
                    $expiring_count = 0;
                    while ($row = $expiring_foods->fetch(PDO::FETCH_ASSOC)): 
                        $expiring_count++;
                    ?>
                        <div class="expiring-item">
                            <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
                            <span class="expiry-date"><?php echo date('M j, Y', strtotime($row['expiry_date'])); ?></span>
                        </div>
                    <?php endwhile; ?>
                    <?php if ($expiring_count == 0): ?>
                        <p class="no-items">No items expiring soon!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="card alert-card">
                <h3>📉 Low Stock Ingredients</h3>
                <div class="low-stock-items">
                    <?php
                    $low_stock_count = 0;
                    while ($row = $low_stock_ingredients->fetch(PDO::FETCH_ASSOC)): 
                        $low_stock_count++;
                    ?>
                        <div class="low-stock-item">
                            <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
                            <span class="quantity"><?php echo $row['quantity'] . ' ' . $row['unit']; ?></span>
                        </div>
                    <?php endwhile; ?>
                    <?php if ($low_stock_count == 0): ?>
                        <p class="no-items">All ingredients well stocked!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Foods Inventory -->
            <div class="card">
                <h3>🍎 Food Items</h3>
                <div class="table-container">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Location</th>
                                <th>Expiry Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $food_count = 0;
                            while ($row = $foods->fetch(PDO::FETCH_ASSOC)): 
                                $food_count++;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo $row['quantity'] . ' ' . $row['unit']; ?></td>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td><?php echo $row['expiry_date'] ? date('M j, Y', strtotime($row['expiry_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <a href="index.php?action=edit_food&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="index.php?action=delete_food&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($food_count == 0): ?>
                                <tr><td colspan="6" class="no-items">No food items found. <a href="index.php?action=add_food">Add your first food item!</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ingredients Inventory -->
            <div class="card">
                <h3>🧄 Ingredients</h3>
                <div class="table-container">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Cost/Unit</th>
                                <th>Supplier</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ingredient_count = 0;
                            while ($row = $ingredients->fetch(PDO::FETCH_ASSOC)): 
                                $ingredient_count++;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo $row['quantity'] . ' ' . $row['unit']; ?></td>
                                    <td><?php echo $row['cost_per_unit'] ? '$' . number_format($row['cost_per_unit'], 2) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($row['supplier']); ?></td>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td>
                                        <a href="index.php?action=edit_ingredient&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="index.php?action=delete_ingredient&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($ingredient_count == 0): ?>
                                <tr><td colspan="7" class="no-items">No ingredients found. <a href="index.php?action=add_ingredient">Add your first ingredient!</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>