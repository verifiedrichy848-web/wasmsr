<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ====================== MARK AS UNDER REVIEW ======================
if ($action === 'under_review' && $id > 0) {
    $pdo->prepare("UPDATE papers SET status='under_review' WHERE id=?")->execute([$id]);
    $_SESSION['success'] = "Paper marked as Under Review.";
    header('Location: admin_dashboard.php');
    exit;
}

// ====================== MARK AS ACCEPTED ======================
if ($action === 'accept' && $id > 0) {
    $pdo->prepare("UPDATE papers SET status='accepted' WHERE id=?")->execute([$id]);
    $_SESSION['success'] = "Paper accepted successfully.";
    header('Location: admin_dashboard.php');
    exit;
}

// ====================== PUBLISH PAPER (only accepted papers) ======================
if ($action === 'approve' && $id > 0) {
    // Confirm paper is accepted before publishing
    $check = $pdo->prepare("SELECT status FROM papers WHERE id = ?");
    $check->execute([$id]);
    $paper_status = $check->fetchColumn();

    // Get current active issue
    $stmt = $pdo->prepare("SELECT id, volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1");
    $stmt->execute();
    $current = $stmt->fetch();

    if (!$current) {
        $pdo->prepare("INSERT INTO issues (volume, issue_number, is_current, created_at) VALUES (11, 1, 1, NOW()) ON DUPLICATE KEY UPDATE is_current = 1")->execute();
        $current = $pdo->query("SELECT id, volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1")->fetch();
    }

    $stmt = $pdo->prepare("UPDATE papers SET 
        status      = 'published',
        issue_id    = ?,
        volume      = ?,
        issue_number = ?,
        published_at = NOW()
        WHERE id = ?");
    $stmt->execute([$current['id'], $current['volume'], $current['issue_number'], $id]);

    $_SESSION['success'] = "&#9989; Paper published in Vol {$current['volume']} No {$current['issue_number']}.";
    header('Location: admin_dashboard.php');
    exit;
}

// ====================== CLOSE CURRENT ISSUE & OPEN NEXT ======================
if ($action === 'close_issue' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $next_volume       = (int)$_POST['next_volume'];
    $next_issue_number = (int)$_POST['next_issue_number'];

    if ($next_volume < 1 || $next_issue_number < 1) {
        $_SESSION['error'] = "Volume and Issue Number must be valid.";
        header('Location: admin_dashboard.php');
        exit;
    }

    // Close the current issue
    $pdo->prepare("UPDATE issues SET is_current = 0, is_closed = 1, closed_at = NOW() WHERE is_current = 1")->execute();

    // Generate issue_code for the new issue
    $issue_code = "vol{$next_volume}-no{$next_issue_number}";

    // Check if this issue already exists
    $check = $pdo->prepare("SELECT id FROM issues WHERE volume = ? AND issue_number = ?");
    $check->execute([$next_volume, $next_issue_number]);
    $existing = $check->fetch();

    if ($existing) {
        $pdo->prepare("UPDATE issues SET is_current = 1, is_closed = 0, closed_at = NULL WHERE id = ?")
            ->execute([$existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO issues (issue_code, volume, issue_number, is_current, is_closed, created_at) 
                       VALUES (?, ?, ?, 1, 0, NOW())")
            ->execute([$issue_code, $next_volume, $next_issue_number]);
    }

    $_SESSION['success'] = "Issue closed. New current issue: Vol {$next_volume} No {$next_issue_number}";
    header('Location: admin_dashboard.php');
    exit;
}

// ====================== REJECT ======================
if ($action === 'reject' && $id > 0) {
    $pdo->prepare("UPDATE papers SET status='rejected' WHERE id=?")->execute([$id]);
    $_SESSION['success'] = "Paper rejected.";
    header('Location: admin_dashboard.php');
    exit;
}

// ====================== DELETE PAPER ======================
if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare("SELECT file_path FROM papers WHERE id=?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();
    if ($file && file_exists(__DIR__ . '/' . $file)) {
        @unlink(__DIR__ . '/' . $file);
    }
    $pdo->prepare("DELETE FROM papers WHERE id=?")->execute([$id]);
    $_SESSION['success'] = "Paper deleted.";
    header('Location: admin_dashboard.php');
    exit;
}

// ====================== DELETE VOLUME ======================
if ($action === 'delete_volume' && !empty($_POST['volume'])) {
    $volume_to_delete = (int)$_POST['volume'];

    $papers_stmt = $pdo->prepare("SELECT file_path FROM papers WHERE volume = ?");
    $papers_stmt->execute([$volume_to_delete]);
    $papers_list = $papers_stmt->fetchAll();

    foreach ($papers_list as $p) {
        if ($p['file_path'] && file_exists(__DIR__ . '/' . $p['file_path'])) {
            @unlink(__DIR__ . '/' . $p['file_path']);
        }
    }

    $pdo->prepare("DELETE FROM papers WHERE volume = ?")->execute([$volume_to_delete]);

    // Also delete archive files for this volume
    foreach (['pdf','doc','docx'] as $ext) {
        $pattern = __DIR__ . "/archives/vol{$volume_to_delete}-*.{$ext}";
        foreach (glob($pattern) ?: [] as $full_file) {
            @unlink($full_file);
        }
    }

    $_SESSION['success'] = "Volume {$volume_to_delete} completely deleted.";
    header('Location: admin_dashboard.php');
    exit;
}

// ====================== DELETE FULL ISSUE ======================
if ($action === 'delete_full_issue' && !empty($_POST['filename'])) {
    $filename = basename($_POST['filename']);
    $filepath = __DIR__ . "/archives/" . $filename;

    $deleted_something = false;
    $messages = [];

    // Delete physical file if it exists
    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            $messages[] = "&#9989; File deleted: " . $filename;
            $deleted_something = true;
        } else {
            $messages[] = "&#10060; Failed to delete file (permission problem): " . $filename;
        }
    }

    // Parse volume and issue number from filename (e.g. vol11-no2.pdf)
    if (preg_match('/vol(\d+)-no(\d+)/i', $filename, $matches)) {
        $del_volume = (int)$matches[1];
        $del_issue  = (int)$matches[2];

        // Delete the closed issue record from the issues table
        $del_stmt = $pdo->prepare("DELETE FROM issues WHERE volume = ? AND issue_number = ? AND is_closed = 1");
        $del_stmt->execute([$del_volume, $del_issue]);

        if ($del_stmt->rowCount() > 0) {
            $messages[] = "&#9989; Closed issue record removed: Vol {$del_volume} No {$del_issue}";
            $deleted_something = true;
        }

        // Delete all published papers in that specific issue
        $papers_to_delete = $pdo->prepare("SELECT file_path FROM papers WHERE volume = ? AND issue_number = ? AND status = 'published'");
        $papers_to_delete->execute([$del_volume, $del_issue]);
        $issue_papers = $papers_to_delete->fetchAll();

        foreach ($issue_papers as $ip) {
            if ($ip['file_path'] && file_exists(__DIR__ . '/' . $ip['file_path'])) {
                @unlink(__DIR__ . '/' . $ip['file_path']);
            }
        }

        $del_papers_stmt = $pdo->prepare("DELETE FROM papers WHERE volume = ? AND issue_number = ? AND status = 'published'");
        $del_papers_stmt->execute([$del_volume, $del_issue]);

        if ($del_papers_stmt->rowCount() > 0) {
            $messages[] = "&#9989; " . $del_papers_stmt->rowCount() . " paper(s) in this issue removed.";
            $deleted_something = true;
        }
    }

    if ($deleted_something) {
        $_SESSION['success'] = implode("<br>", $messages);
    } else {
        $_SESSION['error'] = "&#10060; Nothing found to delete for: " . $filename;
    }

    header('Location: admin_dashboard.php');
    exit;
}

// Fallback redirect
header('Location: admin_dashboard.php');
exit;
?>