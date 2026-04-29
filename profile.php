<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once 'config/db.php';
$uid = $_SESSION['user_id'];

$error = $success = '';

// Load user
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid"));

// Stats
$total_notes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM notes WHERE user_id=$uid"))['c'];
$total_favs  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM notes WHERE user_id=$uid AND is_favourite=1"))['c'];
$total_links = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM links WHERE user_id=$uid"))['c'];
$total_files = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM files WHERE user_id=$uid"))['c'];
$total_tags  = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(DISTINCT nt.tag_id) AS c FROM note_tags nt
     JOIN notes n ON nt.note_id=n.id WHERE n.user_id=$uid"))['c'];

// Handle name update
if (isset($_POST['update_name'])) {
    $new_name = trim($_POST['name']);
    if (empty($new_name)) {
        $error = "Name cannot be empty.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET name=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $new_name, $uid);
        mysqli_stmt_execute($stmt);
        $_SESSION['user_name'] = $new_name;
        $success = "Name updated successfully!";
        $user['name'] = $new_name;
    }
}

// Handle password update
if (isset($_POST['update_password'])) {
    $current  = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_pass !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt   = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $hashed, $uid);
        mysqli_stmt_execute($stmt);
        $success = "Password updated successfully!";
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

    <!-- Stats row -->
    <div class="stats-row" style="grid-template-columns: repeat(5,1fr); margin-bottom:28px;">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_notes; ?></div>
            <div class="stat-label">Notes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_favs; ?></div>
            <div class="stat-label">Favourites</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_links; ?></div>
            <div class="stat-label">Links</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_files; ?></div>
            <div class="stat-label">Files</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_tags; ?></div>
            <div class="stat-label">Tags used</div>
        </div>
    </div>

    <div class="profile-grid">
        <!-- Update name -->
        <div class="form-card">
            <h2>👤 Update Name</h2>
            <form method="POST" style="margin-top:16px;">
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                           style="opacity:0.5; cursor:not-allowed;">
                </div>
                <div class="form-group">
                    <label>Member Since</label>
                    <input type="text" value="<?php echo date('d M Y', strtotime($user['created_at'])); ?>"
                           disabled style="opacity:0.5; cursor:not-allowed;">
                </div>
                <button type="submit" name="update_name" class="btn btn-primary">Update Name</button>
            </form>
        </div>

        <!-- Change password -->
        <div class="form-card">
            <h2>🔒 Change Password</h2>
            <form method="POST" style="margin-top:16px;">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required placeholder="Min 6 characters">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required placeholder="Repeat new password">
                </div>
                <button type="submit" name="update_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>

    <!-- Danger zone -->
    <div class="form-card" style="margin-top:24px; border-left: 4px solid #e74c3c;">
        <h2 style="color:#e74c3c;">⚠️ Danger Zone</h2>
        <p style="margin-top:12px; color:#aaa; font-size:14px;">
            Deleting your account will permanently remove all your notes, links, files and categories.
            This cannot be undone.
        </p>
        <a href="/vault/auth/delete_account.php"
           onclick="return confirm('Are you absolutely sure? This will delete EVERYTHING permanently.')"
           class="btn btn-danger" style="margin-top:16px;">Delete My Account</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>