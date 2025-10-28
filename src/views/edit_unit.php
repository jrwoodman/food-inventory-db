<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Unit - Food Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo">🍽️ Food Inventory</a>
            <nav class="nav">
                <a href="index.php?action=dashboard">📊 Dashboard</a>
                <a href="index.php?action=manage_units">📏 Units</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>📏 Edit Unit</h1>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="index.php?action=edit_unit&id=<?php echo $unit->id; ?>">
                <div class="form-group">
                    <label for="name">Unit Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($unit->name); ?>"
                           placeholder="e.g., Cups, Pounds, Liters">
                    <small>Full name of the measurement unit</small>
                </div>

                <div class="form-group">
                    <label for="abbreviation">Abbreviation *</label>
                    <input type="text" id="abbreviation" name="abbreviation" required 
                           value="<?php echo htmlspecialchars($unit->abbreviation); ?>"
                           placeholder="e.g., cups, lbs, l">
                    <small>Short form used in displays</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Optional description of the unit"><?php echo htmlspecialchars($unit->description ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?php echo $unit->is_active ? 'checked' : ''; ?>> 
                        Active (available for use)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">✓ Save Changes</button>
                    <a href="index.php?action=manage_units" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
