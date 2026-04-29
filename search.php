<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once 'config/db.php';
$uid = $_SESSION['user_id'];

$note_results = $file_results = $link_results = null;
$query = '';

if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $query = trim($_GET['q']);
    $like  = "%$query%";

    // Search notes (by title, content, tag)
    $stmt = mysqli_prepare($conn,
        "SELECT DISTINCT n.id, n.title, n.content, n.is_favourite,
                n.updated_at, c.name AS category_name
         FROM notes n
         LEFT JOIN note_tags nt ON n.id = nt.note_id
         LEFT JOIN tags t ON nt.tag_id = t.id
         LEFT JOIN categories c ON n.category_id = c.id
         WHERE n.user_id = ?
           AND (n.title LIKE ? OR n.content LIKE ? OR t.name LIKE ?)
         ORDER BY n.updated_at DESC"
    );
    mysqli_stmt_bind_param($stmt, "isss", $uid, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $note_results = mysqli_stmt_get_result($stmt);

    // Search files (by original name, category name)
    $stmt2 = mysqli_prepare($conn,
        "SELECT f.*, c.name AS category_name, n.title AS note_title
         FROM files f
         LEFT JOIN categories c ON f.category_id = c.id
         LEFT JOIN notes n ON f.note_id = n.id
         WHERE f.user_id = ?
           AND (f.original_name LIKE ? OR c.name LIKE ?)
         ORDER BY f.uploaded_at DESC"
    );
    mysqli_stmt_bind_param($stmt2, "iss", $uid, $like, $like);
    mysqli_stmt_execute($stmt2);
    $file_results = mysqli_stmt_get_result($stmt2);

    // Search links (by title, description, url, category name)
    $stmt3 = mysqli_prepare($conn,
        "SELECT l.*, c.name AS category_name
         FROM links l
         LEFT JOIN categories c ON l.category_id = c.id
         WHERE l.user_id = ?
           AND (l.title LIKE ? OR l.description LIKE ? OR l.url LIKE ? OR c.name LIKE ?)
         ORDER BY l.saved_at DESC"
    );
    mysqli_stmt_bind_param($stmt3, "issss", $uid, $like, $like, $like, $like);
    mysqli_stmt_execute($stmt3);
    $link_results = mysqli_stmt_get_result($stmt3);
}

// Helper: highlight matched keyword
function highlight($text, $query) {
    $safe_text  = htmlspecialchars($text);
    $safe_query = preg_quote(htmlspecialchars($query), '/');
    return preg_replace(
        '/(' . $safe_query . ')/i',
        '<mark style="background:#fff3b0; border-radius:3px; padding:0 2px;">$1</mark>',
        $safe_text
    );
}

require_once 'includes/header.php';
?>

