<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Food Item - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Add Food Item</h1>
            <nav>
                <a href="index.php?action=dashboard" class="btn btn-primary">Back to Dashboard</a>
            </nav>
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
                        <option value="Fruits">Fruits</option>
                        <option value="Vegetables">Vegetables</option>
                        <option value="Meat">Meat</option>
                        <option value="Dairy">Dairy</option>
                        <option value="Grains">Grains</option>
                        <option value="Beverages">Beverages</option>
                        <option value="Snacks">Snacks</option>
                        <option value="Frozen">Frozen</option>
                        <option value="Canned">Canned</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <select id="unit" name="unit">
                            <option value="pieces">Pieces</option>
                            <option value="lbs">Pounds</option>
                            <option value="kg">Kilograms</option>
                            <option value="oz">Ounces</option>
                            <option value="g">Grams</option>
                            <option value="cups">Cups</option>
                            <option value="liters">Liters</option>
                            <option value="ml">Milliliters</option>
                            <option value="cans">Cans</option>
                            <option value="boxes">Boxes</option>
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
                    <label for="location">Storage Location</label>
                    <select id="location" name="location">
                        <option value="">Select Location</option>
                        <option value="Refrigerator">Refrigerator</option>
                        <option value="Freezer">Freezer</option>
                        <option value="Pantry">Pantry</option>
                        <option value="Counter">Counter</option>
                        <option value="Cupboard">Cupboard</option>
                        <option value="Basement">Basement</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any additional notes about this food item..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Add Food Item</button>
                    <a href="index.php?action=dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>