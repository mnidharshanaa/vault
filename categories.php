<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once 'config/db.php';
$uid = $_SESSION['user_id'];

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (user_id, name) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "is", $uid, $name);
        mysqli_stmt_execute($stmt);
        $success = "Category added!";
    }
}

if (isset($_GET['delete'])) {
    $cid = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE id=$cid AND user_id=$uid");
    header("Location: /vault/categories.php");
    exit;
}

$cats = mysqli_query($conn, "SELECT * FROM categories WHERE user_id=$uid ORDER BY name");
require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-card" style="max-width:500px; margin-bottom:24px;">
        <h2>📁 Categories</h2>
        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>New Category Name</label>
                <input type="text" name="name" placeholder="e.g. College, Work, AI">
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>
    </div>

    <?php if (mysqli_num_rows($cats) > 0): ?>
    <div class="form-card" style="max-width:500px;">
        <h2 style="margin-bottom:16px;">Your Categories</h2>
        <?php while ($cat = mysqli_fetch_assoc($cats)): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f0f2f5;">
                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                <a href="?delete=<?php echo $cat['id']; ?>"
                   onclick="return confirm('Delete this category?')"
                   class="btn btn-danger btn-sm">Delete</a>
            </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>