<div class="container">
    <!-- Search bar -->
    <div class="form-card" style="margin-bottom:28px;">
        <h2>🔍 Search Everything</h2>
        <form method="GET" style="display:flex; gap:12px; margin-top:16px;">
            <input type="text" name="q"
                   value="<?php echo htmlspecialchars($query); ?>"
                   placeholder="Search notes, files and links by keyword or category..."
                   style="flex:1; padding:10px 14px; border:1px solid #ddd;
                          border-radius:8px; font-size:15px; outline:none;">
            <button type="submit" class="btn btn-primary" style="margin-top:0;">Search</button>
        </form>
    </div>

    <?php if ($query !== ''): ?>

        <?php
        $note_count = $note_results ? mysqli_num_rows($note_results) : 0;
        $file_count = $file_results ? mysqli_num_rows($file_results) : 0;
        $link_count = $link_results ? mysqli_num_rows($link_results) : 0;
        $total      = $note_count + $file_count + $link_count;
        ?>

        <!-- Summary bar -->
        <div style="display:flex; gap:12px; margin-bottom:28px; flex-wrap:wrap;">
            <div style="background:#ede9ff; color:#6c63ff; padding:8px 18px;
                        border-radius:20px; font-size:14px; font-weight:600;">
                📝 <?php echo $note_count; ?> Notes
            </div>
            <div style="background:#fef3e2; color:#e67e22; padding:8px 18px;
                        border-radius:20px; font-size:14px; font-weight:600;">
                📁 <?php echo $file_count; ?> Files
            </div>
            <div style="background:#e8f8f4; color:#1abc9c; padding:8px 18px;
                        border-radius:20px; font-size:14px; font-weight:600;">
                🔗 <?php echo $link_count; ?> Links
            </div>
            <div style="background:#f0f2f5; color:#888; padding:8px 18px;
                        border-radius:20px; font-size:14px;">
                <?php echo $total; ?> total results for
                "<strong><?php echo htmlspecialchars($query); ?></strong>"
            </div>
        </div>

        <!-- Notes section -->
        <div class="section-header" style="margin-bottom:14px;">
            <h2>📝 Notes
                <span style="font-size:14px; color:#aaa; font-weight:400;">
                    (<?php echo $note_count; ?>)
                </span>
            </h2>
        </div>

        <?php if ($note_count === 0): ?>
            <div class="empty-state" style="padding:24px;">
                <p>No notes matched.</p>
            </div>
        <?php else: ?>
            <div class="notes-grid" style="margin-bottom:36px;">
                <?php while ($note = mysqli_fetch_assoc($note_results)): ?>
                    <a href="/vault/notes/view.php?id=<?php echo $note['id']; ?>"
                       class="note-card <?php echo $note['is_favourite'] ? 'favourite' : ''; ?>">
                        <h3><?php echo highlight($note['title'], $query); ?></h3>
                        <p><?php echo highlight(substr($note['content'], 0, 120), $query); ?></p>
                        <div class="note-meta">
                            <?php if ($note['category_name']): ?>
                                <span>📁 <?php echo htmlspecialchars($note['category_name']); ?></span>
                            <?php endif; ?>
                            <span><?php echo date('d M Y', strtotime($note['updated_at'])); ?></span>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- Files section -->
        <div class="section-header" style="margin-bottom:14px;">
            <h2>📁 Files
                <span style="font-size:14px; color:#aaa; font-weight:400;">
                    (<?php echo $file_count; ?>)
                </span>
            </h2>
        </div>

        <?php if ($file_count === 0): ?>
            <div class="empty-state" style="padding:24px;">
                <p>No files matched.</p>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:36px;">
                <?php while ($file = mysqli_fetch_assoc($file_results)): ?>
                    <div class="note-card"
                         style="border-left-color:#e67e22; display:flex;
                                justify-content:space-between; align-items:center;">
                        <div>
                            <h3 style="margin-bottom:4px;">
                                <?php echo highlight($file['original_name'], $query); ?>
                            </h3>
                            <div style="font-size:13px; color:#aaa;">
                                <?php if ($file['category_name']): ?>
                                    📁 <?php echo highlight($file['category_name'], $query); ?>
                                    &nbsp;|&nbsp;
                                <?php endif; ?>
                                <?php if ($file['note_title']): ?>
                                    📝 <?php echo htmlspecialchars($file['note_title']); ?>
                                    &nbsp;|&nbsp;
                                <?php endif; ?>
                                <?php echo date('d M Y', strtotime($file['uploaded_at'])); ?>
                            </div>
                        </div>
                        <a href="/vault/uploads/<?php echo $file['filename']; ?>"
                           target="_blank" class="btn btn-secondary btn-sm"
                           style="flex-shrink:0; margin-left:16px;">
                            View
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- Links section -->
        <div class="section-header" style="margin-bottom:14px;">
            <h2>🔗 Links
                <span style="font-size:14px; color:#aaa; font-weight:400;">
                    (<?php echo $link_count; ?>)
                </span>
            </h2>
        </div>

        <?php if ($link_count === 0): ?>
            <div class="empty-state" style="padding:24px;">
                <p>No links matched.</p>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php while ($link = mysqli_fetch_assoc($link_results)): ?>
                    <div class="note-card" style="border-left-color:#1abc9c;">
                        <h3 style="margin-bottom:6px;">
                            <?php echo highlight($link['title'] ?: 'Untitled Link', $query); ?>
                        </h3>
                        <a href="<?php echo htmlspecialchars($link['url']); ?>"
                           target="_blank"
                           style="font-size:13px; color:#6c63ff; word-break:break-all;">
                            <?php echo highlight($link['url'], $query); ?>
                        </a>
                        <?php if ($link['description']): ?>
                            <p style="margin-top:8px; font-size:14px; color:#777;">
                                <?php echo highlight($link['description'], $query); ?>
                            </p>
                        <?php endif; ?>
                        <div class="note-meta" style="margin-top:10px;">
                            <?php if ($link['category_name']): ?>
                                <span>📁 <?php echo highlight($link['category_name'], $query); ?></span>
                            <?php endif; ?>
                            <span><?php echo date('d M Y', strtotime($link['saved_at'])); ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>