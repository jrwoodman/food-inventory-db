<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="User Management - Food & Ingredient Inventory">
    <title>User Management - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>👥 User Management</h1>
            </div>
            <div class="nav-actions">
                <nav>
                    <a href="index.php?action=dashboard" class="btn btn-primary">← Back to Dashboard</a>
                    <a href="index.php?action=register" class="btn btn-success">👤 Add User</a>
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