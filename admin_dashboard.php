<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ====================== HANDLE MANUAL PAPER UPLOAD ======================
// Must be BEFORE header.php to avoid output issues
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_upload'])) {
    $title        = trim($_POST['title'] ?? '');
    $abstract     = trim($_POST['abstract'] ?? '');
    $keywords     = trim($_POST['keywords'] ?? '');
    $author_name  = trim($_POST['author'] ?? '');
    $status       = $_POST['status'] ?? 'published';
    $vol_input    = (int)($_POST['volume'] ?? 11);
    $iss_input    = (int)($_POST['issue_number'] ?? 1);
    $file         = $_FILES['paper_file'] ?? null;
    $upload_msg   = '';
    $upload_ok    = false;

    if (empty($title) || empty($abstract) || empty($author_name)) {
        $upload_msg = "&#10060; Title, Abstract, and Author Name are required.";
    } elseif (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $err_map = [
            UPLOAD_ERR_INI_SIZE  => 'File too large (server php.ini limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
            UPLOAD_ERR_PARTIAL   => 'File only partially uploaded — try again',
            UPLOAD_ERR_NO_FILE   => 'No file was selected',
        ];
        $upload_msg = "&#10060; Upload error: " . ($err_map[$file['error'] ?? 0] ?? 'Unknown error code ' . ($file['error'] ?? '?'));
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $upload_msg = "&#10060; Only PDF files are allowed for individual papers.";
        } else {
            $upload_dir = __DIR__ . "/papers/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $filename = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
            $target   = $upload_dir . $filename;
            $rel_path = "papers/" . $filename;

            if (move_uploaded_file($file['tmp_name'], $target)) {

                // Get or create author — use admin id as fallback
                $author_stmt = $pdo->prepare("SELECT id FROM users WHERE name = ? LIMIT 1");
                $author_stmt->execute([$author_name]);
                $author_row = $author_stmt->fetch();
                $author_id  = $author_row ? $author_row['id'] : $_SESSION['user_id'];

                // KEY FIX: Get or AUTO-CREATE the issue record
                $issue_check = $pdo->prepare("SELECT id, is_current, is_closed FROM issues WHERE volume = ? AND issue_number = ? LIMIT 1");
                $issue_check->execute([$vol_input, $iss_input]);
                $issue_row = $issue_check->fetch();

                if ($issue_row) {
                    $issue_id = $issue_row['id'];
                } else {
                    // Issue doesn't exist yet — create it as CLOSED (past issue going to archives)
                    $create_issue = $pdo->prepare("
                        INSERT INTO issues (volume, issue_number, is_current, is_closed, closed_at, created_at)
                        VALUES (?, ?, 0, 1, NOW(), NOW())
                    ");
                    $create_issue->execute([$vol_input, $iss_input]);
                    $issue_id = $pdo->lastInsertId();
                }

                // Insert the paper
                $stmt = $pdo->prepare("
                    INSERT INTO papers 
                    (author_id, title, abstract, keywords, file_path, status, 
                     volume, issue_number, issue_id, submitted_at, published_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([
                    $author_id, $title, $abstract, $keywords,
                    $rel_path, $status, $vol_input, $iss_input, $issue_id
                ]);

                $upload_ok  = true;
                $upload_msg = "&#9989; SUCCESS! <strong>" . htmlspecialchars($title) . "</strong> uploaded as Vol {$vol_input} No {$iss_input}. It will appear in Archives with a View Papers button.";
            } else {
                $upload_msg = "&#10060; Failed to save file to server. Check that the <strong>papers/</strong> folder exists and has write permission (755).";
            }
        }
    }

    $_SESSION['upload_msg'] = $upload_msg;
    $_SESSION['upload_ok']  = $upload_ok;
    // Store last used volume/issue so form remembers it
    $_SESSION['last_vol']   = $vol_input;
    $_SESSION['last_iss']   = $iss_input;
    header('Location: admin_dashboard.php#upload-section');
    exit;
}

require_once __DIR__ . '/header.php';

// === GET CURRENT ACTIVE ISSUE ===
$current_stmt  = $pdo->query("SELECT id, volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1");
$current_issue = $current_stmt->fetch();

if (!$current_issue) {
    $pdo->prepare("INSERT INTO issues (volume, issue_number, is_current, created_at) VALUES (11, 1, 1, NOW())")->execute();
    $current_issue = $pdo->query("SELECT id, volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1")->fetch();
}

$volume   = $current_issue['volume'];
$issue_no = $current_issue['issue_number'];

// Fetch all papers
$papersStmt = $pdo->prepare("SELECT p.*, u.name AS author_name FROM papers p JOIN users u ON p.author_id = u.id ORDER BY p.submitted_at DESC");
$papersStmt->execute();
$papers = $papersStmt->fetchAll();

// Fetch registered authors
$authorsStmt = $pdo->prepare("SELECT * FROM users WHERE role='author' ORDER BY name ASC");
$authorsStmt->execute();
$authors = $authorsStmt->fetchAll();

// Session messages
if (isset($_SESSION['success'])) {
    echo "<div style='background:#e8f5e8;color:#2e7d32;padding:20px;border-radius:12px;margin:20px 0;text-align:center;font-weight:bold;'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['issue_closed_msg'])) {
    echo "<div style='background:#e8f5e8;color:#2e7d32;padding:28px;border-radius:15px;margin:30px 0;text-align:center;font-size:22px;border:4px solid #4caf50;box-shadow:0 10px 30px rgba(76,175,80,0.25);font-weight:bold;'>
            {$_SESSION['issue_closed_msg']}
          </div>";
    unset($_SESSION['issue_closed_msg']);
}

// Upload result message
$upload_msg = $_SESSION['upload_msg'] ?? '';
$upload_ok  = $_SESSION['upload_ok']  ?? false;
unset($_SESSION['upload_msg'], $_SESSION['upload_ok']);

// Remember last used volume/issue for the form
$form_vol = $_SESSION['last_vol'] ?? $volume;
$form_iss = $_SESSION['last_iss'] ?? $issue_no;
unset($_SESSION['last_vol'], $_SESSION['last_iss']);
?>

<div class="page-title">Admin Dashboard</div>

<!-- LIVE ISSUE COUNTER -->
<div class="card" style="background:#fff8e1;padding:20px;text-align:center;border:2px solid #ffca28;margin-bottom:25px;">
    <h3 style="margin:0 0 10px;color:#e65100;">
        Current Active Issue: <strong>Volume <?= $volume ?> &bull; No <?= $issue_no ?></strong>
    </h3>
    <p style="font-size:20px;margin:10px 0;font-weight:bold;color:#d84315;">
        All new approved papers will be published here
    </p>
    <p style="color:#555;">Admin has full control. Close issue manually when ready.</p>
</div>

<!-- CLOSE CURRENT ISSUE + OPEN NEXT -->
<div class="card" style="background:#fff3e0; padding:30px; border:3px solid #f57c00; margin-bottom:40px;">
    <h3 style="color:#e65100; margin-top:0;">Close Current Issue &amp; Open Next Issue</h3>
    <form method="POST" action="admin_action.php?action=close_issue" onsubmit="return confirm('Close current issue and open the next one?');">
        <div style="display:flex; gap:20px; margin:20px 0;">
            <div style="flex:1;">
                <label><strong>Next Volume Number</strong></label>
                <input type="number" name="next_volume" value="<?= $volume ?>" min="1" required 
                       style="width:100%; padding:14px; font-size:18px; border-radius:8px;">
            </div>
            <div style="flex:1;">
                <label><strong>Next Issue Number</strong></label>
                <input type="number" name="next_issue_number" value="<?= $issue_no + 1 ?>" min="1" required 
                       style="width:100%; padding:14px; font-size:18px; border-radius:8px;">
            </div>
        </div>
        <button type="submit" style="background:#d84315; color:white; padding:16px 40px; font-size:18px; border:none; border-radius:50px; cursor:pointer;">
            Close Current Issue &amp; Open Next
        </button>
    </form>
</div>
<!-- ALL SUBMITTED PAPERS -->
<div class="page-title" style="margin-top:20px;">All Submitted Papers</div>
<?php if (empty($papers)): ?>
    <div class="card">No papers submitted yet.</div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($papers as $p): ?>
            <article class="card">
                <h3 style="color:var(--blue); margin-bottom:6px;"><?= htmlspecialchars($p['title']) ?></h3>
                <p><strong>Author:</strong> <?= htmlspecialchars($p['author_name']) ?></p>

                <!-- STATUS BADGE -->
                <p><strong>Status:</strong>
                    <?php
                    $status_colors = [
                        'submitted'   => '#1976d2',
                        'under_review'=> '#f57c00',
                        'accepted'    => '#6a1b9a',
                        'published'   => '#2e7d32',
                        'rejected'    => '#c62828',
                    ];
                    $status_labels = [
                        'submitted'   => '📥 Submitted',
                        'under_review'=> '🔍 Under Review',
                        'accepted'    => '✅ Accepted',
                        'published'   => '📰 Published',
                        'rejected'    => '❌ Rejected',
                    ];
                    $sc = $status_colors[$p['status']] ?? '#555';
                    $sl = $status_labels[$p['status']] ?? ucfirst($p['status']);
                    ?>
                    <strong style="color:<?= $sc ?>; background:<?= $sc ?>22; padding:4px 12px; border-radius:20px; font-size:13px;">
                        <?= $sl ?>
                    </strong>
                </p>

                <?php if ($p['status'] == 'published'): ?>
                    <p><strong>Published as:</strong> <strong style="color:#1976d2;">Vol <?= $p['volume'] ?> No <?= $p['issue_number'] ?></strong></p>
                <?php else: ?>
                    <p><strong>Issue:</strong> Awaiting publication</p>
                <?php endif; ?>

                <p><strong>Keywords:</strong> <?= htmlspecialchars($p['keywords'] ?? 'None') ?></p>
                <p style="font-size:13px; color:#777;"><strong>Submitted:</strong> <?= date('d M Y', strtotime($p['submitted_at'])) ?></p>

                <div style="margin-top:14px; display:flex; gap:8px; flex-wrap:wrap;">

                    <!-- VIEW -->
                    <a class="btn" href="view_paper.php?id=<?= urlencode($p['id']) ?>"
                       style="background:#0d3b66; padding:10px 20px; font-size:14px;">
                        👁 View
                    </a>

                    <!-- UNDER REVIEW -->
                    <?php if ($p['status'] === 'submitted'): ?>
                        <a class="btn"
                           href="admin_action.php?action=under_review&id=<?= urlencode($p['id']) ?>"
                           style="background:#f57c00; padding:10px 20px; font-size:14px;"
                           onclick="return confirm('Mark this paper as Under Review?')">
                            🔍 Under Review
                        </a>
                    <?php endif; ?>

                    <!-- ACCEPTED -->
                    <?php if (in_array($p['status'], ['submitted', 'under_review'])): ?>
                        <a class="btn"
                           href="admin_action.php?action=accept&id=<?= urlencode($p['id']) ?>"
                           style="background:#6a1b9a; padding:10px 20px; font-size:14px;"
                           onclick="return confirm('Mark this paper as Accepted?')">
                            ✅ Accept
                        </a>
                    <?php endif; ?>

                    <!-- PUBLISH — only accepted papers can be published -->
                    <?php if ($p['status'] === 'accepted'): ?>
                        <a class="btn"
                           href="admin_action.php?action=approve&id=<?= urlencode($p['id']) ?>"
                           style="background:#2e7d32; padding:10px 20px; font-size:14px;"
                           onclick="return confirm('Publish this paper to the current issue?')">
                            📰 Publish
                        </a>
                    <?php endif; ?>

                    <!-- REJECT -->
                    <?php if (!in_array($p['status'], ['published', 'rejected'])): ?>
                        <a class="btn"
                           href="admin_action.php?action=reject&id=<?= urlencode($p['id']) ?>"
                           style="background:#c62828; padding:10px 20px; font-size:14px;"
                           onclick="return confirm('Reject this paper?')">
                            ❌ Reject
                        </a>
                    <?php endif; ?>

                    <!-- DELETE -->
                    <a class="btn" style="background:#b71c1c; padding:10px 20px; font-size:14px;"
                       href="admin_action.php?action=delete&id=<?= urlencode($p['id']) ?>"
                       onclick="return confirm('Are you sure you want to delete this paper permanently?');">
                        🗑 Delete
                    </a>

                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<!-- REGISTERED AUTHORS -->
<div class="page-title" style="margin-top:30px;">Registered Authors</div>
<?php if (empty($authors)): ?>
    <div class="card">No authors registered yet.</div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($authors as $a): ?>
            <article class="card">
                <h3 style="color:var(--purple); margin-bottom:6px;"><?= htmlspecialchars($a['name']) ?></h3>
                <p><strong>Email:</strong> <?= htmlspecialchars($a['email']) ?></p>
                <p><strong>Verified:</strong> <?= $a['is_verified'] ? 'Yes' : 'No' ?></p>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- DANGER ZONE: DELETE ENTIRE VOLUME -->
<div class="page-title" style="margin-top:40px;">Danger Zone: Delete Entire Volume</div>
<div class="card" style="background:#ffebee; border:3px solid #c62828; padding:25px; max-width:700px;">
    <form method="POST" action="admin_action.php" onsubmit="return confirm('WARNING: This will delete ALL papers in this volume permanently!');">
        <input type="hidden" name="action" value="delete_volume">
        <select name="volume" required style="width:100%; padding:12px; margin:10px 0; font-size:16px; border-radius:8px;">
            <option value="" disabled selected>&mdash; Choose Volume to Delete &mdash;</option>
            <?php
            $vols = $pdo->query("SELECT DISTINCT volume FROM papers WHERE status='published' ORDER BY volume DESC")->fetchAll();
            foreach ($vols as $v) {
                echo "<option value='{$v['volume']}'>Volume {$v['volume']}</option>";
            }
            ?>
        </select>
        <button type="submit" style="background:#b71c1c; color:white; padding:15px 30px; font-size:18px; border:none; border-radius:8px; margin-top:15px; font-weight:bold;">
            DELETE ENTIRE VOLUME
        </button>
    </form>
</div>

<!-- UPLOAD FULL ISSUE (PDF or Word) FOR ARCHIVES -->
<div class="page-title" style="margin-top:40px;">Upload Full Issue to Archives</div>
<div class="card" style="padding:30px; max-width:700px; background:#f8fff8; border:2px solid #4caf50;">
    <form method="POST" enctype="multipart/form-data">
        <p style="margin:0 0 8px; font-weight:bold; color:#2e7d32;">Issue code: e.g., vol10-no2, vol11-no1</p>
        <p style="margin:0 0 12px; font-size:13px; color:#555;">This becomes the filename in the archives folder.</p>
        <input type="text" name="issue_code" placeholder="vol10-no2" required 
               style="width:100%; padding:14px; margin-bottom:20px; border-radius:12px; border:2px solid #4caf50; font-size:16px;">
        <p style="margin:0 0 12px; font-weight:bold; color:#2e7d32;">Upload the complete issue file:</p>
        <input type="file" name="full_issue_file" accept=".pdf,.doc,.docx" required 
               style="width:100%; padding:10px; border-radius:12px; border:2px solid #4caf50; font-size:16px;">
        <p style="font-size:13px; color:#777; margin:8px 0 20px;">&#128196; Accepted: PDF, DOC, DOCX</p>
        <button type="submit" name="upload_full_issue" class="btn" 
                style="margin-top:10px; background:#1b5e20; padding:16px 40px; font-size:18px; border-radius:50px;">
            &#8593; Upload to Archives
        </button>
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_full_issue']) && isset($_FILES['full_issue_file'])) {
        $raw_code     = trim($_POST['issue_code'] ?? '');
        $code         = strtolower($raw_code);
        $code         = preg_replace('/[^a-z0-9]+/', '-', $code);
        $code         = preg_replace('/-+/', '-', $code);
        $code         = trim($code, '-');
        $allowed_exts = ['pdf', 'doc', 'docx'];
        $file         = $_FILES['full_issue_file'];
        $uploaded_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (empty($code)) {
            echo "<div style='background:#ffebee;color:#c62828;padding:15px;border-radius:8px;margin-top:20px;'>&#10060; Invalid issue code!</div>";
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            echo "<div style='background:#ffebee;color:#c62828;padding:15px;border-radius:8px;margin-top:20px;'>&#10060; Upload error code: " . $file['error'] . "</div>";
        } elseif (!in_array($uploaded_ext, $allowed_exts)) {
            echo "<div style='background:#ffebee;color:#c62828;padding:15px;border-radius:8px;margin-top:20px;'>&#10060; Only PDF, DOC, and DOCX allowed!</div>";
        } else {
            $archives_dir = __DIR__ . "/archives/";
            if (!is_dir($archives_dir)) mkdir($archives_dir, 0755, true);
            $target = $archives_dir . "{$code}.{$uploaded_ext}";
            if (move_uploaded_file($file['tmp_name'], $target)) {
                echo "<div style='background:#e8f5e8;color:#2e7d32;padding:20px;border-radius:12px;margin-top:20px;font-weight:bold;'>
                        &#9989; SUCCESS! Saved as: <strong>{$code}.{$uploaded_ext}</strong> &mdash; Now visible in Archives!
                      </div>";
            } else {
                echo "<div style='background:#ffebee;color:#c62828;padding:15px;border-radius:8px;margin-top:20px;'>
                        &#10060; Upload failed! Check archives/ folder has permission 755.
                      </div>";
            }
        }
    }
    ?>
