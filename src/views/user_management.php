<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User & Group Management - Food Inventory</title>
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
                <a href="index.php?action=user_management" class="active">👥 Users & Groups</a>
                <a href="index.php?action=manage_locations">📍 Locations</a>
                <a href="index.php?action=manage_stores">🏪 Stores</a>
                <a href="index.php?action=system_settings">⚙️ System Settings</a>
                <a href="index.php?action=profile">⚙️ Profile</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>👥 User & Group Management</h1>
        
        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="showTab('users')">👤 Users</button>
            <button class="tab" onclick="showTab('groups')">👥 Groups</button>
        </div>

        <!-- Users Tab -->
        <div id="users-tab" class="tab-content active">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>User Accounts</h3>
                <a href="index.php?action=register" class="btn btn-success">+ Add User</a>
            </div>

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
            
            <div class="user-stats" style="margin-top: 2rem;">
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

        <!-- Groups Tab -->
        <div id="groups-tab" class="tab-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Groups</h3>
                <a href="index.php?action=create_group" class="btn btn-success">+ Create Group</a>
            </div>

            <?php if (empty($groups)): ?>
                <div class="card">
                    <p class="no-items">No groups found. <a href="index.php?action=create_group">Create your first group!</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($groups as $group): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin: 0;"><?php echo htmlspecialchars($group['name']); ?></h3>
                        <span class="badge badge-primary">Group</span>
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
                        <a href="index.php?action=manage_group_members&id=<?php echo $group['id']; ?>" class="btn btn-sm">Manage Members</a>
                        <a href="index.php?action=edit_group&id=<?php echo $group['id']; ?>" class="btn btn-sm">Edit</a>
                        <a href="index.php?action=delete_group&id=<?php echo $group['id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this group? This will also delete all inventory items in this group.');">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
