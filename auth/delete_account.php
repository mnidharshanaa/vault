<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /vault/auth/login.php"); exit; }
require_once '../config/db.php';
$uid = $_SESSION['user_id'];

// Delete all uploaded files from disk first
$files = mysqli_query($conn, "SELECT filename FROM files WHERE user_id=$uid");
while ($f = mysqli_fetch_assoc($files)) {
    $path = $_SERVER['DOCUMENT_ROOT'] . '/vault/uploads/' . $f['filename'];
    if (file_exists($path)) unlink($path);
}

// Delete user — all related data deleted by CASCADE
mysqli_query($conn, "DELETE FROM users WHERE id=$uid");

session_destroy();
header("Location: /vault/auth/register.php");
exit;
?>