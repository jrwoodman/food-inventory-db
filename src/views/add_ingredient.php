<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Add Ingredient - Food & Ingredient Inventory Management">
    <title>Add Ingredient - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>🧄 Add Ingredient</h1>
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
                    <label for="name">Ingredient Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">Select Category</option>
                        <option value="Spices">Spices</option>
                        <option value="Herbs">Herbs</option>
                        <option value="Oils">Oils</option>
                        <option value="Vinegars">Vinegars</option>
                        <option value="Flour">Flour</option>
                        <option value="Sugar">Sugar</option>
                        <option value="Salt">Salt</option>
                        <option value="Baking">Baking</option>
                        <option value="Sauces">Sauces</option>
                        <option value="Condiments">Condiments</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="unit">Unit</label>
                    <select id="unit" name="unit">
                        <option value="oz">Ounces</option>
                        <option value="g">Grams</option>
                        <option value="lbs">Pounds</option>
                        <option value="kg">Kilograms</option>
                        <option value="cups">Cups</option>
                        <option value="tbsp">Tablespoons</option>
                        <option value="tsp">Teaspoons</option>
                        <option value="ml">Milliliters</option>
                        <option value="liters">Liters</option>
                        <option value="bottles">Bottles</option>
                        <option value="jars">Jars</option>
                        <option value="packages">Packages</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cost_per_unit">Cost per Unit ($)</label>
                        <input type="number" id="cost_per_unit" name="cost_per_unit" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="supplier">Supplier</label>
                        <input type="text" id="supplier" name="supplier" placeholder="Store or supplier name">
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
                    <small class="form-help">Select where you purchased this ingredient</small>
                </div>

                <div class="form-group">
                    <label>Storage Locations & Quantities</label>
                    <div id="locations-container">
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
                            <input type="number" name="locations[0][quantity]" placeholder="Quantity" step="0.01" style="width: 120px;" required value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>">
                            <input type="text" name="locations[0][notes]" placeholder="Notes (optional)" style="flex: 1;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove();">✕</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addLocationRow()" style="margin-top: 0.5rem;">+ Add Location</button>
                    <small class="form-help">Track this ingredient across multiple storage locations</small>
                </div>

                <script>
                let locationIndex = 1;
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
                        <input type="number" name="locations[${locationIndex}][quantity]" placeholder="Quantity" step="0.01" style="width: 120px;" required>
                        <input type="text" name="locations[${locationIndex}][notes]" placeholder="Notes (optional)" style="flex: 1;">
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove();">✕</button>
                    `;
                    container.appendChild(row);
                    locationIndex++;
                }
                </script>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any additional notes about this ingredient..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">✓ Add Ingredient</button>
                    <a href="index.php?action=dashboard" class="btn btn-secondary">× Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>