<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1d23">
    <meta name="description" content="Access Denied - Food & Ingredient Inventory Management">
    <title>Access Denied - Food & Ingredient Inventory</title>
    <link rel="stylesheet" href="assets/css/dark-theme.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php?action=dashboard" class="logo"><?php echo APP_TITLE; ?></a>
            <nav class="nav">
                <a href="index.php?action=dashboard">📊 Dashboard</a>
                <a href="index.php?action=list_groups">👥 Groups</a>
                <a href="index.php?action=profile">⚙️ Profile</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card" style="text-align: center; padding: 3rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🚫</div>
            <h1>Access Denied</h1>
            <p style="color: var(--text-muted); margin: 1.5rem 0;">
                You don't have permission to access this page.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                <a href="index.php?action=dashboard" class="btn btn-primary">← Back to Dashboard</a>
                <a href="index.php?action=list_groups" class="btn btn-secondary">View Groups</a>
            </div>
        </div>
    </div>
</body>
</html>
