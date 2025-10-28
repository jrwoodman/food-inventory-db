<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Add Food Item - Food & Ingredient Inventory Management">
    <title>Add Food Item - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>🍎 Add Food Item</h1>
            </div>
            <div class="nav-actions">
                <nav>
                    <a href="index.php?action=dashboard" class="btn btn-primary">← Back to Dashboard</a>
                </nav>
            </div>
        </header>

        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="add-form">
                <div class="form-group">
                    <label for="name">Food Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">Select Category</option>
                        <?php if(isset($food_categories) && !empty($food_categories)): ?>
                            <?php foreach($food_categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-help">Categories can be managed by admins</small>
                </div>

                <div class="form-group">
                    <label for="group_id">Group *</label>
                    <select id="group_id" name="group_id" required>
                        <option value="">Select Group</option>
                        <?php if(isset($user_groups) && !empty($user_groups)): ?>
                            <?php foreach($user_groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>" <?php echo (isset($default_group_id) && $default_group_id == $group['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-help">Select which group this food item belongs to</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <select id="unit" name="unit">
                            <option value="">Select Unit</option>
                            <?php if(isset($units) && !empty($units)): ?>
                                <?php foreach($units as $unit): ?>
                                    <option value="<?php echo htmlspecialchars($unit['abbreviation']); ?>">
                                        <?php echo htmlspecialchars($unit['name']); ?> (<?php echo htmlspecialchars($unit['abbreviation']); ?>)
                                        <?php if(!empty($unit['description'])): ?>
                                            - <?php echo htmlspecialchars($unit['description']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="purchase_date">Purchase Date</label>
                        <input type="date" id="purchase_date" name="purchase_date">
                    </div>

                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date">
                    </div>
                </div>

                <div class="form-group">
                    <label for="purchase_location">Purchase Location</label>
                    <select id="purchase_location" name="purchase_location">
                        <option value="">Select Store</option>
                        <?php if(isset($stores) && !empty($stores)): ?>
                            <?php foreach($stores as $store): ?>
                                <option value="<?php echo htmlspecialchars($store['name']); ?>">
                                    <?php echo htmlspecialchars($store['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <option value="Other">Other</option>
                    </select>
                    <small class="form-help">Select where you purchased this food item</small>
                </div>

                <div class="form-group">
                    <label for="location">Storage Location</label>
                    <select id="location" name="location">
                        <option value="">Select Location</option>
                        <?php if(isset($locations) && !empty($locations)): ?>
                            <?php foreach($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location['name']); ?>">
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-help">Storage locations can be managed by admins</small>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any additional notes about this food item..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">✓ Add Food Item</button>
                    <a href="index.php?action=dashboard" class="btn btn-secondary">× Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>