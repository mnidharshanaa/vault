<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once '../config/db.php';
$uid = $_SESSION['user_id'];

// Load categories for dropdown
$cats = mysqli_query($conn, "SELECT * FROM categories WHERE user_id=$uid ORDER BY name");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']);
    $content  = trim($_POST['content']);
    $cat_id   = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $fav      = isset($_POST['is_favourite']) ? 1 : 0;
    $tags_raw = trim($_POST['tags']);

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        $cat_val = $cat_id ? $cat_id : "NULL";
        $stmt = mysqli_prepare($conn, "INSERT INTO notes (user_id, category_id, title, content, is_favourite) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissi", $uid, $cat_id, $title, $content, $fav);
        mysqli_stmt_execute($stmt);
        $note_id = mysqli_insert_id($conn);

        // Handle tags
        if (!empty($tags_raw)) {
            $tags = array_map('trim', explode(',', $tags_raw));
            foreach ($tags as $tag_name) {
                if ($tag_name === '') continue;
                $tag_name = strtolower($tag_name);
                // Insert tag if not exists
                $ts = mysqli_prepare($conn, "INSERT IGNORE INTO tags (name) VALUES (?)");
                mysqli_stmt_bind_param($ts, "s", $tag_name);
                mysqli_stmt_execute($ts);
                // Get tag id
                $tr = mysqli_prepare($conn, "SELECT id FROM tags WHERE name=?");
                mysqli_stmt_bind_param($tr, "s", $tag_name);
                mysqli_stmt_execute($tr);
                $tag_id = mysqli_fetch_assoc(mysqli_stmt_get_result($tr))['id'];
                // Link note to tag
                $tl = mysqli_prepare($conn, "INSERT IGNORE INTO note_tags (note_id, tag_id) VALUES (?,?)");
                mysqli_stmt_bind_param($tl, "ii", $note_id, $tag_id);
                mysqli_stmt_execute($tl);
            }
        }

        header("Location: /vault/notes/view.php?id=$note_id");
        exit;
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <h2>📝 New Note</h2>
        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required placeholder="Note title..." value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Content</label>
                <textarea name="content" placeholder="Write your note here..."><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">— No category —</option>
                    <?php while ($cat = mysqli_fetch_assoc($cats)): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <div class="form-hint">Don't see your category? <a href="/vault/categories.php">Add one here</a></div>
            </div>

            <div class="form-group">
                <label>Tags</label>
                <input type="text" name="tags" placeholder="php, project, exam" value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                <div class="form-hint">Separate tags with commas</div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_favourite" value="1"> ⭐ Mark as favourite
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Note</button>
                <a href="/vault/index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>