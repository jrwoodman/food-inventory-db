<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Food Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <style>
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        .tab {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 1rem;
            color: var(--text-muted);
            transition: all 0.2s;
        }
        .tab:hover {
            color: var(--text-color);
        }
        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo">🍽️ Food Inventory</a>
            <nav class="nav">
                <a href="index.php?action=dashboard">📊 Dashboard</a>
                <a href="index.php?action=user_management">👥 Users & Groups</a>
                <a href="index.php?action=system_settings" class="active">⚙️ System Settings</a>
                <a href="index.php?action=profile">⚙️ Profile</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>⚙️ System Settings</h1>
        
        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="showTab('units')">📏 Units</button>
            <button class="tab" onclick="showTab('categories')">🏷️ Categories</button>
            <button class="tab" onclick="showTab('stores')">🏪 Stores</button>
            <button class="tab" onclick="showTab('locations')">📍 Locations</button>
        </div>

        <!-- Units Tab -->
        <div id="units-tab" class="tab-content active">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Measurement Units</h3>
                    <a href="index.php?action=add_unit" class="btn btn-success">+ Add Unit</a>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Abbreviation</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $unit_count = 0;
                            foreach ($units as $row): 
                                $unit_count++;
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['abbreviation']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($row['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-actions">
                                        <a href="index.php?action=edit_unit&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary">✏️ Edit</a>
                                        <a href="index.php?action=toggle_unit_status&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-secondary">
                                            <?php echo $row['is_active'] ? '🚫 Deactivate' : '✅ Activate'; ?>
                                        </a>
                                        <a href="index.php?action=delete_unit&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this unit?');">🗑️ Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($unit_count == 0): ?>
                                <tr><td colspan="5" class="no-items">No units found. <a href="index.php?action=add_unit">Add your first unit!</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Categories Tab -->
        <div id="categories-tab" class="tab-content">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Food & Ingredient Categories</h3>
                    <a href="index.php?action=add_category" class="btn btn-success">+ Add Category</a>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h4>🍎 Food Categories</h4>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $food_count = 0;
                                foreach ($food_categories as $row): 
                                    $food_count++;
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['description'] ?? '-'); ?></td>
                                        <td class="table-actions">
                                            <a href="index.php?action=edit_category&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-primary">✏️ Edit</a>
                                            <a href="index.php?action=delete_category&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this category?');">🗑️ Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($food_count == 0): ?>
                                    <tr><td colspan="3" class="no-items">No food categories found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h4>🧄 Ingredient Categories</h4>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ingredient_count = 0;
                                foreach ($ingredient_categories as $row): 
                                    $ingredient_count++;
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['description'] ?? '-'); ?></td>
                                        <td class="table-actions">
                                            <a href="index.php?action=edit_category&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-primary">✏️ Edit</a>
                                            <a href="index.php?action=delete_category&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this category?');">🗑️ Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($ingredient_count == 0): ?>
                                    <tr><td colspan="3" class="no-items">No ingredient categories found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stores Tab -->
        <div id="stores-tab" class="tab-content">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Store Locations</h3>
                    <a href="index.php?action=add_store" class="btn btn-success">+ Add Store</a>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Store Name</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Website</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $store_count = 0;
                            foreach ($stores as $row): 
                                $store_count++;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                        <?php if(!empty($row['notes'])): ?>
                                            <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;"><?php echo htmlspecialchars($row['notes']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo !empty($row['address']) ? htmlspecialchars($row['address']) : '-'; ?></td>
                                    <td><?php echo !empty($row['phone']) ? htmlspecialchars($row['phone']) : '-'; ?></td>
                                    <td>
                                        <?php if(!empty($row['website'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['website']); ?>" target="_blank" style="color: var(--accent-primary);">
                                                Visit →
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                    <td class="table-actions">
                                        <a href="index.php?action=edit_store&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">✏️ Edit</a>
                                        <a href="index.php?action=toggle_store_status&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-secondary"
                                           onclick="return confirm('Toggle store status?');">
                                            <?php echo $row['is_active'] ? '🚫 Deactivate' : '✅ Activate'; ?>
                                        </a>
                                        <?php if(!$row['is_active']): ?>
                                            <a href="index.php?action=delete_store&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Permanently delete this store?');">
                                                🗑️ Delete
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if($store_count == 0): ?>
                                <tr>
                                    <td colspan="7" class="no-items">
                                        No stores found. <a href="index.php?action=add_store">Add your first store!</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Locations Tab -->
        <div id="locations-tab" class="tab-content">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Storage Locations</h3>
                    <a href="index.php?action=add_location" class="btn btn-success">+ Add Location</a>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Items Using</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $location_count = 0;
                            foreach ($locations as $row): 
                                $location_count++;
                                $total_items = $row['food_count'] + $row['ingredient_count'];
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['description'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($row['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $total_items; ?> items (<?php echo $row['food_count']; ?> foods, <?php echo $row['ingredient_count']; ?> ingredient locations)</td>
                                    <td class="table-actions">
                                        <a href="index.php?action=edit_location&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary">✏️ Edit</a>
                                        <a href="index.php?action=toggle_location_status&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-secondary">
                                            <?php echo $row['is_active'] ? '🚫 Deactivate' : '✅ Activate'; ?>
                                        </a>
                                        <a href="index.php?action=delete_location&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this location?');">🗑️ Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($location_count == 0): ?>
                                <tr><td colspan="5" class="no-items">No locations found. <a href="index.php?action=add_location">Add your first location!</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="margin-top: 2rem;">
            <a href="index.php?action=dashboard" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
