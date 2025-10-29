<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Meal - Food Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo">🍽️ Food Inventory</a>
            <nav class="nav">
                <a href="index.php?action=dashboard">📊 Dashboard</a>
                <?php if ($current_user->canEdit()): ?>
                    <a href="index.php?action=add_food">🍎 Add Food</a>
                    <a href="index.php?action=add_ingredient">🧄 Add Ingredient</a>
                    <a href="index.php?action=track_meal" class="active">🍴 Track Meal</a>
                <?php endif; ?>
                <?php if ($current_user->isAdmin()): ?>
                    <a href="index.php?action=user_management">👥 Users & Groups</a>
                    <a href="index.php?action=system_settings">⚙️ System Settings</a>
                <?php else: ?>
                    <a href="index.php?action=list_groups">👥 Groups</a>
                <?php endif; ?>
                <a href="index.php?action=profile" style="display: flex; align-items: center; gap: 0.25rem;">
                    <img src="<?php echo $current_user->getGravatarUrl(48); ?>" 
                         alt="<?php echo htmlspecialchars($current_user->username); ?>" 
                         style="width: 24px; height: 24px; border-radius: 50%;">
                    <?php echo htmlspecialchars($current_user->username); ?>
                </a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>🍴 Track Meal</h1>
        <p style="color: var(--text-secondary); margin-bottom: 2rem;">
            Search for ingredients and food items used in your meal, then update quantities as needed.
        </p>

        <?php if(isset($_GET['message']) && !empty($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error']) && !empty($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <!-- Search Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3>🔍 Search Ingredients</h3>
            <form method="GET" action="index.php">
                <input type="hidden" name="action" value="track_meal">
                <div class="form-group">
                    <label for="search">Enter ingredient or food names (comma-separated)</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search_query ?? ''); ?>" 
                           placeholder="e.g., eggs, milk, chicken, rice"
                           style="width: 100%;">
                    <small style="color: var(--text-muted);">
                        Tip: Use commas to search for multiple items at once
                    </small>
                </div>
                <button type="submit" class="btn btn-primary">🔍 Search</button>
            </form>
        </div>

        <?php if (!empty($search_results)): ?>
        <!-- Search Results -->
        <div class="card">
            <h3>Search Results (<?php echo count($search_results); ?> items found)</h3>
            
            <form method="POST" action="index.php?action=update_meal_items" id="meal-form">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Current Qty</th>
                                <th>Unit</th>
                                <th>Use Qty</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $item): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?php echo $item['type'] === 'food' ? 'badge-success' : 'badge-primary'; ?>">
                                            <?php echo $item['type'] === 'food' ? '🍎 Food' : '🧄 Ingredient'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['category'] ?? '-'); ?></td>
                                    <td>
                                        <?php 
                                        if ($item['type'] === 'food') {
                                            echo htmlspecialchars($item['location'] ?? '-');
                                        } else {
                                            // For ingredients, show dropdown of available locations
                                            $ingredient = new Ingredient($db);
                                            $ingredient->id = $item['id'];
                                            $ingredient->readOne();
                                            
                                            if (!empty($ingredient->locations)) {
                                                echo '<select name="ingredient_updates[' . $item['id'] . '][location]" class="location-select" required>';
                                                foreach ($ingredient->locations as $loc) {
                                                    echo '<option value="' . htmlspecialchars($loc['location']) . '">';
                                                    echo htmlspecialchars($loc['location']) . ' (' . $loc['quantity'] . ')';
                                                    echo '</option>';
                                                }
                                                echo '</select>';
                                            } else {
                                                echo '-';
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($item['type'] === 'food') {
                                            echo htmlspecialchars($item['quantity'] ?? '0');
                                        } else {
                                            echo htmlspecialchars($item['total_quantity'] ?? '0');
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['unit'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($item['type'] === 'food'): ?>
                                            <input type="number" 
                                                   name="food_updates[<?php echo $item['id']; ?>][decrement]" 
                                                   step="0.1" 
                                                   min="0"
                                                   max="<?php echo $item['quantity']; ?>"
                                                   placeholder="0"
                                                   style="width: 80px;">
                                        <?php else: ?>
                                            <input type="number" 
                                                   name="ingredient_updates[<?php echo $item['id']; ?>][decrement]" 
                                                   step="0.1" 
                                                   min="0"
                                                   placeholder="0"
                                                   style="width: 80px;">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                                            <?php if ($item['type'] === 'food'): ?>
                                                <input type="checkbox" 
                                                       name="food_updates[<?php echo $item['id']; ?>][delete]" 
                                                       value="1">
                                            <?php else: ?>
                                                <input type="checkbox" 
                                                       name="ingredient_updates[<?php echo $item['id']; ?>][delete]" 
                                                       value="1">
                                            <?php endif; ?>
                                            Delete
                                        </label>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-success">✓ Update Inventory</button>
                    <a href="index.php?action=track_meal" class="btn btn-secondary">🔄 New Search</a>
                    <a href="index.php?action=dashboard" class="btn btn-secondary">← Back to Dashboard</a>
                </div>
            </form>
        </div>
        <?php elseif (isset($search_query) && !empty($search_query)): ?>
        <div class="card">
            <p class="no-items">No items found matching your search.</p>
        </div>
        <?php endif; ?>

        <?php if (empty($search_query)): ?>
        <div style="margin-top: 2rem;">
            <a href="index.php?action=dashboard" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // When delete checkbox is checked, clear the decrement input
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const row = this.closest('tr');
                const decrementInput = row.querySelector('input[type="number"]');
                if (this.checked && decrementInput) {
                    decrementInput.value = '';
                    decrementInput.disabled = true;
                } else if (decrementInput) {
                    decrementInput.disabled = false;
                }
            });
        });

        // Form validation
        document.getElementById('meal-form')?.addEventListener('submit', function(e) {
            let hasUpdates = false;
            
            // Check if any decrement values or delete checkboxes are set
            this.querySelectorAll('input[type="number"]').forEach(input => {
                if (input.value && parseFloat(input.value) > 0) {
                    hasUpdates = true;
                }
            });
            
            this.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                if (checkbox.checked) {
                    hasUpdates = true;
                }
            });
            
            if (!hasUpdates) {
                e.preventDefault();
                alert('Please enter quantities to use or select items to delete.');
                return false;
            }
        });
    </script>
</body>
</html>
