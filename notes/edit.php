<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once '../config/db.php';
$uid = $_SESSION['user_id'];
$id  = (int)$_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM notes WHERE id=$id AND user_id=$uid");
$note   = mysqli_fetch_assoc($result);
if (!$note) { echo "Note not found."; exit; }

// Get existing tags for this note
$tags_result = mysqli_query($conn, "SELECT t.name FROM tags t JOIN note_tags nt ON t.id=nt.tag_id WHERE nt.note_id=$id");
$existing_tags = [];
while ($t = mysqli_fetch_assoc($tags_result)) $existing_tags[] = $t['name'];

$cats  = mysqli_query($conn, "SELECT * FROM categories WHERE user_id=$uid ORDER BY name");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $cat_id  = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $fav     = isset($_POST['is_favourite']) ? 1 : 0;
    $tags_raw = trim($_POST['tags']);

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE notes SET title=?, content=?, category_id=?, is_favourite=? WHERE id=? AND user_id=?");
        mysqli_stmt_bind_param($stmt, "ssiiii", $title, $content, $cat_id, $fav, $id, $uid);
        mysqli_stmt_execute($stmt);

        // Remove old tags and re-add
        mysqli_query($conn, "DELETE FROM note_tags WHERE note_id=$id");
        if (!empty($tags_raw)) {
            $tags = array_map('trim', explode(',', $tags_raw));
            foreach ($tags as $tag_name) {
                if ($tag_name === '') continue;
                $tag_name = strtolower($tag_name);
                $ts = mysqli_prepare($conn, "INSERT IGNORE INTO tags (name) VALUES (?)");
                mysqli_stmt_bind_param($ts, "s", $tag_name);
                mysqli_stmt_execute($ts);
                $tr = mysqli_prepare($conn, "SELECT id FROM tags WHERE name=?");
                mysqli_stmt_bind_param($tr, "s", $tag_name);
                mysqli_stmt_execute($tr);
                $tag_id = mysqli_fetch_assoc(mysqli_stmt_get_result($tr))['id'];
                $tl = mysqli_prepare($conn, "INSERT IGNORE INTO note_tags (note_id, tag_id) VALUES (?,?)");
                mysqli_stmt_bind_param($tl, "ii", $id, $tag_id);
                mysqli_stmt_execute($tl);
            }
        }

        header("Location: /vault/notes/view.php?id=$id");
        exit;
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <h2>✏️ Edit Note</h2>
        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars(isset($_POST['title']) ? $_POST['title'] : $note['title']); ?>">
            </div>

            <div class="form-group">
                <label>Content</label>
                <textarea name="content"><?php echo htmlspecialchars(isset($_POST['content']) ? $_POST['content'] : $note['content']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">— No category —</option>
                    <?php
                    mysqli_data_seek($cats, 0);
                    while ($cat = mysqli_fetch_assoc($cats)):
                        $sel = ($cat['id'] == $note['category_id']) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tags</label>
                <input type="text" name="tags" value="<?php echo htmlspecialchars(isset($_POST['tags']) ? $_POST['tags'] : implode(', ', $existing_tags)); ?>">
                <div class="form-hint">Separate tags with commas</div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_favourite" value="1" <?php echo $note['is_favourite'] ? 'checked' : ''; ?>>
                    ⭐ Mark as favourite
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Note</button>
                <a href="/vault/notes/view.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>