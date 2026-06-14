<?php
session_start();
require_once __DIR__ . '/db.php';

// Only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Auto-mark all new messages as read when admin opens this page
try {
    $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE status = 'new'")->execute();
} catch (Exception $e) {
    // Silent fail - don't break the page
}

// Handle delete message request
if (isset($_GET['delete_message'])) {
    $message_id = (int)$_GET['delete_message'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $_SESSION['success'] = "Message ID $message_id deleted successfully.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete message: " . $e->getMessage();
    }
    header("Location: admin_message.php");
    exit;
}

// Fetch all contact messages
try {
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $messages = [];
}

require_once __DIR__ . '/header.php';
?>

<div class="page-title">Contact Messages</div>

<?php if (isset($_SESSION['success'])): ?>
    <div style="background:#e8f5e8; color:#2e7d32; padding:15px; border-radius:8px; margin:20px 0; text-align:center; font-weight:bold;">
        <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div style="background:#ffebee; color:#c62828; padding:15px; border-radius:8px; margin:20px 0; text-align:center; font-weight:bold;">
        <?= $_SESSION['error'] ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div style="background:#ffebee; color:#c62828; padding:15px; border-radius:8px; margin:20px 0; text-align:center;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (empty($messages)): ?>
    <div class="card" style="text-align:center; padding:40px; background:#f8f9fa; border-radius:12px;">
        <h3 style="color:#555; margin:0 0 15px;">No messages yet.</h3>
        <p style="color:#777;">When someone submits the contact form, messages will appear here.</p>
    </div>
<?php else: ?>
    <div style="overflow-x:auto;">
        <table border="1" cellpadding="12" cellspacing="0" style="width:100%; border-collapse:collapse; margin:20px 0; background:white;">
            <thead>
                <tr style="background:#6B21A8; color:white;">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="text-align:center;"><?= $msg['id'] ?></td>
                        <td><?= htmlspecialchars($msg['name']) ?></td>
                        <td><?= htmlspecialchars($msg['email']) ?></td>
                        <td style="max-width:450px; word-break:break-word; line-height:1.5;">
                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                        </td>
                        <td style="white-space:nowrap;"><?= date('M d, Y H:i', strtotime($msg['created_at'])) ?></td>
                        <td style="text-align:center;">
                            <a href="admin_message.php?delete_message=<?= $msg['id'] ?>" 
                               onclick="return confirm('Are you sure you want to delete this message from <?= htmlspecialchars($msg['name']) ?>? This cannot be undone.');"
                               style="color:#c62828; font-weight:bold; text-decoration:none; padding:6px 12px; border-radius:6px; background:#ffebee;">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>