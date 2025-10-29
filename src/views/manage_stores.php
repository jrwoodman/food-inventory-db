<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1d23">
    <meta name="description" content="Manage Stores - Food & Ingredient Inventory Management">
    <title>Manage Stores - Food & Ingredient Inventory</title>
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
                <a href="index.php?action=dashboard">📊 Dashboard</a>
                <a href="index.php?action=profile">👤 Profile</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>🏪 Manage Stores</h1>

        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

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
                        while($row = $stores->fetch(PDO::FETCH_ASSOC)): 
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
                        <?php endwhile; ?>
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

        <div style="margin-top: 2rem;">
            <a href="index.php?action=dashboard" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
