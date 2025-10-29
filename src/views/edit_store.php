<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Store - <?php echo APP_NAME; ?></title>
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
        <h1>Edit Store</h1>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="name">Store Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($store->name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($store->address ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($store->phone ?? ''); ?>" placeholder="(555) 123-4567">
                </div>

                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" value="<?php echo htmlspecialchars($store->website ?? ''); ?>" placeholder="https://example.com">
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($store->notes ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" <?php echo $store->is_active ? 'checked' : ''; ?>> Active
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem; align-items: center;">
                    <button type="submit" class="btn btn-primary">✓ Update Store</button>
                    <a href="index.php?action=manage_stores" class="btn btn-secondary">Cancel</a>
                    <?php if($store->is_active): ?>
                        <span style="margin-left: auto; color: #888; font-size: 0.9em;">💡 Uncheck "Active" to enable deletion</span>
                    <?php else: ?>
                        <a href="index.php?action=delete_store&id=<?php echo $store->id; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Permanently delete this store? This cannot be undone.');" 
                           style="margin-left: auto;">
                            🗑️ Delete Store
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div style="margin-top: 2rem;">
            <a href="index.php?action=manage_stores" class="btn btn-secondary">← Back to Stores</a>
        </div>
    </div>
</body>
</html>
