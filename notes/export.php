<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once '../config/db.php';
$uid = $_SESSION['user_id'];
$id  = (int)$_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM notes WHERE id=$id AND user_id=$uid");
$note   = mysqli_fetch_assoc($result);
if (!$note) { echo "Note not found."; exit; }

// Get tags
$tags_result = mysqli_query($conn, "SELECT t.name FROM tags t JOIN note_tags nt ON t.id=nt.tag_id WHERE nt.note_id=$id");
$tags = [];
while ($t = mysqli_fetch_assoc($tags_result)) $tags[] = $t['name'];

// Build text content
$text  = "KNOWLEDGE VAULT — NOTE EXPORT\n";
$text .= "==============================\n\n";
$text .= "Title   : " . $note['title'] . "\n";
$text .= "Created : " . date('d M Y, h:i A', strtotime($note['created_at'])) . "\n";
$text .= "Updated : " . date('d M Y, h:i A', strtotime($note['updated_at'])) . "\n";
if (!empty($tags)) {
    $text .= "Tags    : " . implode(', ', $tags) . "\n";
}
$text .= "\n------------------------------\n\n";
$text .= $note['content'];
$text .= "\n\n— Exported from Knowledge Vault\n";

// Force download
$filename = 'note_' . $id . '_' . date('Ymd') . '.txt';
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($text));
echo $text;
exit;
?>