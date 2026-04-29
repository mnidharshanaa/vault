<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once '../config/db.php';
$uid = $_SESSION['user_id'];

$notes = mysqli_query($conn, "SELECT n.*, c.name AS category_name FROM notes n LEFT JOIN categories c ON n.category_id=c.id WHERE n.user_id=$uid ORDER BY n.updated_at DESC");

require_once '../includes/header.php';
?>

<div class="container">
    <div class="section-header">
        <h2>📋 All Notes</h2>
        <a href="/vault/notes/create.php" class="btn btn-primary btn-sm">+ New Note</a>
    </div>

    <?php if (mysqli_num_rows($notes) === 0): ?>
        <div class="empty-state"><p>No notes yet.</p></div>
    <?php else: ?>
        <div class="notes-grid">
            <?php while ($note = mysqli_fetch_assoc($notes)): ?>
                <a href="/vault/notes/view.php?id=<?php echo $note['id']; ?>"
                   class="note-card <?php echo $note['is_favourite'] ? 'favourite' : ''; ?>">
                    <h3><?php echo htmlspecialchars($note['title']); ?> <?php echo $note['is_favourite'] ? '⭐' : ''; ?></h3>
                    <p><?php echo htmlspecialchars(substr($note['content'], 0, 120)); ?></p>
                    <div class="note-meta">
                        <span><?php echo $note['category_name'] ? '📁 '.$note['category_name'] : ''; ?></span>
                        <span><?php echo date('d M Y', strtotime($note['updated_at'])); ?></span>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>