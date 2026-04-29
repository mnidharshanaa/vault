<?php
session_start();
session_destroy();
header("Location: /vault/auth/login.php");
exit;
?>