</div>

<!-- DELETE FULL ISSUE FROM ARCHIVE -->
<div class="page-title" style="margin-top:30px;">Delete Full Issue from Archive</div>
<div class="card" style="padding:25px; background:#ffebee; border:2px solid #c62828; max-width:700px;">
    <form method="POST" action="admin_action.php">
        <input type="hidden" name="action" value="delete_full_issue">
        <select name="filename" required style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; font-size:16px;">
            <option value="">-- Select Issue File to Delete --</option>
            <?php
            $all_archive_files = array_merge(
                glob(__DIR__ . "/archives/vol*.pdf")  ?: [],
                glob(__DIR__ . "/archives/vol*.doc")  ?: [],
                glob(__DIR__ . "/archives/vol*.docx") ?: []
            );
            $file_map = [];
            foreach ($all_archive_files as $f) { $file_map[basename($f)] = true; }
            $closed_stmt = $pdo->query("SELECT volume, issue_number FROM issues WHERE is_closed = 1 ORDER BY volume DESC, issue_number DESC");
            $closed_issues_del = $closed_stmt->fetchAll();
            $listed = [];
            foreach ($closed_issues_del as $iss) {
                $found_file = null;
                foreach (['pdf','docx','doc'] as $ext) {
                    $candidate = "vol" . $iss['volume'] . "-no" . $iss['issue_number'] . ".{$ext}";
                    if (isset($file_map[$candidate])) { $found_file = $candidate; break; }
                }
                $label = "&#128274; Closed: Vol " . $iss['volume'] . " No " . $iss['issue_number'];
                $value = $found_file ?? "vol" . $iss['volume'] . "-no" . $iss['issue_number'] . ".pdf";
                if ($found_file) { $label .= " &mdash; " . strtoupper(pathinfo($found_file, PATHINFO_EXTENSION)) . " exists"; $listed[$found_file] = true; }
                echo "<option value='" . htmlspecialchars($value) . "'>" . htmlspecialchars_decode($label) . "</option>";
            }
            foreach ($all_archive_files as $f) {
                $name = basename($f);
                if (!isset($listed[$name])) {
                    echo "<option value='" . htmlspecialchars($name) . "'>&#128196; " . strtoupper(pathinfo($name, PATHINFO_EXTENSION)) . ": {$name}</option>";
                }
            }
            if (empty($closed_issues_del) && empty($all_archive_files)) {
                echo "<option value='' disabled>No files found</option>";
            }
            ?>
        </select>
        <button type="submit" class="btn" style="background:#c62828; color:white; padding:14px 40px; border-radius:50px;"
                onclick="return confirm('Delete this file from the archive?')">
            Delete Selected Issue File
        </button>
    </form>
