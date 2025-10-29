<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Units - <?php echo APP_NAME; ?></title>
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
                <a href="index.php?action=users">👤 Users</a>
                <a href="index.php?action=manage_locations">📍 Locations</a>
                <a href="index.php?action=manage_stores">🏪 Stores</a>
                <a href="index.php?action=manage_units" class="active">📏 Units</a>
                <a href="index.php?action=profile">⚙️ Profile</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>📏 Manage Measurement Units</h1>
        
        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

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

        <div style="margin-top: 2rem;">
            <a href="index.php?action=dashboard" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
