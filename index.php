<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /vault/auth/login.php");
    exit;
}
require_once 'config/db.php';
$uid = $_SESSION['user_id'];

// Stats
$total_notes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM notes WHERE user_id=$uid"))['c'];
$total_favs  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM notes WHERE user_id=$uid AND is_favourite=1"))['c'];
$total_links = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM links WHERE user_id=$uid"))['c'];

// Recent notes
$notes = mysqli_query($conn, "SELECT * FROM notes WHERE user_id=$uid ORDER BY updated_at DESC LIMIT 12");

require_once 'includes/header.php';
?>

<div class="page-wrapper">
    <aside class="sidebar">
        <div class="sidebar-card">
            <h3>Menu</h3>
            <a href="/vault/index.php" class="active">🏠 Dashboard</a>
            <a href="/vault/notes/create.php">📝 New Note</a>
            <a href="/vault/notes/index.php">📋 All Notes</a>
            <a href="/vault/notes/favourites.php">⭐ Favourites</a>
            <a href="/vault/links.php">🔗 Links</a>
            <a href="/vault/search.php">🔍 Search</a>
            <a href="/vault/notes/upload.php">📁 Files</a>
        </div>
        <div class="sidebar-card">
            <h3>Account</h3>
            <a href="/vault/auth/logout.php">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_notes; ?></div>
                <div class="stat-label">Total Notes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_favs; ?></div>
                <div class="stat-label">Favourites</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_links; ?></div>
                <div class="stat-label">Saved Links</div>
            </div>
        </div>

        <div class="section-header">
            <h2>Recent Notes</h2>
            <a href="/vault/notes/create.php" class="btn btn-primary btn-sm">+ New Note</a>
        </div>

        <?php if (mysqli_num_rows($notes) === 0): ?>
            <div class="empty-state">
                <p>No notes yet. Create your first one!</p>
                <a href="/vault/notes/create.php" class="btn btn-primary">+ Create Note</a>
            </div>
        <?php else: ?>
            <div class="notes-grid">
                <?php while ($note = mysqli_fetch_assoc($notes)): ?>
                    <a href="/vault/notes/view.php?id=<?php echo $note['id']; ?>"
                       class="note-card <?php echo $note['is_favourite'] ? 'favourite' : ''; ?>">
                        <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($note['content'], 0, 120)); ?></p>
                        <div class="note-meta">
                            <span><?php echo date('d M Y', strtotime($note['updated_at'])); ?></span>
                            <?php echo $note['is_favourite'] ? '<span>⭐</span>' : ''; ?>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>