<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

// Get the volume from URL (?volume=11)
$volume = isset($_GET['volume']) ? (int)$_GET['volume'] : 0;

if ($volume <= 0) {
    echo "<div style='text-align:center; padding:50px; color:#c62828; font-size:24px;'>Invalid volume selected.</div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

// Fetch all published papers in this volume
$papers_stmt = $pdo->prepare("
    SELECT p.id, p.title, p.abstract, p.keywords, p.volume, p.issue_number, p.published_at,
         COALESCE(p.author_name, u.name, 'Unknown Author') AS author_name
    FROM papers p
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.volume = ? AND p.status = 'published'
    ORDER BY p.display_order ASC, p.published_at ASC
");
$papers_stmt->execute([$volume]);
$papers = $papers_stmt->fetchAll();

// Count papers
$paper_count = count($papers);
?>

<div style="max-width:1200px; margin:40px auto; padding:0 20px;">
    <div style="text-align:center; margin-bottom:50px;">
        <h1 style="font-size:48px; color:#0d3b66; margin:0;">
            Volume <?= $volume ?>
        </h1>
        <p style="font-size:22px; color:#555; margin:15px 0;">
            <?= $paper_count ?> Published Article<?= $paper_count != 1 ? 's' : '' ?>
        </p>
        <div style="width:180px; height:6px; background:linear-gradient(90deg, #0d3b66, #5e8cff); margin:20px auto; border-radius:3px;"></div>
    </div>

    <?php if (empty($papers)): ?>
        <div style="text-align:center; padding:60px; background:#f8f9fa; border-radius:16px;">
            <h3 style="color:#555; font-size:26px;">No published papers in Volume <?= $volume ?> yet.</h3>
            <p style="color:#777; margin-top:15px;">Check back later or contact the editorial team.</p>
        </div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(380px, 1fr)); gap:35px;">
            <?php foreach ($papers as $p): ?>
                <article class="paper-card" style="background:white; border-radius:16px; overflow:hidden; box-shadow:0 10px 35px rgba(0,0,0,0.08); transition:all 0.3s;">
                    <div style="padding:30px;">
                        <h3 style="font-size:24px; color:#0d3b66; margin:0 0 15px; line-height:1.3;">
                            <?= htmlspecialchars($p['title']) ?>
                        </h3>
                        <p style="color:#555; margin:0 0 20px; font-size:16px; line-height:1.6;">
                            <?= htmlspecialchars(substr($p['abstract'], 0, 180)) ?>...
                        </p>
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:15px; color:#555;">
                            <div>
                                <strong>Author:</strong> <?= htmlspecialchars($p['author_name']) ?>
                            </div>
                            <div style="color:#777;">
                                <?= date('M d, Y', strtotime($p['published_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding:20px 30px; background:#f8f9fa; text-align:center; border-top:1px solid #eee;">
                        <a href="view_paper.php?id=<?= $p['id'] ?>" class="btn" style="background:#1976d2; color:white; padding:12px 30px; border-radius:50px; text-decoration:none; font-weight:bold;">
                            Read Full Paper →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>