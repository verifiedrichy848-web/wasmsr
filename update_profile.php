<?php
session_start();
require 'db.php';

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'header.php';

$errors = [];
$success = '';

// Fetch current user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p>User not found.</p>";
    require 'footer.php';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);
    $profile_picture = $_FILES['profile_picture'] ?? null;

    if ($name === '') {
        $errors[] = "Name is required.";
    }

    // Handle profile picture upload
    $profile_name = $user['profile_picture'] ?? '';
    if ($profile_picture && $profile_picture['tmp_name']) {
        $ext = pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
        $profile_name = uniqid().'_'.basename($profile_picture['name']);
        move_uploaded_file($profile_picture['tmp_name'], __DIR__.'/uploads/'.$profile_name);
    }

    // Update database if no errors
    if (empty($errors)) {
        $query = "UPDATE users SET name = :name";
        $params = ['name' => $name, 'id' => $user['id']];

        if ($password !== '') {
            $query .= ", password = :password";
            $params['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($profile_name !== $user['profile_picture']) {
            $query .= ", profile_picture = :profile_picture";
            $params['profile_picture'] = $profile_name;
        }

        $query .= " WHERE id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        $_SESSION['user_name'] = $name;
        $success = "Profile updated successfully!";
    }
}
?>

<div class="page-title">Update My Details</div>

<div class="card" style="max-width:600px; margin:auto;">
    <?php if ($errors): ?>
        <div style="color:red; margin-bottom:12px;">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green; margin-bottom:12px;">
            <p><?= $success ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Name</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($user['name']) ?>">

        <label>New Password (leave blank to keep current)</label>
        <input type="password" name="password">

        <label>Profile Picture</label>
        <input type="file" name="profile_picture" accept="image/*">
        <?php if (!empty($user['profile_picture'])): ?>
            <p>Current: <img src="uploads/<?= htmlspecialchars($user['profile_picture']) ?>" width="100"></p>
        <?php endif; ?>

        <button class="btn" type="submit">Update Details</button>
    </form>
</div>

<?php
require 'footer.php';
?>
