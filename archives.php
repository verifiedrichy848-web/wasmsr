<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

// Check if user is admin
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Handle delete request for archive files (PDF, DOC, DOCX)
if ($is_admin && isset($_GET['delete_pdf'])) {
    $file_to_delete = __DIR__ . "/archives/" . basename($_GET['delete_pdf']);
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
        $_SESSION['success'] = "File deleted: " . basename($_GET['delete_pdf']);
    }
    header("Location: archives.php");
    exit;
}

// ============================================================
// SINGLE SOURCE OF TRUTH: Only closed issues from issues table
// Left join papers to get paper count per issue
// ============================================================
$closed_issues_stmt = $pdo->query("
    SELECT 
        i.id,
        i.volume,
        i.issue_number,
        i.closed_at,
        COUNT(p.id) AS paper_count
    FROM issues i
    LEFT JOIN papers p 
        ON p.volume = i.volume 
        AND p.issue_number = i.issue_number 
        AND p.status = 'published'
    WHERE i.is_closed = 1
    GROUP BY i.id, i.volume, i.issue_number, i.closed_at
    ORDER BY i.volume DESC, i.issue_number DESC
");
$closed_issues = $closed_issues_stmt->fetchAll();

// Build lookup of closed issues for all file types
$closed_lookup = [];
foreach ($closed_issues as $ci) {
    foreach (['pdf', 'docx', 'doc'] as $ext) {
        $key = "vol" . $ci['volume'] . "-no" . $ci['issue_number'] . ".{$ext}";
        $closed_lookup[$key] = true;
    }
}

// Scan archives folder for ALL file types
$all_archive_files = array_merge(
    glob(__DIR__ . "/archives/vol*.pdf")  ?: [],
    glob(__DIR__ . "/archives/vol*.doc")  ?: [],
    glob(__DIR__ . "/archives/vol*.docx") ?: []
);

// Standalone files not matching any closed issue
$standalone_files = [];
foreach ($all_archive_files as $f) {
    $filename = basename($f);
    if (!isset($closed_lookup[$filename])) {
        $standalone_files[] = $f;
    }
}

// Sort standalone files descending by volume then issue number
usort($standalone_files, function($a, $b) {
    preg_match('/vol(\d+)/i', basename($a), $ma);
    preg_match('/vol(\d+)/i', basename($b), $mb);
    $volA = isset($ma[1]) ? (int)$ma[1] : 0;
    $volB = isset($mb[1]) ? (int)$mb[1] : 0;
    if ($volA !== $volB) return $volB - $volA;
    preg_match('/no(\d+)/i', basename($a), $na);
    preg_match('/no(\d+)/i', basename($b), $nb);
    $noA = isset($na[1]) ? (int)$na[1] : 0;
    $noB = isset($nb[1]) ? (int)$nb[1] : 0;
    return $noB - $noA;
});

// Helper: find archive file for a closed issue (checks pdf, docx, doc)
function find_archive_file($volume, $issue_number) {
    foreach (['pdf', 'docx', 'doc'] as $ext) {
        $rel  = "archives/vol{$volume}-no{$issue_number}.{$ext}";
        $abs  = __DIR__ . "/" . $rel;
        if (file_exists($abs)) {
            return ['path' => $rel, 'abs' => $abs, 'ext' => $ext, 'filename' => basename($abs)];
        }
    }
    return null;
}

// Helper: download button label by file extension
function download_label($ext) {
    if ($ext === 'pdf')  return '&#128196; Download Full Issue PDF';
    if ($ext === 'docx') return '&#128196; Download Full Issue (Word)';
    if ($ext === 'doc')  return '&#128196; Download Full Issue (Word)';
    return '&#8595; Download Full Issue';
}

// Helper: display name from filename (remove extension, clean up dashes)
function display_name_from_file($filename) {
    $name = preg_replace('/\.(pdf|docx|doc)$/i', '', $filename);
    $name = str_replace('-', ' ', $name);
    return strtoupper(trim($name));
}
?>

<!-- HERO -->
<section class="hero card" style="background:linear-gradient(135deg, #0d3b66, #1a4971); color:white; text-align:center; padding:100px 20px; border-radius:20px; margin:40px 0;">
    <h2 style="font-size:48px; margin:0 0 20px; font-weight:900;">Journal Archives</h2>
    <p style="font-size:22px; max-width:900px; margin:0 auto; opacity:0.95;">
        Browse all closed volumes and issues of <strong>WASMASR</strong> &mdash; fully open access
    </p>
</section>

<?php if (isset($_SESSION['success'])): ?>
    <div style="background:#e8f5e8;color:#2e7d32;padding:15px;border-radius:8px;margin:20px auto;max-width:800px;text-align:center;font-weight:bold;">
        <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- TITLE -->
<div style="text-align:center; margin:70px 0;">
    <h2 style="font-size:48px; color:#0d3b66; margin:0; font-weight:900;">
        All Closed Volumes &amp; Issues
    </h2>
    <div style="width:180px; height:6px; background:linear-gradient(90deg, #0d3b66, #5e8cff); margin:25px auto; border-radius:3px;"></div>
</div>

<!-- VOLUMES GRID -->
<div style="max-width:1400px; margin:0 auto; padding:0 20px;">
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:40px;">

        <!-- LOOP 1: Closed issues from DB (single source of truth) -->
        <?php foreach ($closed_issues as $issue):
            $archive = find_archive_file($issue['volume'], $issue['issue_number']);
        ?>
            <div class="paper-card" style="background:white; border-radius:22px; overflow:hidden; box-shadow:0 15px 45px rgba(0,0,0,0.1); position:relative;">
                <div style="height:260px; background:linear-gradient(135deg, #1e3a8a, #6b21a8); display:flex; align-items:center; justify-content:center;">
                    <div style="text-align:center; color:white;">
                        <h3 style="font-size:52px; margin:0; font-weight:900;">
                            Vol <?= $issue['volume'] ?>
                        </h3>
                        <p style="font-size:20px; margin:10px 0 0;">
                            No <?= $issue['issue_number'] ?>
                            &bull; <?= $issue['paper_count'] ?> Article<?= $issue['paper_count'] != 1 ? 's' : '' ?>
                        </p>
                        <?php if ($archive): ?>
                            <p style="font-size:14px; margin:8px 0 0; opacity:0.7;">
                                &#128196; <?= strtoupper($archive['ext']) ?> available
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="padding:30px; text-align:center;">
                    <h3 style="font-size:26px; color:#0d3b66; margin:0 0 20px;">
                        Volume <?= $issue['volume'] ?> &bull; Issue <?= $issue['issue_number'] ?>
                    </h3>

                    <a href="volume_papers.php?volume=<?= $issue['volume'] ?>" class="btn"
                       style="background:#0d3b66; margin:8px; padding:12px 30px; border-radius:50px; color:white; text-decoration:none; display:inline-block;">
                        View Papers in this Issue &rarr;
                    </a>

                    <?php if ($archive): ?>
                        <a href="<?= htmlspecialchars($archive['path']) ?>" class="btn"
                           style="background:#166534; margin:8px; padding:12px 30px; border-radius:50px; color:white; text-decoration:none; display:inline-block;">
                            <?= download_label($archive['ext']) ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($is_admin && $archive): ?>
                        <a href="?delete_pdf=<?= urlencode($archive['filename']) ?>"
                           class="btn"
                           style="background:#c62828; margin:8px; padding:7px 30px; border-radius:50px; color:white; text-decoration:none; display:inline-block;"
                           onclick="return confirm('Delete <?= strtoupper($archive['ext']) ?> file for Vol <?= $issue['volume'] ?> No <?= $issue['issue_number'] ?>?')">
                            Delete File
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- LOOP 2: Standalone admin-uploaded files with no matching closed issue record -->
        <?php foreach ($standalone_files as $f):
            $filename     = basename($f);
            $ext          = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $display_name = display_name_from_file($filename);
            $rel_path     = "archives/" . $filename;
            $banner_color = 'linear-gradient(135deg, #1e3a8a, #6b21a8)';
            $type_icon    = ($ext === 'pdf') ? '&#128196;' : '&#128196;';
            $type_label   = ($ext === 'pdf') ? 'PDF' : strtoupper($ext);
        ?>
            <div class="paper-card" style="background:white; border-radius:22px; overflow:hidden; box-shadow:0 15px 45px rgba(0,0,0,0.1); position:relative;">
                <div style="height:200px; background:<?= $banner_color ?>; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:10px;">
                    <div style="font-size:40px;"><?= $type_icon ?></div>
                    <h3 style="color:white; font-size:22px; margin:0; font-weight:900; text-align:center; padding:0 15px;">
                        <?= $display_name ?>
                    </h3>
                    <span style="color:rgba(255,255,255,0.7); font-size:13px;"><?= $type_label ?> Document</span>
                </div>
                <div style="padding:30px; text-align:center;">
                    <p style="margin:0 0 20px; color:#555;">Admin-uploaded full issue</p>
                    <a href="<?= htmlspecialchars($rel_path) ?>" class="btn"
                       style="background:#166534; margin:8px; padding:12px 30px; border-radius:50px; color:white; text-decoration:none; display:inline-block;">
                        <?= download_label($ext) ?>
                    </a>
                    <?php if ($is_admin): ?>
                        <a href="?delete_pdf=<?= urlencode($filename) ?>"
                           class="btn"
                           style="background:#c62828; margin:8px; padding:7px 30px; border-radius:50px; color:white; text-decoration:none; display:inline-block;"
                           onclick="return confirm('Delete <?= $filename ?> permanently?')">
                            Delete File
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Empty state -->
        <?php if (empty($closed_issues) && empty($standalone_files)): ?>
            <div style="grid-column:1/-1; text-align:center; padding:60px 20px; color:#555;">
                <p style="font-size:22px;">No archived issues yet. Issues will appear here once the admin closes them.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Specific Volume Papers View -->
<?php if (isset($_GET['volume'])):
    $selected_volume = (int)$_GET['volume'];
    $papers_stmt = $pdo->prepare("
        SELECT p.*, u.name AS author_name 
        FROM papers p 
        JOIN users u ON p.author_id = u.id 
        WHERE p.volume = ? AND p.status = 'published' 
        ORDER BY p.display_order ASC
    ");
    $papers_stmt->execute([$selected_volume]);
    $selected_papers = $papers_stmt->fetchAll();
?>
    <div style="max-width:1400px; margin:60px auto 0; padding:0 20px;">
        <h2 style="font-size:40px; color:#0d3b66; text-align:center; margin-bottom:40px;">
            Volume <?= $selected_volume ?> Papers
        </h2>

        <?php if (empty($selected_papers)): ?>
            <p style="text-align:center; font-size:20px; color:#555;">No published papers in this volume yet.</p>
        <?php else: ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:30px;">
                <?php foreach ($selected_papers as $p): ?>
                    <article class="paper-card" style="background:white; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.08);">
                        <div style="padding:25px;">
                            <h3 style="font-size:24px; color:#0d3b66; margin:0 0 12px;">
                                <?= htmlspecialchars($p['title']) ?>
                            </h3>
                            <p style="color:#555; margin:0 0 15px;">
                                <?= htmlspecialchars(substr($p['abstract'], 0, 150)) ?>...
                            </p>
                            <p style="color:#777; font-size:14px;">
                                By <?= htmlspecialchars($p['author_name']) ?>
                            </p>
                            <a href="view_paper.php?id=<?= $p['id'] ?>" class="btn"
                               style="background:#1976d2; margin-top:15px; padding:10px 24px; border-radius:50px; color:white; text-decoration:none; display:inline-block;">
                                Read Full Paper
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>