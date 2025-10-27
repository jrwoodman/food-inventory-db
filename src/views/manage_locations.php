<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Locations - Food Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo">🍽️ Food Inventory</a>
            <nav class="nav">
                <a href="index.php?action=dashboard">📊 Dashboard</a>
                <a href="index.php?action=manage_locations">📍 Locations</a>
                <a href="index.php?action=manage_stores">🏪 Stores</a>
                <a href="index.php?action=users">👥 Users</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>📍 Manage Storage Locations</h1>
        
        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

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
                        $location_model = new Location($this->db ?? $db);
                        while ($row = $locations->fetch(PDO::FETCH_ASSOC)): 
                            $location_count++;
                            $location_model->id = $row['id'];
                            $food_count = $location_model->getFoodCount();
                            $ingredient_count = $location_model->getIngredientLocationCount();
                            $total_items = $food_count + $ingredient_count;
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
                                <td><?php echo $total_items; ?> items (<?php echo $food_count; ?> foods, <?php echo $ingredient_count; ?> ingredient locations)</td>
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
                        <?php endwhile; ?>
                        <?php if ($location_count == 0): ?>
                            <tr><td colspan="5" class="no-items">No locations found. <a href="index.php?action=add_location">Add your first location!</a></td></tr>
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
