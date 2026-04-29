<?php
session_start();
$_SESSION['dark_mode'] = empty($_SESSION['dark_mode']) ? 1 : 0;
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '/vault/index.php';
// Safety check — only allow relative URLs
if (strpos($redirect, '/') !== 0) $redirect = '/vault/index.php';
header("Location: $redirect");
exit;
?>