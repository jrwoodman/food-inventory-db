<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Add Store - Food & Ingredient Inventory Management">
    <title>Add Store - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>🏪 Add New Store</h1>
            </div>
            <div class="nav-actions">
                <nav>
                    <a href="index.php?action=manage_stores" class="btn btn-primary">← Back to Stores</a>
                </nav>
            </div>
        </header>

        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="add-form">
                <div class="form-group">
                    <label for="name">Store Name *</label>
                    <input type="text" id="name" name="name" required
                           placeholder="e.g., Walmart, Target, Local Market"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" 
                              placeholder="Complete store address including street, city, state, zip"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               placeholder="(555) 123-4567"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" 
                               placeholder="https://example.com"
                               value="<?php echo isset($_POST['website']) ? htmlspecialchars($_POST['website']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" 
                              placeholder="Additional notes about this store (hours, special features, etc.)"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">Store is active and available for selection</span>
                        </label>
                        <small class="form-help">Inactive stores won't appear in dropdown lists</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">✓ Add Store</button>
                    <a href="index.php?action=manage_stores" class="btn btn-secondary">× Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>