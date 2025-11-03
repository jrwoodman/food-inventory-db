<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Food & Ingredient Inventory Management System">
    <title>Food & Ingredient Inventory</title>
    <?php if (APP_FAVICON): ?>
    <link rel="icon" href="<?php echo APP_FAVICON; ?>" type="image/x-icon">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo" style="display: flex; align-items: center; gap: 0.75rem;">
                <?php if (APP_ICON): ?>
                    <img src="<?php echo APP_ICON; ?>" alt="<?php echo APP_NAME; ?>" style="width: 32px; height: 32px;">
                <?php endif; ?>
                <div style="display: flex; flex-direction: column; line-height: 1.2;">
                    <span><?php echo APP_TITLE; ?></span>
                    <?php if (APP_SUBTITLE): ?>
                        <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 400;"><?php echo APP_SUBTITLE; ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <nav class="nav">
                <a href="index.php?action=dashboard" class="active">📊 Dashboard</a>
                <?php if ($current_user->canEdit()): ?>
                    <a href="index.php?action=add_food">🍎 Add Food</a>
                    <a href="index.php?action=add_ingredient">🧄 Add Ingredient</a>
                    <a href="index.php?action=track_meal">🍴 Track Meal</a>
                <?php endif; ?>
                <a href="index.php?action=system_settings">⚙️ Settings</a>
                <a href="index.php?action=profile" style="display: flex; align-items: center; gap: 0.5rem;">
                    <img src="<?php echo $current_user->getGravatarUrl(64); ?>" 
                         alt="<?php echo htmlspecialchars($current_user->username); ?>" 
                         style="width: 40px; height: 40px; border-radius: 50%;">
                    <?php echo htmlspecialchars($current_user->username); ?>
                </a>
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
                <h3>🔍 Quick Search</h3>
                <p style="color: var(--text-muted); margin-bottom: 1rem;">Enter item names separated by commas to quickly locate items and view their storage locations.</p>
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
                
                <?php if (isset($search_results)): ?>
                    <?php if (!empty($search_results)): ?>
                    <div style="background: var(--card-bg); padding: 1rem; border-radius: 4px; border: 1px solid var(--border-color);">
                        <h4 style="margin-top: 0;">Search Results (<?php echo count($search_results); ?> locations found)</h4>
                        <div style="display: grid; gap: 0.75rem;">
                            <?php foreach ($search_results as $result): ?>
                                <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 4px; border-left: 3px solid <?php echo $result['type'] === 'food' ? '#4CAF50' : '#FF9800'; ?>;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                                        <div style="flex: 1;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                                <h5 style="margin: 0;">
                                                    <?php echo $result['type'] === 'food' ? '🍎' : '🧄'; ?> 
                                                    <?php echo htmlspecialchars($result['name']); ?>
                                                </h5>
                                                <span style="font-size: 0.75rem; padding: 0.125rem 0.5rem; background: var(--card-bg); border-radius: 12px; color: var(--text-muted);">
                                                    <?php echo ucfirst($result['type']); ?>
                                                </span>
                                            </div>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.5rem; font-size: 0.875rem; color: #b5bac1;">
                                                <div>
                                                    <span style="color: #CACACA;">📍 Location:</span> <?php echo htmlspecialchars($result['location'] ?? 'N/A'); ?>
                                                </div>
                                                <div>
                                                    <span style="color: #CACACA;">📦 Quantity:</span> <?php echo htmlspecialchars($result['quantity'] ?? '0'); ?> <?php echo htmlspecialchars($result['unit'] ?? ''); ?>
                                                </div>
                                                <?php if (!empty($result['category'])): ?>
                                                <div>
                                                    <span style="color: #CACACA;">🏷️ Category:</span> <?php echo htmlspecialchars($result['category']); ?>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($result['expiry_date'])): ?>
                                                <div>
                                                    <span style="color: #CACACA;">📅 Expires:</span> <?php echo date('M j, Y', strtotime($result['expiry_date'])); ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <a href="index.php?action=<?php echo $result['type'] === 'food' ? 'edit_food' : 'edit_ingredient'; ?>&id=<?php echo $result['id']; ?>" 
                                           class="btn btn-primary btn-sm" 
                                           style="white-space: nowrap; text-decoration: none;"
                                           title="Edit <?php echo htmlspecialchars($result['name']); ?>">
                                            ✏️ Edit
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="background: var(--card-bg); padding: 1.5rem; border-radius: 4px; border: 1px solid var(--border-color); text-align: center;">
                        <p style="color: var(--text-muted); margin: 0;">🔍 No items found matching your search.</p>
                    </div>
                    <?php endif; ?>
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
                <h3><span class="card-icon">⚠️</span> Expiring Soon / Expired</h3>
                <div class="expiring-items">
                    <?php
                    $expiring_count = 0;
                    if ($expiring_foods && is_object($expiring_foods)) {
                        while ($row = $expiring_foods->fetch(PDO::FETCH_ASSOC)): 
                            $expiring_count++;
                            $is_expired = ($row['status'] === 'expired');
                            $style = $is_expired ? 'color: #ff4444; font-weight: bold;' : '';
                            $icon = $is_expired ? ' 🚨' : '';
                        ?>
                            <a href="index.php?action=edit_food&id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                                <div class="expiring-item" style="<?php echo $style; ?>">
                                    <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                    <span class="expiry-date"><?php echo date('M j, Y', strtotime($row['expiry_date'])); ?><?php echo $icon; ?></span>
                                </div>
                            </a>
                        <?php endwhile;
                    }
                    if ($expiring_count == 0): ?>
                        <p class="no-items">No items expiring soon or expired!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Foods Alert -->
            <div class="card alert-card">
                <h3><span class="card-icon">🍎</span> Low Stock Foods</h3>
                <div class="low-stock-items">
                    <?php
                    $low_stock_foods_count = 0;
                    if (isset($low_stock_foods) && $low_stock_foods && is_object($low_stock_foods)) {
                        while ($row = $low_stock_foods->fetch(PDO::FETCH_ASSOC)): 
                            $low_stock_foods_count++;
                            $qty = $row['total_quantity'] ?? 0;
                            $is_zero = ($qty == 0);
                            $is_critical = ($qty > 0 && $qty <= CRITICAL_STOCK_THRESHOLD);
                            $is_low = ($qty > CRITICAL_STOCK_THRESHOLD && $qty <= LOW_STOCK_THRESHOLD);
                            
                            // Determine color and icon
                            $color = '';
                            $icon = '';
                            if ($is_zero) {
                                $color = 'color: #ff4444; font-weight: bold;';
                                $icon = ' 🚨';
                            } elseif ($is_critical) {
                                $color = 'color: #ff6b6b; font-weight: bold;';
                                $icon = ' ⚠️';
                            } elseif ($is_low) {
                                $color = 'color: #ffa500;';
                                $icon = ' ⚡';
                            }
                        ?>
                            <a href="index.php?action=edit_food&id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                                <div class="low-stock-item" style="<?php echo $color; ?>">
                                    <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                    <span class="quantity"><?php echo $qty . ' ' . $row['unit'] . $icon; ?></span>
                                </div>
                            </a>
                        <?php endwhile;
                    }
                    if ($low_stock_foods_count == 0): ?>
                        <p class="no-items">All foods well stocked!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Ingredients Alert -->
            <div class="card alert-card">
                <h3><span class="card-icon">🧄</span> Low Stock Ingredients</h3>
                <div class="low-stock-items">
                    <?php
                    $low_stock_count = 0;
                    if ($low_stock_ingredients && is_object($low_stock_ingredients)) {
                        while ($row = $low_stock_ingredients->fetch(PDO::FETCH_ASSOC)): 
                            $low_stock_count++;
                            $qty = $row['total_quantity'] ?? 0;
                            $is_zero = ($qty == 0);
                            $is_critical = ($qty > 0 && $qty <= CRITICAL_STOCK_THRESHOLD);
                            $is_low = ($qty > CRITICAL_STOCK_THRESHOLD && $qty <= LOW_STOCK_THRESHOLD);
                            
                            // Determine color and icon
                            $color = '';
                            $icon = '';
                            if ($is_zero) {
                                $color = 'color: #ff4444; font-weight: bold;';
                                $icon = ' 🚨';
                            } elseif ($is_critical) {
                                $color = 'color: #ff6b6b; font-weight: bold;';
                                $icon = ' ⚠️';
                            } elseif ($is_low) {
                                $color = 'color: #ffa500;';
                                $icon = ' ⚡';
                            }
                        ?>
                            <a href="index.php?action=edit_ingredient&id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                                <div class="low-stock-item" style="<?php echo $color; ?>">
                                    <span class="item-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                    <span class="quantity"><?php echo $qty . ' ' . $row['unit'] . $icon; ?></span>
                                </div>
                            </a>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0;"><span class="card-icon">🧄</span> Ingredients</h3>
                <div id="ingredients-pagination-controls" style="display: flex; gap: 0.5rem; align-items: center;"></div>
            </div>
            <div class="table-container">
                <table class="inventory-table" id="ingredients-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <?php if ($show_all_groups): ?><th>Group</th><?php endif; ?>
                            <th>Total Quantity</th>
                            <th>Allergens</th>
                            <th>Supplier</th>
                            <th>Expiry Date</th>
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
                                <tr data-brand="<?php echo htmlspecialchars($row['supplier'] ?? ''); ?>" data-category="<?php echo htmlspecialchars($row['category'] ?? ''); ?>">
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category'] ?? ''); ?></td>
                                    <?php if ($show_all_groups): ?><td><?php echo htmlspecialchars($row['group_name'] ?? 'No Group'); ?></td><?php endif; ?>
                                    <td><?php echo ($row['total_quantity'] ?? 0) . ' ' . $row['unit']; ?></td>
                                    <td>
                                        <?php
                                        $allergens = [];
                                        if (!empty($row['contains_gluten'])) $allergens[] = '<span class="badge badge-warning" style="background: #ff9800; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-right: 0.25rem;">Gluten</span>';
                                        if (!empty($row['contains_milk'])) $allergens[] = '<span class="badge badge-info" style="background: #2196F3; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-right: 0.25rem;">Milk</span>';
                                        if (!empty($row['contains_soy'])) $allergens[] = '<span class="badge badge-success" style="background: #4CAF50; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-right: 0.25rem;">Soy</span>';
                                        if (!empty($row['contains_nuts'])) $allergens[] = '<span class="badge badge-danger" style="background: #d32f2f; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-right: 0.25rem;">Nuts</span>';
                                        echo !empty($allergens) ? implode('', $allergens) : '—';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['supplier'] ?? ''); ?></td>
                                    <td><?php echo $row['expiry_date'] ? date('M j, Y', strtotime($row['expiry_date'])) : 'N/A'; ?></td>
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
                            <tr><td colspan="<?php echo $show_all_groups ? '8' : '7'; ?>" class="no-items">No ingredients found. <?php if ($current_user->canEdit()): ?><a href="index.php?action=add_ingredient">Add your first ingredient!</a><?php endif; ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Foods Section (Full Width) -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="margin: 0;"><span class="card-icon">🍎</span> Food Items</h3>
                    <div id="foods-pagination-controls" style="display: flex; gap: 0.5rem; align-items: center;"></div>
                </div>
                <div class="table-container">
                    <table class="inventory-table" id="foods-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <?php if ($show_all_groups): ?><th>Group</th><?php endif; ?>
                                <th>Total Quantity</th>
                                <th>Allergens</th>
                                <th>Brand</th>
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
                                <tr data-brand="<?php echo htmlspecialchars($row['brand'] ?? ''); ?>" data-category="<?php echo htmlspecialchars($row['category'] ?? ''); ?>">
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <?php if ($show_all_groups): ?><td><?php echo htmlspecialchars($row['group_name'] ?? 'No Group'); ?></td><?php endif; ?>
                                        <td><?php echo ($row['total_quantity'] ?? 0) . ' ' . $row['unit']; ?></td>
                                        <td>
                                            <?php
                                            $allergens = [];
                                            if (!empty($row['contains_gluten'])) $allergens[] = '<span class="badge badge-warning" style="background: #ff9800; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-right: 0.25rem;">Gluten</span>';
                                            if (!empty($row['contains_milk'])) $allergens[] = '<span class="badge badge-info" style="background: #2196F3; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-right: 0.25rem;">Milk</span>';
                                            if (!empty($row['contains_soy'])) $allergens[] = '<span class="badge badge-success" style="background: #4CAF50; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-right: 0.25rem;">Soy</span>';
                                            if (!empty($row['contains_nuts'])) $allergens[] = '<span class="badge badge-danger" style="background: #d32f2f; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-right: 0.25rem;">Nuts</span>';
                                            echo !empty($allergens) ? implode('', $allergens) : '—';
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['brand'] ?? ''); ?></td>
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
                                <tr><td colspan="<?php echo $show_all_groups ? '8' : '7'; ?>" class="no-items">No food items found. <?php if ($current_user->canEdit()): ?><a href="index.php?action=add_food">Add your first food item!</a><?php endif; ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    </div>

    <script>
        // Limit items in alert widgets with show more/less functionality
        document.addEventListener('DOMContentLoaded', function() {
            const itemsPerPage = <?php echo DASHBOARD_ALERT_ITEMS_PER_PAGE; ?>; // Configurable in config.php: set to 0 for no limit
            
            function setupShowMore(containerSelector, itemSelector) {
                const container = document.querySelector(containerSelector);
                if (!container) return;
                
                const items = Array.from(container.querySelectorAll(itemSelector));
                if (items.length <= itemsPerPage || itemsPerPage === 0) return;
                
                // Hide items beyond the limit
                items.forEach((item, index) => {
                    if (index >= itemsPerPage) {
                        item.style.display = 'none';
                        item.classList.add('hidden-item');
                    }
                });
                
                // Add show more button
                const showMoreBtn = document.createElement('button');
                showMoreBtn.className = 'btn btn-secondary btn-sm';
                showMoreBtn.style.cssText = 'width: 100%; margin-top: 0.5rem;';
                showMoreBtn.textContent = `Show All (${items.length - itemsPerPage} more)`;
                
                let showingAll = false;
                showMoreBtn.onclick = function() {
                    showingAll = !showingAll;
                    items.forEach((item, index) => {
                        if (index >= itemsPerPage) {
                            item.style.display = showingAll ? '' : 'none';
                        }
                    });
                    showMoreBtn.textContent = showingAll ? 'Show Less' : `Show All (${items.length - itemsPerPage} more)`;
                };
                
                container.appendChild(showMoreBtn);
            }
            
            // Apply to all alert widgets (there are multiple low-stock-items containers)
            setupShowMore('.expiring-items', '.expiring-item');
            
            // Handle each low-stock-items container separately
            document.querySelectorAll('.low-stock-items').forEach((container) => {
                const items = Array.from(container.querySelectorAll('.low-stock-item'));
                if (items.length <= itemsPerPage || itemsPerPage === 0) return;
                
                // Hide items beyond the limit
                items.forEach((item, idx) => {
                    if (idx >= itemsPerPage) {
                        item.style.display = 'none';
                    }
                });
                
                // Add show more button
                const showMoreBtn = document.createElement('button');
                showMoreBtn.className = 'btn btn-secondary btn-sm';
                showMoreBtn.style.cssText = 'width: 100%; margin-top: 0.5rem;';
                showMoreBtn.textContent = `Show All (${items.length - itemsPerPage} more)`;
                
                let showingAll = false;
                showMoreBtn.onclick = function() {
                    showingAll = !showingAll;
                    items.forEach((item, idx) => {
                        if (idx >= itemsPerPage) {
                            item.style.display = showingAll ? '' : 'none';
                        }
                    });
                    showMoreBtn.textContent = showingAll ? 'Show Less' : `Show All (${items.length - itemsPerPage} more)`;
                };
                
                container.appendChild(showMoreBtn);
            });
            
            // Table pagination
            const tableItemsPerPage = <?php echo DASHBOARD_TABLE_ITEMS_PER_PAGE; ?>;
            
            function setupTablePagination(tableId, paginationControlsId) {
                const table = document.getElementById(tableId);
                if (!table || tableItemsPerPage === 0) return;
                
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.querySelector('.no-items'));
                
                if (rows.length <= tableItemsPerPage) return;
                
                let currentPage = 1;
                const totalPages = Math.ceil(rows.length / tableItemsPerPage);
                
                function showPage(page) {
                    currentPage = page;
                    const start = (page - 1) * tableItemsPerPage;
                    const end = start + tableItemsPerPage;
                    
                    rows.forEach((row, index) => {
                        row.style.display = (index >= start && index < end) ? '' : 'none';
                    });
                    
                    updatePaginationControls();
                }
                
                function updatePaginationControls() {
                    const controls = document.getElementById(paginationControlsId);
                    controls.innerHTML = `
                        <span style="color: var(--text-muted); font-size: 0.9rem;">Page ${currentPage} of ${totalPages} (${rows.length} items)</span>
                        <button class="btn btn-sm prev-btn" style="${currentPage === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''}">&laquo; Prev</button>
                        <button class="btn btn-sm next-btn" style="${currentPage === totalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''}">Next &raquo;</button>
                    `;
                    
                    // Attach event listeners
                    const prevBtn = controls.querySelector('.prev-btn');
                    const nextBtn = controls.querySelector('.next-btn');
                    
                    prevBtn.onclick = function() {
                        if (currentPage > 1) showPage(currentPage - 1);
                    };
                    
                    nextBtn.onclick = function() {
                        if (currentPage < totalPages) showPage(currentPage + 1);
                    };
                }
                
                showPage(1);
            }
            
            setupTablePagination('ingredients-table', 'ingredients-pagination-controls');
            setupTablePagination('foods-table', 'foods-pagination-controls');
        });
    </script>
    <script src="../assets/js/app.js"></script>
    <script>
        // Pass configuration to nutrition.js
        window.NUTRITION_LINK_MODE = '<?php echo USDA_NUTRITION_LINK_MODE; ?>';
    </script>
    <script src="../assets/js/nutrition.js"></script>
</body>
</html>
