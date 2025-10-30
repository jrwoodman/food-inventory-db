<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1d23">
    <meta name="description" content="Add User - Food & Ingredient Inventory Management">
    <title><?php echo $users_count == 0 ? 'Create Account' : 'Add User'; ?> - Food & Ingredient Inventory</title>
    <?php if (APP_FAVICON): ?>
    <link rel="icon" href="<?php echo APP_FAVICON; ?>" type="image/x-icon">
    <?php endif; ?>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <?php if($users_count > 0): ?>
    <!-- Header for existing system (admin adding user) -->
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
                <?php if ($current_user && $current_user->canEdit()): ?>
                    <a href="index.php?action=add_food">🍎 Add Food</a>
                    <a href="index.php?action=add_ingredient">🧄 Add Ingredient</a>
                    <a href="index.php?action=track_meal">🍴 Track Meal</a>
                <?php endif; ?>
                <a href="index.php?action=system_settings">⚙️ Settings</a>
                <?php if ($current_user): ?>
                <a href="index.php?action=profile" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                    <img src="<?php echo $current_user->getGravatarUrl(64); ?>" 
                         alt="<?php echo htmlspecialchars($current_user->username); ?>" 
                         style="width: 32px; height: 32px; border-radius: 50%;">
                    <?php echo htmlspecialchars($current_user->username); ?>
                </a>
                <a href="index.php?action=logout">🚪 Logout</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1>👥 Add New User</h1>
    <?php else: ?>
    <!-- First-time setup -->
    <div class="login-container">
        <div class="login-card" style="max-width: 600px;">
            <h1><?php echo APP_TITLE; ?></h1>
            <p style="text-align: center; color: var(--text-secondary); margin-bottom: 2rem;">
                Welcome! Create your admin account to get started.
            </p>
    <?php endif; ?>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           placeholder="Choose a username">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="user@example.com">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name"
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                               placeholder="Optional">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name"
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                               placeholder="Optional">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6"
                           placeholder="At least 6 characters">
                    <small style="color: var(--text-muted);">Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                           placeholder="Re-enter password">
                </div>

                <?php if($users_count > 0): ?>
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="user" <?php echo ($_POST['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>
                            User (Can Edit)
                        </option>
                        <option value="viewer" <?php echo ($_POST['role'] ?? '') === 'viewer' ? 'selected' : ''; ?>>
                            Viewer (Read Only)
                        </option>
                        <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                            Admin (Full Access)
                        </option>
                    </select>
                    <small style="color: var(--text-muted);">
                        <strong>Viewer:</strong> Read-only access | 
                        <strong>User:</strong> Can add/edit items | 
                        <strong>Admin:</strong> Full control including user management
                    </small>
                </div>
                <?php else: ?>
                <input type="hidden" name="role" value="admin">
                <?php endif; ?>

                <?php if($users_count > 0 && !empty($all_groups)): ?>
                <div class="form-group">
                    <label for="groups">Groups</label>
                    <div style="max-height: 150px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.75rem;">
                        <?php foreach($all_groups as $group): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="group_ids[]" value="<?php echo $group['id']; ?>" 
                                       style="margin-right: 0.5rem;"
                                       <?php echo (isset($_POST['group_ids']) && in_array($group['id'], $_POST['group_ids'])) ? 'checked' : ''; ?>>
                                <span><?php echo htmlspecialchars($group['name']); ?></span>
                                <?php if (!empty($group['description'])): ?>
                                <small style="color: var(--text-muted); margin-left: 0.5rem;">- <?php echo htmlspecialchars($group['description']); ?></small>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <small style="color: var(--text-muted);">Select which groups this user should be added to</small>
                </div>
                <?php endif; ?>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-success" style="<?php echo $users_count == 0 ? 'width: 100%;' : ''; ?>">
                        <?php echo $users_count == 0 ? '✓ Create Admin Account' : '✓ Add User'; ?>
                    </button>
                    <?php if($users_count > 0): ?>
                        <a href="index.php?action=users" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

    <?php if($users_count > 0): ?>
        <div style="margin-top: 2rem;">
            <a href="index.php?action=users" class="btn btn-secondary">← Back to Users</a>
        </div>
    </div>
    <?php else: ?>
        </div>
    </div>
    <?php endif; ?>

    <script src="../assets/js/app.js"></script>
</body>
</html>
