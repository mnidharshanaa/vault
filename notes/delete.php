<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /vault/auth/login.php");
    exit;
}

require_once '../config/db.php';

if (isset($_GET['id'])) {
    $note_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Ensure the note belongs to the logged-in user before deleting
    $stmt = mysqli_prepare($conn, "DELETE FROM notes WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $note_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: /vault/index.php?msg=deleted");
    } else {
        echo "Error deleting note: " . mysqli_error($conn);
    }
    exit;
} else {
    header("Location: /vault/index.php");
    exit;
}
?>
