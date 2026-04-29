<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once 'config/db.php';
$uid = $_SESSION['user_id'];

$error = $success = '';

$cats = mysqli_query($conn, "SELECT * FROM categories WHERE user_id=$uid ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url    = trim($_POST['url']);
    $title  = trim($_POST['title']);
    $desc   = trim($_POST['description']);
    $cat_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    if (empty($url)) {
        $error = "URL is required.";
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO links (user_id, url, title, description, category_id) VALUES (?,?,?,?,?)"
        );
        mysqli_stmt_bind_param($stmt, "isssi", $uid, $url, $title, $desc, $cat_id);
        mysqli_stmt_execute($stmt);
        $success = "Link saved!";
    }
}

if (isset($_GET['delete'])) {
    $lid = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM links WHERE id=$lid AND user_id=$uid");
    header("Location: /vault/links.php");
    exit;
}

$links = mysqli_query($conn,
    "SELECT l.*, c.name AS category_name
     FROM links l
     LEFT JOIN categories c ON l.category_id = c.id
     WHERE l.user_id=$uid ORDER BY l.saved_at DESC"
);

require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-card" style="margin-bottom:24px;">
        <h2>🔗 Save a Link</h2>
        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>URL *</label>
                <input type="url" name="url" required placeholder="https://example.com">
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" placeholder="Give it a short name">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" style="min-height:80px;"
                          placeholder="Why are you saving this?"></textarea>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">— No category —</option>
                    <?php
                    mysqli_data_seek($cats, 0);
                    while ($cat = mysqli_fetch_assoc($cats)):
                    ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div class="form-hint">
                    Don't see your category? <a href="/vault/categories.php">Add one here</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:0;">Save Link</button>
        </form>
    </div>

    <div class="section-header">
        <h2>📚 Saved Links
            <span style="font-size:14px;color:#aaa;font-weight:400;">
                (<?php echo mysqli_num_rows($links); ?>)
            </span>
        </h2>
    </div>

    <?php if (mysqli_num_rows($links) === 0): ?>
        <div class="empty-state"><p>No links saved yet.</p></div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:16px;">
            <?php
            mysqli_data_seek($links, 0);
            while ($link = mysqli_fetch_assoc($links)):
            ?>
                <div class="note-card" style="border-left-color:#1abc9c;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div style="flex:1; min-width:0;">
                            <h3 style="margin-bottom:6px;">
                                <?php echo $link['title'] ? htmlspecialchars($link['title']) : 'Untitled Link'; ?>
                            </h3>
                            <a href="<?php echo htmlspecialchars($link['url']); ?>"
                               target="_blank"
                               style="font-size:13px; color:#6c63ff; word-break:break-all;">
                                <?php echo htmlspecialchars($link['url']); ?>
                            </a>
                            <?php if ($link['description']): ?>
                                <p style="margin-top:8px; font-size:14px; color:#777;">
                                    <?php echo htmlspecialchars($link['description']); ?>
                                </p>
                            <?php endif; ?>
                            <div class="note-meta" style="margin-top:10px;">
                                <?php if ($link['category_name']): ?>
                                    <span>📁 <?php echo htmlspecialchars($link['category_name']); ?></span>
                                <?php endif; ?>
                                <span><?php echo date('d M Y', strtotime($link['saved_at'])); ?></span>
                            </div>
                        </div>
                        <a href="?delete=<?php echo $link['id']; ?>"
                           onclick="return confirm('Delete this link?')"
                           class="btn btn-danger btn-sm" style="margin-left:16px; margin-top:0; flex-shrink:0;">
                            🗑️
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>