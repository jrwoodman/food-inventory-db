<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1d23">
    <meta name="description" content="Edit Group - Food & Ingredient Inventory Management">
    <title>Edit Group - Food & Ingredient Inventory</title>
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
                <a href="index.php?action=profile">⚙️ Profile</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>✏️ Edit Group</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="name">Group Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($group->name); ?>" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($group->description ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-success">✓ Update Group</button>
                    <a href="index.php?action=list_groups" class="btn btn-secondary">× Cancel</a>
                </div>
            </form>
        </div>

        <div style="margin-top: 2rem;">
            <a href="index.php?action=list_groups" class="btn btn-secondary">← Back to Groups</a>
        </div>
    </div>
</body>
</html>
