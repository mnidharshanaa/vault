<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once '../config/db.php';
$uid = $_SESSION['user_id'];
$id  = (int)$_GET['id'];

$result = mysqli_query($conn, "SELECT n.*, c.name AS category_name FROM notes n LEFT JOIN categories c ON n.category_id = c.id WHERE n.id=$id AND n.user_id=$uid");
$note = mysqli_fetch_assoc($result);

if (!$note) { echo "Note not found."; exit; }

// Get tags
$tags_result = mysqli_query($conn, "SELECT t.name FROM tags t JOIN note_tags nt ON t.id=nt.tag_id WHERE nt.note_id=$id");

require_once '../includes/header.php';
?>

<div class="container">
    <div class="note-view-card">
        <h1><?php echo htmlspecialchars($note['title']); ?> <?php echo $note['is_favourite'] ? '⭐' : ''; ?></h1>
        <div class="note-view-meta">
            <?php if ($note['category_name']): ?>
                <span>📁 <?php echo htmlspecialchars($note['category_name']); ?> &nbsp;|&nbsp; </span>
            <?php endif; ?>
            <span>Created: <?php echo date('d M Y, h:i A', strtotime($note['created_at'])); ?></span>
            &nbsp;|&nbsp;
            <span>Updated: <?php echo date('d M Y, h:i A', strtotime($note['updated_at'])); ?></span>
        </div>

        <?php
        $tags = [];
        while ($tag = mysqli_fetch_assoc($tags_result)) $tags[] = $tag['name'];
        if (!empty($tags)):
        ?>
        <div class="tag-list">
            <?php foreach ($tags as $tag): ?>
                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="note-view-body" style="margin-top: 28px;">
            <?php echo nl2br(htmlspecialchars($note['content'])); ?>
        </div>

        <div class="note-view-actions">
        <a href="/vault/notes/edit.php?id=<?php echo $note['id']; ?>" class="btn btn-secondary">✏️ Edit</a>
        <a href="/vault/notes/export.php?id=<?php echo $note['id']; ?>" class="btn btn-secondary">⬇️ Export .txt</a>
        <a href="/vault/notes/delete.php?id=<?php echo $note['id']; ?>"
       class="btn btn-danger"
       onclick="return confirm('Delete this note? This cannot be undone.')">🗑️ Delete</a>
        <a href="/vault/index.php" class="btn btn-secondary">← Back</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>