<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Edit Food Item - Food & Ingredient Inventory Management">
    <title>Edit Food Item - Food & Ingredient Inventory</title>
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
                    <a href="index.php?action=add_food" class="active">🍎 Add Food</a>
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
        <h1>✏️ Edit Food Item</h1>

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
                        <?php if(isset($food_categories) && !empty($food_categories)): ?>
                            <?php foreach($food_categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>"
                                    <?php echo ($food->category ?? '') === $category['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-help">Categories can be managed by admins</small>
                </div>

                <div class="form-group">
                    <label for="brand">Brand</label>
                    <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($food->brand ?? ''); ?>" placeholder="Brand name (optional)">
                </div>

                <div class="form-group">
                    <label for="group_id">Group *</label>
                    <select id="group_id" name="group_id" required>
                        <option value="">Select Group</option>
                        <?php if(isset($user_groups) && !empty($user_groups)): ?>
                            <?php foreach($user_groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>" <?php echo ($food->group_id ?? '') == $group['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-help">Select which group this food item belongs to</small>
                </div>

                <div class="form-group">
                    <label for="unit">Unit</label>
                    <select id="unit" name="unit">
                        <option value="">Select Unit</option>
                        <?php if(isset($units) && !empty($units)): ?>
                            <?php foreach($units as $unit): ?>
                                <option value="<?php echo htmlspecialchars($unit['abbreviation']); ?>"
                                    <?php echo ($food->unit ?? '') === $unit['abbreviation'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($unit['name']); ?> (<?php echo htmlspecialchars($unit['abbreviation']); ?>)
                                    <?php if(!empty($unit['description'])): ?>
                                        - <?php echo htmlspecialchars($unit['description']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
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
                    <label>Storage Locations & Quantities</label>
                    <div id="locations-container">
                        <?php if(!empty($food->locations)): ?>
                            <?php foreach($food->locations as $index => $loc): ?>
                                <div class="location-row" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <select name="locations[<?php echo $index; ?>][location]" style="flex: 1;" required>
                                        <option value="">Select Location</option>
                                        <?php if(isset($locations) && !empty($locations)): ?>
                                            <?php foreach($locations as $location): ?>
                                                <option value="<?php echo htmlspecialchars($location['name']); ?>"
                                                    <?php echo ($loc['location'] ?? '') === $location['name'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($location['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <input type="number" name="locations[<?php echo $index; ?>][quantity]" 
                                           placeholder="Quantity" step="0.1" style="width: 120px;" 
                                           value="<?php echo htmlspecialchars($loc['quantity'] ?? '0'); ?>" required>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove();">✕</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="location-row" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <select name="locations[0][location]" style="flex: 1;" required>
                                    <option value="">Select Location</option>
                                    <?php if(isset($locations) && !empty($locations)): ?>
                                        <?php foreach($locations as $location): ?>
                                            <option value="<?php echo htmlspecialchars($location['name']); ?>">
                                                <?php echo htmlspecialchars($location['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <input type="number" name="locations[0][quantity]" placeholder="Quantity" step="0.1" style="width: 120px;" required>
                                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove();">✕</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addLocationRow()" style="margin-top: 0.5rem;">+ Add Location</button>
                    <small class="form-help">Add this food to multiple storage locations with different quantities</small>
                </div>
                
                <script>
                let locationIndex = <?php echo !empty($food->locations) ? count($food->locations) : 1; ?>;
                const locationsData = <?php echo json_encode($locations ?? []); ?>;
                function addLocationRow() {
                    const container = document.getElementById('locations-container');
                    const row = document.createElement('div');
                    row.className = 'location-row';
                    row.style.cssText = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem;';
                    
                    let optionsHtml = '<option value="">Select Location</option>';
                    locationsData.forEach(loc => {
                        optionsHtml += `<option value="${loc.name}">${loc.name}</option>`;
                    });
                    
                    row.innerHTML = `
                        <select name="locations[${locationIndex}][location]" style="flex: 1;" required>
                            ${optionsHtml}
                        </select>
                        <input type="number" name="locations[${locationIndex}][quantity]" placeholder="Quantity" step="0.1" style="width: 120px;" required>
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove();">✕</button>
                    `;
                    container.appendChild(row);
                    locationIndex++;
                }
                </script>

                <div class="form-group">
                    <label>Allergens</label>
                    <div style="display: flex; gap: 1.5rem; padding: 0.5rem 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="contains_gluten" value="1" <?php echo ($food->contains_gluten ?? 0) ? 'checked' : ''; ?>>
                            <span>Contains Gluten</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="contains_milk" value="1" <?php echo ($food->contains_milk ?? 0) ? 'checked' : ''; ?>>
                            <span>Contains Milk</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="contains_soy" value="1" <?php echo ($food->contains_soy ?? 0) ? 'checked' : ''; ?>>
                            <span>Contains Soy</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="contains_nuts" value="1" <?php echo ($food->contains_nuts ?? 0) ? 'checked' : ''; ?>>
                            <span>Contains Nuts</span>
                        </label>
                    </div>
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
