<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Group - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php?action=dashboard" class="nav-brand"><?php echo SITE_NAME; ?></a>
            <ul class="nav-menu">
                <li><a href="index.php?action=dashboard">Dashboard</a></li>
                <li><a href="index.php?action=list_groups">Groups</a></li>
                <li><a href="index.php?action=profile">Profile</a></li>
                <li><a href="index.php?action=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Edit Group</h1>
            <a href="index.php?action=list_groups" class="btn">Back to Groups</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <div class="form-group">
                <label for="name">Group Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($group->name); ?>" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($group->description ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Group</button>
                <a href="index.php?action=list_groups" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
