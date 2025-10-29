<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1d23">
    <meta name="description" content="Manage Members - Food & Ingredient Inventory Management">
    <title>Manage Members - <?php echo htmlspecialchars($group->name); ?> - Food & Ingredient Inventory</title>
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
        <h1>👥 Manage Members - <?php echo htmlspecialchars($group->name); ?></h1>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php 
        $membership = $current_user->isMemberOfGroup($group->id);
        $can_manage = $current_user->isAdmin() || ($membership && in_array($membership['role'], ['owner', 'admin']));
        ?>

        <?php if ($can_manage && !empty($all_users)): ?>
        <div class="card">
            <h3>➕ Add New Member</h3>
            <form method="POST" action="index.php?action=add_group_member" style="display: flex; gap: 0.5rem; align-items: end; flex-wrap: wrap; margin-top: 1rem;">
                <input type="hidden" name="group_id" value="<?php echo $group->id; ?>">
                
                <div class="form-group">
                    <label for="user_id">User</label>
                    <select id="user_id" name="user_id" required>
                        <option value="">Select a user...</option>
                        <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?> 
                            (<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                        <?php if ($membership && $membership['role'] === 'owner'): ?>
                        <option value="owner">Owner</option>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Add Member</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3>👤 Current Members (<?php echo count($members); ?>)</h3>
            
            <?php if (empty($members)): ?>
                <p>No members in this group.</p>
            <?php else: ?>
                <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <?php if ($can_manage): ?>
                            <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['username']); ?></td>
                            <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td>
                                <?php if ($can_manage && $member['id'] != $current_user->id): ?>
                                <form method="POST" action="index.php?action=update_group_member_role" style="display:inline;">
                                    <input type="hidden" name="group_id" value="<?php echo $group->id; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                    <select name="role" onchange="this.form.submit()">
                                        <option value="member" <?php echo $member['role'] === 'member' ? 'selected' : ''; ?>>Member</option>
                                        <option value="admin" <?php echo $member['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <?php if ($membership && $membership['role'] === 'owner'): ?>
                                        <option value="owner" <?php echo $member['role'] === 'owner' ? 'selected' : ''; ?>>Owner</option>
                                        <?php endif; ?>
                                    </select>
                                </form>
                                <?php else: ?>
                                <span class="badge badge-<?php echo $member['role']; ?>"><?php echo ucfirst($member['role']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($member['joined_at'])); ?></td>
                            <?php if ($can_manage): ?>
                            <td>
                                <?php if ($member['id'] != $current_user->id): ?>
                                <a href="index.php?action=remove_group_member&group_id=<?php echo $group->id; ?>&user_id=<?php echo $member['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to remove this member from the group?');">Remove</a>
                                <?php else: ?>
                                <span class="text-muted">You</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 2rem;">
            <a href="index.php?action=list_groups" class="btn btn-secondary">← Back to Groups</a>
        </div>
    </div>
</body>
</html>
