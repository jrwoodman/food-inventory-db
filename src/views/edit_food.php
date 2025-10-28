<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Edit Food Item - Food & Ingredient Inventory Management">
    <title>Edit Food Item - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>✏️ Edit Food Item</h1>
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
                    <label for="name">Food Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($food->name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">Select Category</option>
                        <option value="Fruits" <?php echo ($food->category ?? '') === 'Fruits' ? 'selected' : ''; ?>>Fruits</option>
                        <option value="Vegetables" <?php echo ($food->category ?? '') === 'Vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                        <option value="Meat" <?php echo ($food->category ?? '') === 'Meat' ? 'selected' : ''; ?>>Meat</option>
                        <option value="Dairy" <?php echo ($food->category ?? '') === 'Dairy' ? 'selected' : ''; ?>>Dairy</option>
                        <option value="Grains" <?php echo ($food->category ?? '') === 'Grains' ? 'selected' : ''; ?>>Grains</option>
                        <option value="Beverages" <?php echo ($food->category ?? '') === 'Beverages' ? 'selected' : ''; ?>>Beverages</option>
                        <option value="Snacks" <?php echo ($food->category ?? '') === 'Snacks' ? 'selected' : ''; ?>>Snacks</option>
                        <option value="Frozen" <?php echo ($food->category ?? '') === 'Frozen' ? 'selected' : ''; ?>>Frozen</option>
                        <option value="Canned" <?php echo ($food->category ?? '') === 'Canned' ? 'selected' : ''; ?>>Canned</option>
                        <option value="Other" <?php echo ($food->category ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" step="0.01" value="<?php echo htmlspecialchars($food->quantity ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <select id="unit" name="unit">
                            <option value="pieces" <?php echo ($food->unit ?? '') === 'pieces' ? 'selected' : ''; ?>>Pieces</option>
                            <option value="lbs" <?php echo ($food->unit ?? '') === 'lbs' ? 'selected' : ''; ?>>Pounds</option>
                            <option value="kg" <?php echo ($food->unit ?? '') === 'kg' ? 'selected' : ''; ?>>Kilograms</option>
                            <option value="oz" <?php echo ($food->unit ?? '') === 'oz' ? 'selected' : ''; ?>>Ounces</option>
                            <option value="g" <?php echo ($food->unit ?? '') === 'g' ? 'selected' : ''; ?>>Grams</option>
                            <option value="cups" <?php echo ($food->unit ?? '') === 'cups' ? 'selected' : ''; ?>>Cups</option>
                            <option value="liters" <?php echo ($food->unit ?? '') === 'liters' ? 'selected' : ''; ?>>Liters</option>
                            <option value="ml" <?php echo ($food->unit ?? '') === 'ml' ? 'selected' : ''; ?>>Milliliters</option>
                            <option value="cans" <?php echo ($food->unit ?? '') === 'cans' ? 'selected' : ''; ?>>Cans</option>
                            <option value="boxes" <?php echo ($food->unit ?? '') === 'boxes' ? 'selected' : ''; ?>>Boxes</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="purchase_date">Purchase Date</label>
                        <input type="date" id="purchase_date" name="purchase_date" value="<?php echo htmlspecialchars($food->purchase_date ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($food->expiry_date ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="purchase_location">Purchase Location</label>
                    <select id="purchase_location" name="purchase_location">
                        <option value="">Select Store</option>
                        <?php if(isset($stores) && !empty($stores)): ?>
                            <?php foreach($stores as $store): ?>
                                <option value="<?php echo htmlspecialchars($store['name']); ?>" 
                                    <?php echo ($food->purchase_location ?? '') === $store['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($store['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <option value="Other" <?php echo ($food->purchase_location ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <small class="form-help">Select where you purchased this food item</small>
                </div>

                <div class="form-group">
                    <label for="location">Storage Location</label>
                    <select id="location" name="location">
                        <option value="">Select Location</option>
                        <?php if(isset($locations) && !empty($locations)): ?>
                            <?php foreach($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location['name']); ?>"
                                    <?php echo ($food->location ?? '') === $location['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-help">Storage locations can be managed by admins</small>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any additional notes about this food item..."><?php echo htmlspecialchars($food->notes ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">✓ Update Food Item</button>
                    <a href="index.php?action=dashboard" class="btn btn-secondary">× Cancel</a>
                    <a href="index.php?action=delete_food&id=<?php echo $food->id; ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this food item?');"
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
