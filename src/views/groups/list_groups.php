<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1d23">
    <meta name="description" content="My Groups - Food & Ingredient Inventory Management">
    <title>My Groups - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo">🍽️ Food Inventory</a>
            <nav class="nav">
                <a href="index.php?action=dashboard">📊 Dashboard</a>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>👥 My Groups</h1>
            <?php if ($current_user->canEdit()): ?>
            <a href="index.php?action=create_group" class="btn btn-success">+ Create New Group</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php if (empty($groups)): ?>
            <div class="card">
                <p class="no-items">You are not a member of any groups yet.
                <?php if ($current_user->canEdit()): ?>
                    <a href="index.php?action=create_group">Create your first group!</a>
                <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
                <?php foreach ($groups as $group): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin: 0;"><?php echo htmlspecialchars($group['name']); ?></h3>
                        <span class="badge badge-<?php echo $group['role'] === 'owner' ? 'success' : ($group['role'] === 'admin' ? 'primary' : 'secondary'); ?>"><?php echo ucfirst($group['role']); ?></span>
                    </div>
                    
                    <?php if (!empty($group['description'])): ?>
                    <p style="color: var(--text-muted); margin-bottom: 1rem;"><?php echo htmlspecialchars($group['description']); ?></p>
                    <?php endif; ?>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 0.875rem; color: var(--text-muted);">Members</div>
                            <div style="font-size: 1.5rem; font-weight: 600; margin-top: 0.25rem;"><?php echo $group['member_count']; ?></div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 0.875rem; color: var(--text-muted);">Foods</div>
                            <div style="font-size: 1.5rem; font-weight: 600; margin-top: 0.25rem;"><?php echo $group['inventory_counts']['foods']; ?></div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 0.875rem; color: var(--text-muted);">Ingredients</div>
                            <div style="font-size: 1.5rem; font-weight: 600; margin-top: 0.25rem;"><?php echo $group['inventory_counts']['ingredients']; ?></div>
                        </div>
                    </div>
                    
                    <div class="table-actions" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <?php if (in_array($group['role'], ['owner', 'admin']) || $current_user->isAdmin()): ?>
                        <a href="index.php?action=manage_group_members&id=<?php echo $group['id']; ?>" class="btn btn-sm">Manage Members</a>
                        <a href="index.php?action=edit_group&id=<?php echo $group['id']; ?>" class="btn btn-sm">Edit</a>
                        <?php if ($group['role'] === 'owner' || $current_user->isAdmin()): ?>
                        <a href="index.php?action=delete_group&id=<?php echo $group['id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this group? This will also delete all inventory items in this group.');">Delete</a>
                        <?php endif; ?>
                        <?php else: ?>
                        <a href="index.php?action=manage_group_members&id=<?php echo $group['id']; ?>" class="btn btn-sm">View Members</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top: 2rem;">
            <a href="index.php?action=dashboard" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
