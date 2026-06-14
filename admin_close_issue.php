<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Close the issue
    $stmt = $pdo->prepare("UPDATE issues SET is_closed = 1 WHERE id = ?");
    $stmt->execute([$id]);

    // Optional: mark all papers in this issue as published
    $stmt = $pdo->prepare("UPDATE papers SET status = 'published' WHERE issue_code = (SELECT issue_code FROM issues WHERE id = ?)");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Issue closed successfully.";
} else {
    $_SESSION['error'] = "Invalid issue ID.";
}

header("Location: admin_dashboard.php");
exit;
?>