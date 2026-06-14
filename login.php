<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

$default_admin_email = "admin@gmail.com";
$default_admin_password = "AdminBIU";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email === $default_admin_email && $password === $default_admin_password) {
        $_SESSION['user_id'] = 0;
        $_SESSION['user_name'] = "Admin";
        $_SESSION['user_role'] = "admin";
        header('Location:admin_dashboard.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<?php include 'header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Welcome Back</h2>
        <p>Login to continue to your account</p>

        <?php if (!empty($error)): ?>
            <div class="error-alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@example.com" required autocomplete="email">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn full">Login</button>
        </form>

        <p class="bottom-text">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>