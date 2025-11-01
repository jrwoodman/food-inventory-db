<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Store Location - <?php echo APP_NAME; ?></title>
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
                    <a href="index.php?action=add_food">🍎 Add Food</a>
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
        <h1>Add Store Location</h1>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="chain_id">Store Chain *</label>
                    <select id="chain_id" name="chain_id" required>
                        <option value="">Select Store Chain</option>
                        <?php 
                        $chain_id = $_GET['chain_id'] ?? ($_POST['chain_id'] ?? '');
                        while ($chain = $chains->fetch(PDO::FETCH_ASSOC)): 
                        ?>
                            <option value="<?php echo $chain['id']; ?>" <?php echo ($chain_id == $chain['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($chain['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="location_name">Location Name</label>
                    <input type="text" id="location_name" name="location_name"
                           placeholder="e.g., Downtown, North Side, Main Street"
                           value="<?php echo htmlspecialchars($_POST['location_name'] ?? ''); ?>">
                    <small style="color: var(--text-muted);">Optional: Leave blank for unnamed locations</small>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" 
                              placeholder="Complete street address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" 
                           placeholder="(555) 123-4567"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="hours">Hours</label>
                    <input type="text" id="hours" name="hours" 
                           placeholder="Mon-Fri 9am-9pm, Sat-Sun 10am-6pm"
                           value="<?php echo htmlspecialchars($_POST['hours'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" 
                              placeholder="Additional notes about this location"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" checked> Active
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-success">✓ Add Location</button>
                    <a href="index.php?action=system_settings#stores" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div style="margin-top: 2rem;">
            <a href="index.php?action=system_settings#stores" class="btn btn-secondary">← Back to System Settings</a>
        </div>
    </div>
    
    <script>
        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        
        function formatPhoneNumber(value) {
            // Remove all non-digit characters
            const digits = value.replace(/\D/g, '');
            
            // Format as (XXX) YYY-ZZZZ
            if (digits.length <= 3) {
                return digits;
            } else if (digits.length <= 6) {
                return `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
            } else {
                return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
            }
        }
        
        phoneInput.addEventListener('input', function(e) {
            const cursorPosition = e.target.selectionStart;
            const oldValue = e.target.value;
            const oldLength = oldValue.length;
            
            // Format the value
            const formatted = formatPhoneNumber(oldValue);
            e.target.value = formatted;
            
            // Adjust cursor position
            const newLength = formatted.length;
            const diff = newLength - oldLength;
            e.target.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
        });
        
        // Format on page load if there's a value
        if (phoneInput.value) {
            phoneInput.value = formatPhoneNumber(phoneInput.value);
        }
    </script>
</body>
</html>
