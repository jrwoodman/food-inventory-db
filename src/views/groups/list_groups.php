<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1d23">
    <meta name="description" content="My Groups - Food & Ingredient Inventory Management">
    <title>My Groups - Food & Ingredient Inventory</title>
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
            <div class="card" style="margin-bottom: 1.5rem;">
                <h3>⭐ Default Group</h3>
                <p style="color: var(--text-muted); margin-bottom: 1rem;">Your default group will be pre-selected when adding new food or ingredient items.</p>
                <form method="POST" action="index.php?action=set_default_group" style="display: flex; gap: 0.5rem; align-items: end;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label for="default_group">Default Group</label>
                        <select id="default_group" name="group_id">
                            <option value="">None (use first group)</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>" <?php echo ($current_user->default_group_id == $group['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">✓ Set Default</button>
                </form>
            </div>
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