</div>

<!-- MANUAL PAPER UPLOAD BY ADMIN -->
<div class="page-title" style="margin-top:50px;" id="upload-section">Manually Upload Paper to Any Volume</div>

<?php if ($upload_msg): ?>
    <div style="background:<?= $upload_ok ? '#e8f5e8' : '#ffebee' ?>;
                color:<?= $upload_ok ? '#2e7d32' : '#c62828' ?>;
                padding:20px; border-radius:12px; margin:20px 0; text-align:center;
                font-size:17px; border:2px solid <?= $upload_ok ? '#4caf50' : '#ef9a9a' ?>;">
        <?= $upload_msg ?>
    </div>
<?php endif; ?>

<div class="card" style="padding:35px; max-width:800px; margin:0 auto; background:#f8fff8; border:2px solid #4caf50;">

    <div style="background:#e3f2fd; padding:16px; border-radius:10px; margin-bottom:24px; border-left:5px solid #1976d2;">
        <p style="margin:0; font-size:15px; color:#0d47a1; line-height:1.7;">
            <strong>&#8505; How to use:</strong><br>
            — To upload papers to <strong>Vol 10 No 2</strong>: set Volume = <strong>10</strong>, Issue = <strong>2</strong><br>
            — To upload to current issue <strong>Vol <?= $volume ?> No <?= $issue_no ?></strong>: keep the default values<br>
            — Submit once per paper. Repeat for each paper in the same volume.<br>
            — The issue will <strong>automatically appear in Archives</strong> with a View Papers button.
        </p>
    </div>

    <form method="POST" enctype="multipart/form-data" action="admin_dashboard.php">
        <input type="hidden" name="manual_upload" value="1">

        <div style="margin-bottom:20px;">
            <label style="font-weight:bold; color:#2e7d32;">Author Name</label>
            <input type="text" name="author" required placeholder="e.g. John Doe"
                   style="width:100%; padding:12px; margin-top:8px; border-radius:8px; border:1px solid #ccc; font-size:15px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:bold; color:#2e7d32;">Title</label>
            <input type="text" name="title" required placeholder="Paper title"
                   style="width:100%; padding:12px; margin-top:8px; border-radius:8px; border:1px solid #ccc; font-size:15px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:bold; color:#2e7d32;">Abstract</label>
            <textarea name="abstract" required rows="5" placeholder="Brief summary of the paper"
                      style="width:100%; padding:12px; margin-top:8px; border-radius:8px; border:1px solid #ccc; font-size:15px;"></textarea>
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:bold; color:#2e7d32;">Keywords (comma separated)</label>
            <input type="text" name="keywords" placeholder="education, management, africa"
                   style="width:100%; padding:12px; margin-top:8px; border-radius:8px; border:1px solid #ccc; font-size:15px;">
        </div>

        <div style="display:flex; gap:20px; margin-bottom:25px;">
            <div style="flex:1;">
                <label style="font-weight:bold; color:#2e7d32;">Volume</label>
                <input type="number" name="volume" value="<?= $form_vol ?>" min="1" required
                       style="width:100%; padding:12px; margin-top:8px; border-radius:8px; border:1px solid #ccc; font-size:16px;">
            </div>
            <div style="flex:1;">
                <label style="font-weight:bold; color:#2e7d32;">Issue Number</label>
                <input type="number" name="issue_number" value="<?= $form_iss ?>" min="1" required
                       style="width:100%; padding:12px; margin-top:8px; border-radius:8px; border:1px solid #ccc; font-size:16px;">
            </div>
        </div>

        <div style="margin-bottom:25px;">
            <label style="font-weight:bold; color:#2e7d32;">Status</label>
            <select name="status" required style="width:100%; padding:12px; margin-top:8px; border-radius:8px; border:1px solid #ccc; font-size:15px;">
                <option value="published" selected>Published</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div style="margin-bottom:25px;">
            <label style="font-weight:bold; color:#2e7d32;">Upload Paper (PDF only)</label>
            <input type="file" name="paper_file" accept=".pdf" required
                   style="width:100%; padding:12px; margin-top:8px; border-radius:8px; border:1px solid #ccc; font-size:15px;">
            <p style="font-size:13px; color:#777; margin:6px 0 0;">Only PDF files accepted. Max size depends on your server settings.</p>
        </div>

        <button type="submit" class="btn" style="background:#1976d2; color:white; padding:16px 40px; font-size:18px; border:none; border-radius:50px; cursor:pointer; width:100%;">
            &#8593; Upload &amp; Publish Paper
        </button>
    </form>
