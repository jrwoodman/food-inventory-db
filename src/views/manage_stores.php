<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Manage Stores - Food & Ingredient Inventory Management">
    <title>Manage Stores - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>🏪 Store Management</h1>
            </div>
            <div class="nav-actions">
                <nav>
                    <a href="index.php?action=dashboard" class="btn btn-primary">← Back to Dashboard</a>
                    <a href="index.php?action=add_store" class="btn btn-success">+ Add Store</a>
                </nav>
                <div class="theme-toggle">
                    <span class="theme-toggle-label">Theme</span>
                    <label class="theme-switch">
                        <input type="checkbox" id="theme-toggle">
                        <span class="theme-slider"></span>
                    </label>
                </div>
            </div>
        </header>

        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="stores-container">
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $total_stores = 0;
                        $active_stores = 0;
                        if($stores) {
                            while($row = $stores->fetch(PDO::FETCH_ASSOC)) {
                                $total_stores++;
                                if($row['is_active']) $active_stores++;
                            }
                            $stores->execute(); // Reset for table display
                        }
                        echo $total_stores; 
                        ?>
                    </div>
                    <div class="stat-label">Total Stores</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_stores; ?></div>
                    <div class="stat-label">Active Stores</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_stores - $active_stores; ?></div>
                    <div class="stat-label">Inactive Stores</div>
                </div>
            </div>

            <div class="stores-table-container">
                <table class="stores-table">
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
                        <?php if($stores && $stores->rowCount() > 0): ?>
                            <?php while($row = $stores->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="<?php echo $row['is_active'] ? 'store-active' : 'store-inactive'; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                        <?php if(!empty($row['notes'])): ?>
                                            <div class="store-notes"><?php echo htmlspecialchars($row['notes']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($row['address'])): ?>
                                            <div class="store-address"><?php echo htmlspecialchars($row['address']); ?></div>
                                        <?php else: ?>
                                            <span class="no-data">No address</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($row['phone'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($row['phone']); ?>">
                                                <?php echo htmlspecialchars($row['phone']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="no-data">No phone</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($row['website'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['website']); ?>" target="_blank" class="store-website">
                                                Visit Website
                                            </a>
                                        <?php else: ?>
                                            <span class="no-data">No website</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="index.php?action=edit_store&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit Store">
                                                ✏️ Edit
                                            </a>
                                            <a href="index.php?action=toggle_store_status&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm <?php echo $row['is_active'] ? 'btn-warning' : 'btn-success'; ?>"
                                               onclick="return confirm('Are you sure you want to <?php echo $row['is_active'] ? 'deactivate' : 'activate'; ?> this store?')"
                                               title="<?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?> Store">
                                                <?php echo $row['is_active'] ? '⏸️ Deactivate' : '▶️ Activate'; ?>
                                            </a>
                                            <?php if(!$row['is_active']): ?>
                                            <a href="index.php?action=delete_store&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to permanently delete this store? This action cannot be undone.')"
                                               title="Delete Store Permanently">
                                                🗑️ Delete
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data-row">
                                    <div class="no-data-message">
                                        <div class="no-data-icon">🏪</div>
                                        <h3>No stores found</h3>
                                        <p>Start by adding your first store location.</p>
                                        <a href="index.php?action=add_store" class="btn btn-primary">+ Add Store</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>