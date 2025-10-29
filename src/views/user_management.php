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
                <?php if ($current_user->canEdit()): ?>
                    <a href="index.php?action=add_food">🍎 Add Food</a>
                    <a href="index.php?action=add_ingredient">🧄 Add Ingredient</a>
                    <a href="index.php?action=track_meal">🍴 Track Meal</a>
                <?php endif; ?>
                <?php if ($current_user->isAdmin()): ?>
                    <a href="index.php?action=user_management" class="active">👥 Users & Groups</a>
                    <a href="index.php?action=system_settings">⚙️ System Settings</a>
                <?php else: ?>
                    <a href="index.php?action=list_groups">👥 Groups</a>
                <?php endif; ?>
                <a href="index.php?action=profile" style="display: flex; align-items: center; gap: 0.25rem;">
                    <span style="background: var(--primary-color); color: white; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">
                        <?php echo strtoupper(substr($current_user->username, 0, 1)); ?>
                    </span>
                    <?php echo htmlspecialchars($current_user->username); ?>
                </a>
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

            <div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $user_count = 0;
                            while ($row = $users->fetch(PDO::FETCH_ASSOC)): 
                                $user_count++;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name'] ?: $row['username']); ?></strong>
                                    </td>
                                    <td>@<?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                            <?php echo ucfirst($row['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['last_login']): ?>
                                            <?php echo date('M j, Y', strtotime($row['last_login'])); ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-actions">
                                        <a href="index.php?action=edit_user&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary">✏️ Edit</a>
                                        <?php if ($row['id'] != $current_user->id): ?>
                                            <a href="index.php?action=delete_user&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                               🗑️ Delete
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if ($user_count == 0): ?>
                                <tr><td colspan="7" class="no-items">No users found. <a href="index.php?action=register">Add your first user!</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
