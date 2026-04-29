<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, name, password FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: /vault/index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<div class="auth-box">
    <h2>Login</h2>
    <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" required placeholder="you@email.com">

        <label>Password</label>
        <input type="password" name="password" required placeholder="Your password">

        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p class="auth-link">No account? <a href="register.php">Register</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>