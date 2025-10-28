<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1d23">
    <meta name="description" content="Add Store - Food & Ingredient Inventory Management">
    <title>Add Store - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo">🍽️ Food Inventory</a>
            <nav class="nav">
                <a href="index.php?action=manage_stores">🏪 Stores</a>
                <a href="index.php?action=dashboard">📊 Dashboard</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>Add New Store</h1>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
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

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" 
                           placeholder="(555) 123-4567"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" 
                           placeholder="https://example.com"
                           value="<?php echo isset($_POST['website']) ? htmlspecialchars($_POST['website']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" 
                              placeholder="Additional notes about this store (hours, special features, etc.)"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="is_active" name="is_active" checked> Active
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-success">✓ Add Store</button>
                    <a href="index.php?action=manage_stores" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div style="margin-top: 2rem;">
            <a href="index.php?action=manage_stores" class="btn btn-secondary">← Back to Stores</a>
        </div>
    </div>
</body>
</html>
