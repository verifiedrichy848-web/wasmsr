<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'author') {
    header('Location: login.php');
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']);
    $abstract = trim($_POST['abstract']);
    $keywords = trim($_POST['keywords']);
    $file     = $_FILES['paper_file'];

    // 1. Check PDF format
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        $msg = "<div style='background:#ffebee;color:#c62828;padding:18px;border-radius:10px;margin:20px 0;'>
                  Only PDF files are allowed! Please convert your document to PDF before uploading.
                </div>";
    }
    // 2. Check upload error
    elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File too large (php.ini limit)',
            UPLOAD_ERR_FORM_SIZE  => 'File too large',
            UPLOAD_ERR_PARTIAL    => 'Partial upload',
            UPLOAD_ERR_NO_FILE    => 'No file selected',
        ];
        $error = $errors[$file['error']] ?? 'Unknown error';
        $msg = "<div style='background:#ffebee;color:#c62828;padding:18px;border-radius:10px;margin:20px 0;'>
                  Upload failed: $error
                </div>";
    }
    // FILE IS GOOD — NO WORD COUNT CHECK ANYMORE
    else {
        $upload_dir = "papers/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $target = $upload_dir . time() . "_" . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $target)) {

            // === NEW: Get current active issue and assign paper to it ===
            $current_stmt = $pdo->query("SELECT id, volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1");
            $current_issue = $current_stmt->fetch();

            if (!$current_issue) {
                // Create default current issue if none exists
                $pdo->prepare("INSERT INTO issues (volume, issue_number, is_current, created_at) VALUES (11, 1, 1, NOW())")->execute();
                $current_issue = $pdo->query("SELECT id, volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1")->fetch();
            }

            $issue_id     = $current_issue['id'];
            $volume       = $current_issue['volume'];
            $issue_number = $current_issue['issue_number'];

            // FINAL INSERT — assign to current active issue
            $stmt = $pdo->prepare("INSERT INTO papers 
                (author_id, title, abstract, keywords, file_path, status, submitted_at, published_at, 
                 issue_id, volume, issue_number) 
                VALUES (?, ?, ?, ?, ?, 'submitted', NOW(), NULL, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'], 
                $title, 
                $abstract, 
                $keywords, 
                $target,
                $issue_id,
                $volume,
                $issue_number
            ]);

            $msg = "<div style='background:#e8f5e8;color:#2e7d32;padding:30px;border-radius:15px;margin:30px 0;text-align:center;font-size:24px;border:4px solid #4caf50;'>
                      SUCCESS!<br><br>
                      <strong>$title</strong><br>
                      Paper Submitted to Vol {$volume} No {$issue_number}!
                    </div>";
        } else {
            $msg = "<div style='background:#ffebee;color:#c62828;padding:18px;border-radius:10px;margin:20px 0;'>
                      Failed to save file!
                    </div>";
        }
    }
}
?>

<div class="page-title">Submit New Paper</div>

<div style="background:#e3f2fd;padding:25px;border-radius:15px;margin:30px 0;border-left:6px solid #1976d2;">
    <h3 style="color:#1565c0;margin-top:0;">Submission Requirements</h3>
    <p style="font-size:17px;line-height:1.7;margin:10px 0;">
        <strong>All submissions must meet the following rules:</strong><br><br>
        • Your paper <strong>must be in PDF format only</strong> (Word files not accepted)<br><br>
        • Minimum word count: <strong>5,000 words</strong><br>
        • Maximum word count: <strong>8,000 words</strong><br><br>
        <span style="color:#d32f2f;">Submissions that do not meet these requirements will be automatically rejected.</span>
    </p>
</div>

<?php if ($msg) echo $msg; ?>

<div class="card" style="max-width:800px;margin:0 auto;padding:35px;">
    <form method="POST" enctype="multipart/form-data" id="submitForm">
        <label><strong>Paper Title</strong></label>
        <input type="text" name="title" required style="width:100%;padding:12px;margin:10px 0;border-radius:8px;">

        <label><strong>Abstract (max 300 words)</strong></label>
        <textarea name="abstract" rows="5" maxlength="1500" required style="width:100%;padding:12px;margin:10px 0;border-radius:8px;"></textarea>

        <label><strong>Keywords (comma separated)</strong></label>
        <input type="text" name="keywords" required placeholder="e.g. education, technology, research" style="width:100%;padding:12px;margin:10px 0;border-radius:8px;">

        <label><strong>Upload Full Paper (PDF Only)</strong></label>
        <input type="file" name="paper_file" accept=".pdf" required style="width:100%;padding:12px;margin:15px 0;">

        <button type="submit" class="btn full" style="background:#27ae60;padding:18px;font-size:20px;margin-top:20px;">
            Submit Paper for Review
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>