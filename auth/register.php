<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2  = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt2, "sss", $name, $email, $hashed);
            if (mysqli_stmt_execute($stmt2)) {
                $success = "Account created! <a href='login.php'>Login now</a>";
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
    }
}
?>

<div class="auth-box">
    <h2>Create Account</h2>
    <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

    <form method="POST">
        <label>Name</label>
        <input type="text" name="name" required placeholder="Your name">

        <label>Email</label>
        <input type="email" name="email" required placeholder="you@email.com">

        <label>Password</label>
        <input type="password" name="password" required placeholder="Min 6 characters">

        <label>Confirm Password</label>
        <input type="password" name="confirm" required placeholder="Repeat password">

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>