<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="User Management - Food & Ingredient Inventory">
    <title>User Management - Food & Ingredient Inventory</title>
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
                <?php if ($current_user->canEdit()): ?>
                    <a href="index.php?action=add_food">🍎 Add Food</a>
                    <a href="index.php?action=add_ingredient">🧄 Add Ingredient</a>
                    <a href="index.php?action=track_meal">🍴 Track Meal</a>
                <?php endif; ?>
                <a href="index.php?action=system_settings">⚙️ Settings</a>
                <a href="index.php?action=profile" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                    <img src="<?php echo $current_user->getGravatarUrl(64); ?>" 
                         alt="<?php echo htmlspecialchars($current_user->username); ?>" 
                         style="width: 32px; height: 32px; border-radius: 50%;">
                    <?php echo htmlspecialchars($current_user->username); ?>
                </a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>👥 User Management</h1>
            <a href="index.php?action=register" class="btn btn-success">+ Add User</a>
        </div>

        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="users-container">
            <?php 
            $user_count = 0;
            while ($row = $users->fetch(PDO::FETCH_ASSOC)): 
                $user_count++;
            ?>
                <div class="user-card">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($row['username'], 0, 2)); ?>
                    </div>
                    
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name'] ?: $row['username']); ?></h4>
                        <p>@<?php echo htmlspecialchars($row['username']); ?> • <?php echo htmlspecialchars($row['email']); ?></p>
                        <p><small>Joined <?php echo date('M j, Y', strtotime($row['created_at'])); ?></small></p>
                    </div>
                    
                    <div class="user-meta">
                        <span class="user-role <?php echo $row['role']; ?>"><?php echo $row['role']; ?></span>
                        <span class="user-status <?php echo $row['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $row['is_active'] ? '● Active' : '○ Inactive'; ?>
                        </span>
                        <?php if ($row['last_login']): ?>
                            <span class="user-status">Last login: <?php echo date('M j, Y', strtotime($row['last_login'])); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="user-actions">
                        <a href="index.php?action=edit_user&id=<?php echo $row['id']; ?>" 
                           class="btn btn-sm btn-primary"
                           title="Edit <?php echo htmlspecialchars($row['username']); ?>">
                           ✏️ Edit
                        </a>
                        
                        <?php if ($row['id'] != $current_user->id): ?>
                            <a href="index.php?action=delete_user&id=<?php echo $row['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               title="Delete <?php echo htmlspecialchars($row['username']); ?>"
                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                               🗑️ Delete
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <?php if ($user_count == 0): ?>
                <div class="card">
                    <div class="no-items">
                        <p>No users found.</p>
                        <a href="index.php?action=register">Add your first user!</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="user-stats">
            <div class="card">
                <h3>📊 User Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $user_count; ?></span>
                        <span class="stat-label">Total Users</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php 
                            // Count active users
                            $users->execute(); // Reset cursor
                            $active_count = 0;
                            while ($row = $users->fetch(PDO::FETCH_ASSOC)) {
                                if ($row['is_active']) $active_count++;
                            }
                            echo $active_count;
                        ?></span>
                        <span class="stat-label">Active Users</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>