<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Food Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo">🍽️ Food Inventory</a>
            <nav class="nav">
                <a href="index.php?action=users">👥 Users</a>
                <a href="index.php?action=dashboard">📊 Dashboard</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>Edit User</h1>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user->username); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user->first_name ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user->last_name ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="viewer" <?php echo $user->role == 'viewer' ? 'selected' : ''; ?>>Viewer (Read Only)</option>
                        <option value="user" <?php echo $user->role == 'user' ? 'selected' : ''; ?>>User (Can Edit)</option>
                        <option value="admin" <?php echo $user->role == 'admin' ? 'selected' : ''; ?>>Admin (Full Access)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" <?php echo $user->is_active ? 'checked' : ''; ?>> Active
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">✓ Update User</button>
                    <a href="index.php?action=users" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div class="card" style="margin-top: 2rem; border-left: 3px solid var(--accent-primary);">
            <h3>💡 Note</h3>
            <p>To change this user's password, they should use the Profile page after logging in, or an admin can reset it manually.</p>
        </div>
    </div>
</body>
</html>
