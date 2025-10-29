<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1d23">
    <meta name="description" content="Login - Food & Ingredient Inventory Management">
    <title>Login - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1><?php echo APP_TITLE; ?></h1>
            <p style="text-align: center; color: var(--text-secondary); margin-bottom: 2rem;">Please sign in to your account</p>

            <?php if(isset($_GET['message'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
            <?php endif; ?>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
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

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    🔐 Sign In
                </button>
            </form>

            <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-muted); font-size: 0.875rem;">Default credentials: <strong style="color: var(--text-primary);">admin</strong> / <strong style="color: var(--text-primary);">admin123</strong></p>
                <small style="color: var(--text-muted);">Change after first login!</small>
            </div>
        </div>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>