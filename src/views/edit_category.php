<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - <?php echo APP_NAME; ?></title>
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
        <h1>🏷️ Edit Category</h1>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="index.php?action=edit_category&id=<?php echo $category->id; ?>">
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($category->name); ?>"
                           placeholder="e.g., Vegetables, Spices">
                    <small>Name of the category</small>
                </div>

                <div class="form-group">
                    <label for="type">Type *</label>
                    <select id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="food" <?php echo $category->type === 'food' ? 'selected' : ''; ?>>🍎 Food</option>
                        <option value="ingredient" <?php echo $category->type === 'ingredient' ? 'selected' : ''; ?>>🧄 Ingredient</option>
                    </select>
                    <small>Whether this category is for foods or ingredients</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Optional description of the category"><?php echo htmlspecialchars($category->description ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">✓ Save Changes</button>
                    <a href="index.php?action=system_settings" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