</div>

<!-- QUICK LINKS -->
<div style="margin-top:40px; text-align:center;">
    <a class="btn" href="admin_message.php">View Contact Messages</a>
    <a class="btn" style="background:#8e24aa; margin:0 10px;" href="current-issue.php" target="_blank">Preview Current Issue</a>
    <a class="btn" style="background:#d81b60;" href="archives.php" target="_blank">View Archives</a>
</div>

<!-- MANUAL VOLUME CLOSURE TABLE -->
<div class="page-title" style="margin-top:40px;">Manage Volumes (Close Manually)</div>
<table border="1" cellpadding="12" cellspacing="0" style="width:100%; margin:20px 0; border-collapse:collapse;">
    <thead>
        <tr style="background:#6B21A8; color:white;">
            <th>Volume</th>
            <th>Paper Count</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $volumes = $pdo->query("SELECT DISTINCT volume FROM papers ORDER BY volume DESC")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($volumes as $vol):
            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM papers WHERE volume = ?");
            $count_stmt->execute([$vol]);
            $count = $count_stmt->fetchColumn();
        ?>
            <tr>
                <td>Volume <?= $vol ?></td>
                <td><?= $count ?></td>
                <td>
                    <a href="admin_action.php?action=close_volume&volume=<?= $vol ?>"
                       onclick="return confirm('Close Volume <?= $vol ?> permanently?');"
                       style="color:red; font-weight:bold; text-decoration:none;">
                        Close Volume
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/footer.php'; ?>