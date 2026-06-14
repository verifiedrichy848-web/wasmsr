<?php
session_start();
require 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $error = "Email already registered!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'author')");
        if ($stmt->execute([$name, $email, $password])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'author';
            header('Location: index.php');
            exit;
        } else {
            $error = "Registration failed. Try again.";
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Create Account</h2>
        <p>Join WASMASR Academic Journal today</p>

        <?php if ($error): ?>
            <div class="error-alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="John Doe" required>
            </div>
            <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@example.com" required autocomplete="email">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn full">Create Account</button>
        </form>

        <p class="bottom-text">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
    <div style="text-align: center; margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
    <p style="font-size: 1.1rem; color: #555; margin-bottom: 1rem;">
        Prefer not to create an account yet?
    </p>
    <p style="font-size: 1.15rem; font-weight: 500; margin: 0;">
        You can also submit your paper directly via email:
    </p>
    <a href="mailto:wajsmsr@biu.edu.ng" 
       style="display: inline-block; margin-top: 1rem; padding: 12px 24px; background: #6B21A8; color: white; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 1.1rem; transition: 0.3s;">
        Submit via Email → wajsmsr@biu.edu.ng
    </a>
    <p style="margin-top: 1rem; font-size: 0.95rem; color: #777;">
        Please include your manuscript as a PDF document attachment.
    </p>
</div>
</div>

<?php include 'footer.php'; ?>