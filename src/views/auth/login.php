<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Login - Food & Ingredient Inventory Management">
    <title>Login - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>🍽️ Food Inventory</h1>
                <p>Please sign in to your account</p>
            </div>

            <?php if(isset($_GET['message'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
            <?php endif; ?>

            <?php if(!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="form-container auth-form">
                <form method="POST" class="add-form">
                    <div class="form-group">
                        <label for="username">Username or Email *</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               placeholder="Enter your username or email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary auth-btn">
                            🔐 Sign In
                        </button>
                    </div>
                </form>
            </div>

            <div class="auth-footer">
                <p>Default admin credentials: <strong>admin</strong> / <strong>admin123</strong></p>
                <small>Change these credentials immediately after first login!</small>
            </div>

            <div class="theme-toggle-container">
                <div class="theme-toggle">
                    <span class="theme-toggle-label">Theme</span>
                    <label class="theme-switch">
                        <input type="checkbox" id="theme-toggle">
                        <span class="theme-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>