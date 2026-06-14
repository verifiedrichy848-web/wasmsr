<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/db.php';

// Handle form submission
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (empty($errors)) {
        // Save message to database
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        $success = 'Thank you! Your message has been received.';
        $name = $email = $message = ''; // clear form
    }
}
?>

<div class="page-title">Contact Us</div>

<div class="grid" style="gap:30px; margin-bottom:40px;">
    <!-- Contact Form -->
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

        <form method="POST">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" placeholder="Your name" value="<?= htmlspecialchars($name ?? '') ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Your email" value="<?= htmlspecialchars($email ?? '') ?>" required>

            <label for="message">Message</label>
            <textarea name="message" id="message" placeholder="Your message" rows="6" required><?= htmlspecialchars($message ?? '') ?></textarea>

            <button class="btn" type="submit">Send Message</button>
        </form>
    </div>

    <!-- Contact Info & Map -->
    <div class="card" style="max-width:600px; margin:auto;">
        <h3 style="color:var(--purple); margin-bottom:12px;">Our Contact Info</h3>
        <p><strong>Email:</strong> wajsmsr@biu.edu.ng</p>
        <p><strong>Phone:</strong> +234 8053314506, +234 8038503165</p>
        <p><strong>Address:</strong> Benson Idahosa University, Benin City, Nigeria</p>

        <div style="margin-top:20px; border-radius:10px; overflow:hidden;">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.847384699626!2d5.6014360759439645!3d6.2837838937051576!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1040d17858946197%3A0xc4ad4ccb50edbc3d!2sBenson%20Idahosa%20University!5e0!3m2!1sen!2sng!4v1764172831354!5m2!1sen!2sng" 
                width="100%" 
                height="250" 
                style="border:0; border-radius:10px;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
