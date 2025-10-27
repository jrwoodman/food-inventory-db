<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Food & Ingredient Inventory Management System">
    <title>Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>🍽️ Food & Ingredient Inventory</h1>
            </div>
            <div class="nav-actions">
                <nav>
                    <a href="index.php?action=dashboard" class="btn btn-primary">📊 Dashboard</a>
                    <?php if ($current_user->canEdit()): ?>
                        <a href="index.php?action=add_food" class="btn btn-success">🍎 Add Food</a>
                        <a href="index.php?action=add_ingredient" class="btn btn-success">🧄 Add Ingredient</a>
                    <?php endif; ?>
                    <?php if ($current_user->isAdmin()): ?>
                        <a href="index.php?action=users" class="btn btn-secondary">👥 Users</a>
                        <a href="index.php?action=manage_stores" class="btn btn-secondary">🏪 Stores</a>
                    <?php endif; ?>
                </nav>
                <div class="user-nav">
                    <div class="user-info-nav">
                        <span class="user-greeting">Welcome, <?php echo htmlspecialchars($current_user->first_name ?: $current_user->username); ?>!</span>
                        <div class="user-dropdown">
                            <a href="index.php?action=profile" class="btn btn-ghost btn-sm">👤 Profile</a>
                            <a href="index.php?action=logout" class="btn btn-ghost btn-sm">🚪 Logout</a>
                        </div>
                    </div>
                    <div class="theme-toggle">
                        <span class="theme-toggle-label">Theme</span>
                        <label class="theme-switch">
                            <input type="checkbox" id="theme-toggle">
                            <span class="theme-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
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
                <h3><span class="card-icon">⚠️</span> Expiring Soon (Next 7 Days)</h3>
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
                <h3><span class="card-icon">📉</span> Low Stock Ingredients</h3>
                <div class="low-stock-items">
                    <?php
                    $low_stock_count = 0;
                    while ($row = $low_stock_ingredients->fetch(PDO::FETCH_ASSOC)): 
                        $low_stock_count++;
                    ?>
                        <div class="low-stock-item">
                            <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
                            <span class="quantity"><?php echo ($row['total_quantity'] ?? 0) . ' ' . $row['unit']; ?></span>
                        </div>
                    <?php endwhile; ?>
                    <?php if ($low_stock_count == 0): ?>
                        <p class="no-items">All ingredients well stocked!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Foods Inventory -->
            <div class="card">
                <h3><span class="card-icon">🍎</span> Food Items</h3>
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
                                    <td class="table-actions">
                                        <a href="index.php?action=edit_food&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit <?php echo htmlspecialchars($row['name']); ?>">
                                           ✏️ Edit
                                        </a>
                                        <a href="index.php?action=delete_food&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Delete <?php echo htmlspecialchars($row['name']); ?>"
                                           onclick="return confirm('Are you sure you want to delete this item?')">
                                           🗑️ Delete
                                        </a>
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
                <h3><span class="card-icon">🧄</span> Ingredients</h3>
                <div class="table-container">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Total Quantity</th>
                                <th>Cost/Unit</th>
                                <th>Supplier</th>
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
                                    <td><?php echo htmlspecialchars($row['category'] ?? ''); ?></td>
                                    <td><?php echo ($row['total_quantity'] ?? 0) . ' ' . $row['unit']; ?></td>
                                    <td><?php echo isset($row['cost_per_unit']) && $row['cost_per_unit'] ? '$' . number_format($row['cost_per_unit'], 2) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($row['supplier'] ?? ''); ?></td>
                                    <td class="table-actions">
                                        <a href="index.php?action=edit_ingredient&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit <?php echo htmlspecialchars($row['name']); ?>">
                                           ✏️ Edit
                                        </a>
                                        <a href="index.php?action=delete_ingredient&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Delete <?php echo htmlspecialchars($row['name']); ?>"
                                           onclick="return confirm('Are you sure you want to delete this item?')">
                                           🗑️ Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($ingredient_count == 0): ?>
                                <tr><td colspan="6" class="no-items">No ingredients found. <a href="index.php?action=add_ingredient">Add your first ingredient!</a></td></tr>
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