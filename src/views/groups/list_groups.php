<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Groups - <?php echo defined('APP_NAME') ? APP_NAME : 'Food Inventory'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php?action=dashboard" class="nav-brand"><?php echo defined('APP_NAME') ? APP_NAME : 'Food Inventory'; ?></a>
            <ul class="nav-menu">
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=list_groups" class="active">Groups</a></li>
                <?php if ($current_user->isAdmin()): ?>
                <li><a href="index.php?action=users">Users</a></li>
                <li><a href="index.php?action=manage_stores">Stores</a></li>
                <li><a href="index.php?action=manage_locations">Locations</a></li>
                <?php endif; ?>
                <li><a href="index.php?action=profile">Profile</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>My Groups</h1>
            <?php if ($current_user->canEdit()): ?>
            <a href="index.php?action=create_group" class="btn btn-primary">Create New Group</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php if (empty($groups)): ?>
            <div class="empty-state">
                <p>You are not a member of any groups yet.</p>
                <?php if ($current_user->canEdit()): ?>
                <p><a href="index.php?action=create_group" class="btn">Create a Group</a></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="groups-grid">
                <?php foreach ($groups as $group): ?>
                <div class="group-card">
                    <div class="group-header">
                        <h3><?php echo htmlspecialchars($group['name']); ?></h3>
                        <span class="badge badge-<?php echo $group['role']; ?>"><?php echo ucfirst($group['role']); ?></span>
                    </div>
                    
                    <?php if (!empty($group['description'])): ?>
                    <p class="group-description"><?php echo htmlspecialchars($group['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="group-stats">
                        <div class="stat">
                            <span class="stat-label">Members</span>
                            <span class="stat-value"><?php echo $group['member_count']; ?></span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Foods</span>
                            <span class="stat-value"><?php echo $group['inventory_counts']['foods']; ?></span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Ingredients</span>
                            <span class="stat-value"><?php echo $group['inventory_counts']['ingredients']; ?></span>
                        </div>
                    </div>
                    
                    <div class="group-actions">
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
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
