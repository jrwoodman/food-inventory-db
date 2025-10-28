<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Food & Ingredient Inventory Management System">
    <title>Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo">🍽️ Food Inventory</a>
            <nav class="nav">
                <?php if ($current_user->canEdit()): ?>
                    <a href="index.php?action=add_food">🍎 Add Food</a>
                    <a href="index.php?action=add_ingredient">🧄 Add Ingredient</a>
                    <a href="index.php?action=track_meal">🍴 Track Meal</a>
                <?php endif; ?>
                <a href="index.php?action=list_groups">👥 Groups</a>
                <?php if ($current_user->isAdmin()): ?>
                    <a href="index.php?action=users">👤 Users</a>
                    <a href="index.php?action=manage_locations">📍 Locations</a>
                    <a href="index.php?action=manage_stores">🏪 Stores</a>
                <?php endif; ?>
                <a href="index.php?action=profile">⚙️ Profile</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">

        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        
        <?php if ($current_user->canEdit()): ?>
            <!-- Multi-Search Widget -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <h3>🔍 Quick Search & Update</h3>
                <p style="color: var(--text-muted); margin-bottom: 1rem;">Enter item names separated by commas to search and update multiple items at once.</p>
                <form method="GET" action="index.php" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <input type="hidden" name="action" value="bulk_search">
                    <input type="text" 
                           name="search_items" 
                           id="search_items"
                           placeholder="e.g. Milk, Bread, Eggs, Flour"
                           style="flex: 1; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);"
                           value="<?php echo isset($_GET['search_items']) ? htmlspecialchars($_GET['search_items']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                
                <?php if (isset($search_results) && !empty($search_results)): ?>
                    <div style="background: var(--card-bg); padding: 1rem; border-radius: 4px; border: 1px solid var(--border-color);">
                        <h4 style="margin-top: 0;">Search Results (<?php echo count($search_results); ?> items found)</h4>
                        <form method="POST" action="index.php?action=bulk_update" id="bulk-update-form">
                            <?php foreach ($search_results as $result): ?>
                                <div style="background: var(--bg-secondary); padding: 1rem; margin-bottom: 1rem; border-radius: 4px; border-left: 3px solid <?php echo $result['type'] === 'food' ? '#4CAF50' : '#FF9800'; ?>;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                        <h5 style="margin: 0;">
                                            <?php echo $result['type'] === 'food' ? '🍎' : '🧄'; ?> 
                                            <?php echo htmlspecialchars($result['name']); ?>
                                            <span style="font-size: 0.875rem; color: var(--text-muted); font-weight: normal;">(<?php echo ucfirst($result['type']); ?>)</span>
                                        </h5>
                                    </div>
                                    
                                    <input type="hidden" name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][type]" value="<?php echo $result['type']; ?>">
                                    <input type="hidden" name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][id]" value="<?php echo $result['id']; ?>">
                                    
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 0.875rem;">Quantity</label>
                                            <input type="number" 
                                                   step="0.01"
                                                   name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][quantity]" 
                                                   value="<?php echo $result['quantity']; ?>"
                                                   style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                                        </div>
                                        
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 0.875rem;">Unit</label>
                                            <select name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][unit]"
                                                    style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                                                <option value="">Select unit...</option>
                                                <?php if (isset($units)): foreach ($units as $unit_option): ?>
                                                    <option value="<?php echo htmlspecialchars($unit_option); ?>" 
                                                            <?php echo ($result['unit'] == $unit_option) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($unit_option); ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 0.875rem;">Category</label>
                                            <select name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][category]"
                                                    style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                                                <option value="">Select category...</option>
                                                <?php if (isset($categories)): foreach ($categories as $cat_option): ?>
                                                    <option value="<?php echo htmlspecialchars($cat_option); ?>" 
                                                            <?php echo ($result['category'] == $cat_option) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat_option); ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 0.875rem;"><?php echo $result['type'] === 'food' ? 'Expiry Date' : 'Expiry Date'; ?></label>
                                            <input type="date" 
                                                   name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][expiry_date]" 
                                                   value="<?php echo $result['expiry_date'] ?? ''; ?>"
                                                   style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                                        </div>
                                        
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 0.875rem;">Purchase Date</label>
                                            <input type="date" 
                                                   name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][purchase_date]" 
                                                   value="<?php echo date('Y-m-d'); ?>"
                                                   style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                                        </div>
                                        
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 0.875rem;">Purchase Location</label>
                                            <select name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][purchase_location]"
                                                    style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                                                <option value="">Select store...</option>
                                                <?php if (isset($stores)): foreach ($stores as $store): ?>
                                                    <option value="<?php echo htmlspecialchars($store['name']); ?>" 
                                                            <?php echo ($result['purchase_location'] == $store['name']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($store['name']); ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                        
                                        <?php if ($result['type'] === 'food'): ?>
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 0.875rem;">Storage Location</label>
                                            <select name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][location]"
                                                    style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                                                <option value="">Select location...</option>
                                                <?php if (isset($locations)): foreach ($locations as $location): ?>
                                                    <option value="<?php echo htmlspecialchars($location['name']); ?>" 
                                                            <?php echo ($result['location'] == $location['name']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($location['name']); ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                        <?php else: ?>
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 0.875rem;">Supplier</label>
                                            <input type="text" 
                                                   name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][supplier]" 
                                                   value="<?php echo htmlspecialchars($result['supplier'] ?? ''); ?>"
                                                   style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                                        </div>
                                        
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 0.875rem;">Cost/Unit</label>
                                            <input type="number" 
                                                   step="0.01"
                                                   name="items[<?php echo $result['type'] . '_' . $result['id']; ?>][cost_per_unit]" 
                                                   value="<?php echo $result['cost_per_unit'] ?? ''; ?>"
                                                   style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div style="display: flex; gap: 0.5rem; justify-content: end; margin-top: 1rem;">
                                <a href="index.php?action=dashboard" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-success">💾 Update All Items</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($current_user->isAdmin() && isset($all_groups)): ?>
            <div class="card" style="margin-bottom: 1.5rem;">
                <form method="GET" action="index.php" style="display: flex; gap: 0.5rem; align-items: end;">
                    <input type="hidden" name="action" value="dashboard">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label for="group_filter">Filter by Group</label>
                        <select id="group_filter" name="group_filter" onchange="this.form.submit()">
                            <option value="all" <?php echo ($show_all_groups) ? 'selected' : ''; ?>>All Groups</option>
                            <?php foreach ($all_groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>" <?php echo ($filter_group_id == $group['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if (!$current_user->isAdmin() && (!$foods || !is_object($foods)) && (!$ingredients || !is_object($ingredients))): ?>
            <div class="alert alert-info">
                <strong>Welcome!</strong> You're not a member of any groups yet. 
                <?php if ($current_user->canEdit()): ?>
                <a href="index.php?action=create_group">Create a group</a> or ask an administrator to add you to an existing group to start managing inventory.
                <?php else: ?>
                Please ask an administrator to add you to a group to access inventory.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Alert Cards Row -->
        <div class="dashboard-grid">
            <!-- Expiring Foods Alert -->
            <div class="card alert-card">
                <h3><span class="card-icon">⚠️</span> Expiring Soon (Next 7 Days)</h3>
                <div class="expiring-items">
                    <?php
                    $expiring_count = 0;
                    if ($expiring_foods && is_object($expiring_foods)) {
                        while ($row = $expiring_foods->fetch(PDO::FETCH_ASSOC)): 
                            $expiring_count++;
                        ?>
                            <div class="expiring-item">
                                <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                <span class="expiry-date"><?php echo date('M j, Y', strtotime($row['expiry_date'])); ?></span>
                            </div>
                        <?php endwhile;
                    }
                    if ($expiring_count == 0): ?>
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
                    if ($low_stock_ingredients && is_object($low_stock_ingredients)) {
                        while ($row = $low_stock_ingredients->fetch(PDO::FETCH_ASSOC)): 
                            $low_stock_count++;
                        ?>
                            <div class="low-stock-item">
                                <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                <span class="quantity"><?php echo ($row['total_quantity'] ?? 0) . ' ' . $row['unit']; ?></span>
                            </div>
                        <?php endwhile;
                    }
                    if ($low_stock_count == 0): ?>
                        <p class="no-items">All ingredients well stocked!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ingredients Section (Full Width) -->
        <div class="card">
            <h3><span class="card-icon">🧄</span> Ingredients</h3>
            <div class="table-container">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <?php if ($show_all_groups): ?><th>Group</th><?php endif; ?>
                            <th>Total Quantity</th>
                            <th>Cost/Unit</th>
                            <th>Supplier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ingredient_count = 0;
                        if ($ingredients && is_object($ingredients)) {
                            while ($row = $ingredients->fetch(PDO::FETCH_ASSOC)): 
                                $ingredient_count++;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category'] ?? ''); ?></td>
                                    <?php if ($show_all_groups): ?><td><?php echo htmlspecialchars($row['group_name'] ?? 'No Group'); ?></td><?php endif; ?>
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
                            <?php endwhile;
                        }
                        if ($ingredient_count == 0): ?>
                            <tr><td colspan="<?php echo $show_all_groups ? '7' : '6'; ?>" class="no-items">No ingredients found. <?php if ($current_user->canEdit()): ?><a href="index.php?action=add_ingredient">Add your first ingredient!</a><?php endif; ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Foods Section (Full Width) -->
            <div class="card">
                <h3><span class="card-icon">🍎</span> Food Items</h3>
                <div class="table-container">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <?php if ($show_all_groups): ?><th>Group</th><?php endif; ?>
                                <th>Quantity</th>
                                <th>Location</th>
                                <th>Expiry Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $food_count = 0;
                            if ($foods && is_object($foods)) {
                                while ($row = $foods->fetch(PDO::FETCH_ASSOC)): 
                                    $food_count++;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <?php if ($show_all_groups): ?><td><?php echo htmlspecialchars($row['group_name'] ?? 'No Group'); ?></td><?php endif; ?>
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
                                <?php endwhile;
                            }
                            if ($food_count == 0): ?>
                                <tr><td colspan="<?php echo $show_all_groups ? '7' : '6'; ?>" class="no-items">No food items found. <?php if ($current_user->canEdit()): ?><a href="index.php?action=add_food">Add your first food item!</a><?php endif; ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>