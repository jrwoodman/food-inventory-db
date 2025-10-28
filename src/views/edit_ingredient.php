<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Edit Ingredient - Food & Ingredient Inventory Management">
    <title>Edit Ingredient - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>✏️ Edit Ingredient</h1>
            </div>
            <div class="nav-actions">
                <nav>
                    <a href="index.php?action=dashboard" class="btn btn-primary">← Back to Dashboard</a>
                </nav>
            </div>
        </header>

        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="add-form">
                <div class="form-group">
                    <label for="name">Ingredient Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($ingredient->name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">Select Category</option>
                        <option value="Spices" <?php echo ($ingredient->category ?? '') === 'Spices' ? 'selected' : ''; ?>>Spices</option>
                        <option value="Herbs" <?php echo ($ingredient->category ?? '') === 'Herbs' ? 'selected' : ''; ?>>Herbs</option>
                        <option value="Oils" <?php echo ($ingredient->category ?? '') === 'Oils' ? 'selected' : ''; ?>>Oils</option>
                        <option value="Vinegars" <?php echo ($ingredient->category ?? '') === 'Vinegars' ? 'selected' : ''; ?>>Vinegars</option>
                        <option value="Flour" <?php echo ($ingredient->category ?? '') === 'Flour' ? 'selected' : ''; ?>>Flour</option>
                        <option value="Sugar" <?php echo ($ingredient->category ?? '') === 'Sugar' ? 'selected' : ''; ?>>Sugar</option>
                        <option value="Salt" <?php echo ($ingredient->category ?? '') === 'Salt' ? 'selected' : ''; ?>>Salt</option>
                        <option value="Baking" <?php echo ($ingredient->category ?? '') === 'Baking' ? 'selected' : ''; ?>>Baking</option>
                        <option value="Sauces" <?php echo ($ingredient->category ?? '') === 'Sauces' ? 'selected' : ''; ?>>Sauces</option>
                        <option value="Condiments" <?php echo ($ingredient->category ?? '') === 'Condiments' ? 'selected' : ''; ?>>Condiments</option>
                        <option value="Other" <?php echo ($ingredient->category ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="unit">Unit</label>
                    <select id="unit" name="unit">
                        <option value="oz" <?php echo ($ingredient->unit ?? '') === 'oz' ? 'selected' : ''; ?>>Ounces</option>
                        <option value="g" <?php echo ($ingredient->unit ?? '') === 'g' ? 'selected' : ''; ?>>Grams</option>
                        <option value="lbs" <?php echo ($ingredient->unit ?? '') === 'lbs' ? 'selected' : ''; ?>>Pounds</option>
                        <option value="kg" <?php echo ($ingredient->unit ?? '') === 'kg' ? 'selected' : ''; ?>>Kilograms</option>
                        <option value="cups" <?php echo ($ingredient->unit ?? '') === 'cups' ? 'selected' : ''; ?>>Cups</option>
                        <option value="tbsp" <?php echo ($ingredient->unit ?? '') === 'tbsp' ? 'selected' : ''; ?>>Tablespoons</option>
                        <option value="tsp" <?php echo ($ingredient->unit ?? '') === 'tsp' ? 'selected' : ''; ?>>Teaspoons</option>
                        <option value="ml" <?php echo ($ingredient->unit ?? '') === 'ml' ? 'selected' : ''; ?>>Milliliters</option>
                        <option value="liters" <?php echo ($ingredient->unit ?? '') === 'liters' ? 'selected' : ''; ?>>Liters</option>
                        <option value="bottles" <?php echo ($ingredient->unit ?? '') === 'bottles' ? 'selected' : ''; ?>>Bottles</option>
                        <option value="jars" <?php echo ($ingredient->unit ?? '') === 'jars' ? 'selected' : ''; ?>>Jars</option>
                        <option value="packages" <?php echo ($ingredient->unit ?? '') === 'packages' ? 'selected' : ''; ?>>Packages</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cost_per_unit">Cost per Unit ($)</label>
                        <input type="number" id="cost_per_unit" name="cost_per_unit" step="0.01" value="<?php echo htmlspecialchars($ingredient->cost_per_unit ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="supplier">Supplier</label>
                        <input type="text" id="supplier" name="supplier" value="<?php echo htmlspecialchars($ingredient->supplier ?? ''); ?>" placeholder="Store or supplier name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="purchase_date">Purchase Date</label>
                        <input type="date" id="purchase_date" name="purchase_date" value="<?php echo htmlspecialchars($ingredient->purchase_date ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($ingredient->expiry_date ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="purchase_location">Purchase Location</label>
                    <select id="purchase_location" name="purchase_location">
                        <option value="">Select Store</option>
                        <?php if(isset($stores) && !empty($stores)): ?>
                            <?php foreach($stores as $store): ?>
                                <option value="<?php echo htmlspecialchars($store['name']); ?>" 
                                    <?php echo ($ingredient->purchase_location ?? '') === $store['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($store['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <option value="Other" <?php echo ($ingredient->purchase_location ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <small class="form-help">Select where you purchased this ingredient</small>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any additional notes about this ingredient..."><?php echo htmlspecialchars($ingredient->notes ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">✓ Update Ingredient</button>
                    <a href="index.php?action=dashboard" class="btn btn-secondary">× Cancel</a>
                    <a href="index.php?action=delete_ingredient&id=<?php echo $ingredient->id; ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this ingredient?');"
                       style="margin-left: auto;">
                        🗑️ Delete
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>
