<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Location - <?php echo APP_NAME; ?></title>
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
                <a href="index.php?action=profile">👤 Profile</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>⚠️ Delete Location with Items</h1>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <strong>Warning!</strong> This location "<strong><?php echo htmlspecialchars($location->name); ?></strong>" is currently in use.
        </div>

        <div class="card">
            <h3>Items Using This Location</h3>
            <p>
                • <strong><?php echo $food_count; ?></strong> food item(s)<br>
                • <strong><?php echo $ingredient_count; ?></strong> ingredient location(s)<br>
                <strong>Total: <?php echo ($food_count + $ingredient_count); ?> items</strong>
            </p>

            <h3 style="margin-top: 2rem;">Migrate Items to Another Location</h3>
            <p>Before deleting this location, you must move all items to another location:</p>

            <form method="POST">
                <div class="form-group">
                    <label for="migrate_to">Move all items to:</label>
                    <select id="migrate_to" name="migrate_to" required>
                        <option value="">-- Select New Location --</option>
                        <?php foreach ($other_locations as $loc): ?>
                            <?php if ($loc['id'] != $location->id): ?>
                                <option value="<?php echo htmlspecialchars($loc['name']); ?>">
                                    <?php echo htmlspecialchars($loc['name']); ?>
                                    <?php if (!$loc['is_active']): ?> (Inactive)<?php endif; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure? This will move <?php echo ($food_count + $ingredient_count); ?> items and delete this location.');">
                        🗑️ Migrate & Delete Location
                    </button>
                    <a href="index.php?action=manage_locations" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div class="card" style="margin-top: 2rem; border-left: 3px solid var(--accent-primary);">
            <h4>💡 Alternative Options</h4>
            <p>Instead of deleting, you could:</p>
            <ul>
                <li><strong>Deactivate</strong> the location to prevent future use while keeping existing data</li>
                <li><strong>Rename</strong> the location if you need to reorganize</li>
            </ul>
        </div>
    </div>
</body>
</html>
