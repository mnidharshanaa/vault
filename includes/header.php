<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Dark mode: read from session, default off
$dark = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Vault</title>
    <link rel="stylesheet" href="/vault/assets/style.css">
</head>
<body class="<?php echo $dark; ?>">

<?php if (isset($_SESSION['user_id'])): ?>
<nav class="navbar">
    <a href="/vault/index.php" class="nav-brand">🗄️ Knowledge Vault</a>
    <div style="display:flex; align-items:center;">
        <div class="nav-links">
            <a href="/vault/index.php">Dashboard</a>
            <a href="/vault/notes/create.php">+ New Note</a>
            <a href="/vault/search.php">Search</a>
            <a href="/vault/links.php">Links</a>
            <a href="/vault/notes/upload.php">Files</a>
            <a href="/vault/profile.php">Profile</a>
            <a href="/vault/auth/logout.php">Logout</a>
        </div>
        <!-- Dark mode toggle -->
        <form method="POST" action="/vault/toggle_dark.php" style="margin:0;">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
            <button type="submit" class="dark-toggle">
                <?php echo $dark ? '☀️ Light' : '🌙 Dark'; ?>
            </button>
        </form>
    </div>
</nav>
<?php endif; ?>

<div class="container-outer">