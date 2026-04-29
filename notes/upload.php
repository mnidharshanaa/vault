<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once '../config/db.php';
$uid = $_SESSION['user_id'];

$error = $success = '';

$notes = mysqli_query($conn, "SELECT id, title FROM notes WHERE user_id=$uid ORDER BY updated_at DESC");
$cats  = mysqli_query($conn, "SELECT * FROM categories WHERE user_id=$uid ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note_id = !empty($_POST['note_id']) ? (int)$_POST['note_id'] : null;
    $cat_id  = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a valid file.";
    } else {
        $allowed   = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'txt'];
        $max_size  = 5 * 1024 * 1024;
        $original  = basename($_FILES['file']['name']);
        $ext       = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $file_size = $_FILES['file']['size'];
        $file_type = $_FILES['file']['type'];

        if (!in_array($ext, $allowed)) {
            $error = "File type not allowed. Allowed: " . implode(', ', $allowed);
        } elseif ($file_size > $max_size) {
            $error = "File too large. Max size is 5MB.";
        } else {
            $new_name = uniqid('file_', true) . '.' . $ext;
            $dest     = $_SERVER['DOCUMENT_ROOT'] . '/vault/uploads/' . $new_name;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                $stmt = mysqli_prepare($conn,
                    "INSERT INTO files (user_id, note_id, category_id, filename, original_name, file_type)
                     VALUES (?,?,?,?,?,?)"
                );
                mysqli_stmt_bind_param($stmt, "iiisss", $uid, $note_id, $cat_id, $new_name, $original, $file_type);
                mysqli_stmt_execute($stmt);
                $success = "File uploaded successfully!";
            } else {
                $error = "Upload failed. Check that the uploads/ folder exists and is writable.";
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $fid  = (int)$_GET['delete'];
    $frow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT filename FROM files WHERE id=$fid AND user_id=$uid"));
    if ($frow) {
        $fpath = $_SERVER['DOCUMENT_ROOT'] . '/vault/uploads/' . $frow['filename'];
        if (file_exists($fpath)) unlink($fpath);
        mysqli_query($conn, "DELETE FROM files WHERE id=$fid AND user_id=$uid");
    }
    header("Location: /vault/notes/upload.php");
    exit;
}

$files = mysqli_query($conn,
    "SELECT f.*, n.title AS note_title, c.name AS category_name
     FROM files f
     LEFT JOIN notes n ON f.note_id = n.id
     LEFT JOIN categories c ON f.category_id = c.id
     WHERE f.user_id=$uid ORDER BY f.uploaded_at DESC"
);

require_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card" style="margin-bottom:24px;">
        <h2>📁 Upload a File</h2>
        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>File * <span style="color:#aaa;font-weight:400;">(PDF, image, doc, txt — max 5MB)</span></label>
                <input type="file" name="file" required
                       style="padding:8px; border:1px solid #ddd; border-radius:8px; width:100%;">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">— No category —</option>
                    <?php while ($cat = mysqli_fetch_assoc($cats)): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div class="form-hint">
                    Don't see your category? <a href="/vault/categories.php">Add one here</a>
                </div>
            </div>
            <div class="form-group">
                <label>Attach to Note (optional)</label>
                <select name="note_id">
                    <option value="">— No note —</option>
                    <?php while ($n = mysqli_fetch_assoc($notes)): ?>
                        <option value="<?php echo $n['id']; ?>">
                            <?php echo htmlspecialchars($n['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:0;">Upload</button>
        </form>
    </div>

    <div class="section-header">
        <h2>📂 Uploaded Files</h2>
    </div>

    <?php if (mysqli_num_rows($files) === 0): ?>
        <div class="empty-state"><p>No files uploaded yet.</p></div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php while ($file = mysqli_fetch_assoc($files)): ?>
                <div class="note-card" style="border-left-color:#e67e22; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 style="margin-bottom:4px;">
                            <?php echo htmlspecialchars($file['original_name']); ?>
                        </h3>
                        <div style="font-size:13px; color:#aaa;">
                            <?php if ($file['category_name']): ?>
                                📁 <?php echo htmlspecialchars($file['category_name']); ?>
                                &nbsp;|&nbsp;
                            <?php endif; ?>
                            <?php if ($file['note_title']): ?>
                                📝 <?php echo htmlspecialchars($file['note_title']); ?>
                                &nbsp;|&nbsp;
                            <?php endif; ?>
                            <?php echo date('d M Y', strtotime($file['uploaded_at'])); ?>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; flex-shrink:0; margin-left:16px;">
                        <a href="/vault/uploads/<?php echo $file['filename']; ?>"
                           target="_blank" class="btn btn-secondary btn-sm">View</a>
                        <a href="?delete=<?php echo $file['id']; ?>"
                           onclick="return confirm('Delete this file?')"
                           class="btn btn-danger btn-sm">🗑️</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>