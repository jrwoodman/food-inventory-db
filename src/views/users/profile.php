<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo APP_NAME; ?></title>
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
                <a href="index.php?action=profile">👤 Profile</a>
                <a href="index.php?action=logout">🚪 Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>👤 My Profile</h1>

        <?php if(isset($error) && !empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if(isset($success) && !empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Profile Information -->
        <div class="card">
            <h3>Profile Information</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($current_user->username); ?>" disabled>
                    <small style="color: var(--text-muted);">Username cannot be changed</small>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_user->email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($current_user->first_name ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($current_user->last_name ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <input type="text" value="<?php echo ucfirst($current_user->role); ?>" disabled>
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary">✓ Update Profile</button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="card">
            <h3>Change Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <small style="color: var(--text-muted);">Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>

                <button type="submit" name="change_password" class="btn btn-warning">🔒 Change Password</button>
            </form>
        </div>

        <!-- Active Sessions -->
        <div class="card">
            <h3>Active Sessions</h3>
            <p style="color: var(--text-secondary); margin-bottom: 1rem;">Manage your active login sessions across different devices.</p>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Browser</th>
                            <th>Login Time</th>
                            <th>Expires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $session_count = 0;
                        while ($session = $sessions->fetch(PDO::FETCH_ASSOC)): 
                            $session_count++;
                            $is_current = $session['id'] == ($_SESSION['session_id'] ?? '');
                        ?>
                            <tr <?php echo $is_current ? 'style="background: var(--bg-hover);"' : ''; ?>>
                                <td><?php echo htmlspecialchars($session['ip_address']); ?></td>
                                <td style="font-size: 0.875rem;">
                                    <?php 
                                    $ua = $session['user_agent'];
                                    if (strpos($ua, 'Chrome') !== false) echo '🌐 Chrome';
                                    elseif (strpos($ua, 'Firefox') !== false) echo '🦊 Firefox';
                                    elseif (strpos($ua, 'Safari') !== false) echo '🧭 Safari';
                                    elseif (strpos($ua, 'Edge') !== false) echo '🌊 Edge';
                                    else echo '🖥️ ' . substr($ua, 0, 30);
                                    ?>
                                </td>
                                <td><?php echo date('M j, Y H:i', strtotime($session['created_at'])); ?></td>
                                <td><?php echo date('M j, Y H:i', strtotime($session['expires_at'])); ?></td>
                                <td>
                                    <?php if ($is_current): ?>
                                        <span class="badge badge-success">Current Session</span>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Revoke this session?');">
                                                🚫 Revoke
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($session_count == 0): ?>
                            <tr><td colspan="5" class="no-items">No active sessions</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($session_count > 1): ?>
                <form method="POST" style="margin-top: 1rem;">
                    <button type="submit" name="revoke_all" class="btn btn-danger" 
                            onclick="return confirm('Revoke all other sessions except this one?');">
                        🚫 Revoke All Other Sessions
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div style="margin-top: 2rem;">
            <a href="index.php?action=dashboard" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
