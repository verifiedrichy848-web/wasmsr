<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

/*
|--------------------------------------------------------------------------
| 1. DETERMINE WHICH ISSUE TO DISPLAY
|--------------------------------------------------------------------------
*/

$issue_code = null;
$volume = null;
$issue_number = null;

// 1. From URL parameter (?issue= or ?volume= & ?issue_number=)
if (!empty($_GET['issue'])) {
    $issue_code = preg_replace('/[^a-z0-9\-]/i', '', $_GET['issue']);
} elseif (!empty($_GET['volume']) && !empty($_GET['issue_number'])) {
    $volume = (int)$_GET['volume'];
    $issue_number = (int)$_GET['issue_number'];
}

// 2. Use current active issue if nothing in URL
if (!$issue_code && !$volume) {
    $stmt = $pdo->query("SELECT volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1");
    $current = $stmt->fetch();
    if ($current) {
        $volume = $current['volume'];
        $issue_number = $current['issue_number'];
    }
}

// 3. Final fallback - latest closed or published issue
if (!$volume) {
    $stmt = $pdo->query("
        SELECT volume, issue_number 
        FROM issues 
        ORDER BY created_at DESC, id DESC 
        LIMIT 1
    ");
    $fallback = $stmt->fetch();
    if ($fallback) {
        $volume = $fallback['volume'];
        $issue_number = $fallback['issue_number'];
    }
}

// If still no issue found
if (!$volume) {
    echo "<div style='padding:80px;text-align:center;color:#555;font-size:20px;'>
            No issues have been published yet.
          </div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| 2. FETCH THE SELECTED ISSUE DATA
|--------------------------------------------------------------------------
*/
$issueStmt = $pdo->prepare("SELECT * FROM issues WHERE volume = ? AND issue_number = ?");
$issueStmt->execute([$volume, $issue_number]);
$issue = $issueStmt->fetch(PDO::FETCH_ASSOC);

if (!$issue) {
    echo "<div style='padding:80px;text-align:center;color:red;font-size:20px;'>
            The requested issue could not be found.
          </div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$is_current_issue = (bool)$issue['is_current'];

/*
|--------------------------------------------------------------------------
| 3. FETCH PUBLISHED PAPERS FOR THIS ISSUE
|--------------------------------------------------------------------------
*/
$papersStmt = $pdo->prepare("
    SELECT p.*, u.name AS author_name
    FROM papers p
    JOIN users u ON p.author_id = u.id
    WHERE p.status = 'published' 
      AND p.volume = ? 
      AND p.issue_number = ?
    ORDER BY p.display_order ASC, p.published_at ASC
");
$papersStmt->execute([$volume, $issue_number]);
$papers = $papersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* === TOC STYLES === */
.toc-wrapper {
    max-width: 900px;
    margin: 50px auto;
    background: #fff;
    padding: 60px 80px;
    border: 3px double #000;
    font-family: "Times New Roman", Times, serif;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.toc-wrapper h1 {
    text-align: center;
    font-size: 32px;
    margin-bottom: 10px;
    font-weight: normal;
}
.toc-meta {
    text-align: center;
    font-size: 18px;
    margin: 25px 0;
}
.current-badge {
    display: inline-block;
    background: #c00;
    color: #fff;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 12px;
    margin-left: 10px;
    text-transform: uppercase;
    font-weight: bold;
}
.toc-wrapper h2 {
    text-align: center;
    margin: 50px 0 30px;
    font-size: 28px;
}
.toc-article {
    margin: 28px 0;
    position: relative;
    padding-right: 90px;
    line-height: 1.5;
}
.toc-title {
    font-weight: bold;
    font-size: 17px;
}
.toc-author {
    font-size: 16px;
    color: #333;
}
.toc-page {
    position: absolute;
    right: 0;
    top: 0;
    font-weight: bold;
    font-size: 16px;
}
hr {
    border: none;
    border-top: 2px solid #000;
    margin: 50px 0;
}
@media print {
    body { background:#fff; }
    .toc-wrapper { box-shadow:none; border:none; }
}
</style>

<div class="toc-wrapper">

    <h1>WEST AFRICAN SOCIAL AND MANAGEMENT SCIENCES REVIEW (WASMSR)</h1>

    <div class="toc-meta">
        <strong><?= htmlspecialchars($issue['display_name'] ?? "Volume {$volume} Number {$issue_number}") ?></strong>
        <?php if ($is_current_issue): ?>
            <span class="current-badge">Current Issue</span>
        <?php endif; ?>
        <br>
        ISSN: 2141-5048
    </div>

    <hr>

    <h2>TABLE OF CONTENTS</h2>

    <?php if (empty($papers)): ?>
        <p style="text-align:center;font-style:italic;margin-top:50px;font-size:18px;">
            No published articles for this issue yet.<br>
            Papers will appear here once approved by the Admin.
        </p>
    <?php else: ?>
        <?php 
        $starting_page = 1;
        $pages_per_article = 10;
        foreach ($papers as $index => $p): 
            $page_number = $starting_page + ($index * $pages_per_article);
        ?>
            <div class="toc-article">
                <div class="toc-title"><?= htmlspecialchars($p['title']) ?></div>
                <div class="toc-author"><?= htmlspecialchars($p['author_name']) ?></div>
                <div class="toc-page"><?= $page_number ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>