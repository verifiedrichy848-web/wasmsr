<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

// Get current active issue
$current_stmt = $pdo->query("SELECT volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1");
$current = $current_stmt->fetch();

if (!$current) {
    $pdo->prepare("INSERT INTO issues (volume, issue_number, is_current, created_at) VALUES (11, 1, 1, NOW()) ON DUPLICATE KEY UPDATE is_current = 1")->execute();
    $current = $pdo->query("SELECT volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1")->fetch();
}

$current_volume = $current['volume'];
$current_issue_number = $current['issue_number'];

// Fetch papers in the CURRENT active issue
$stmt = $pdo->prepare("
    SELECT p.*, u.name AS author_name 
    FROM papers p 
    JOIN users u ON p.author_id = u.id 
    WHERE p.status = 'published' 
      AND p.volume = ? 
      AND p.issue_number = ? 
    ORDER BY p.display_order ASC, p.published_at ASC
");
$stmt->execute([$current_volume, $current_issue_number]);
$papers = $stmt->fetchAll();
?>

<style>
/* Your original mobile CSS - kept 100% */
@media (max-width: 768px) {
    a[href="issue-toc.php"] {
        display: block !important; width: 92% !important; max-width: 360px !important;
        margin: 30px auto 0 !important; padding: 16px 20px !important; font-size: 17px !important;
        font-weight: bold !important; text-align: center !important; border-radius: 50px !important;
        background: #166534 !important; color: white !important;
        box-shadow: 0 8px 25px rgba(22,101,52,0.4) !important; text-decoration: none !important;
    }
    .hero h1, h1[style*="font-size:48px"] { font-size: 28px !important; }
    .hero h2, h2[style*="font-size:36px"] { font-size: 22px !important; }
    .hero p { font-size: 16px !important; }
}

@media (max-width: 480px) {
    a[href="issue-toc.php"] { font-size: 16px !important; padding: 14px 18px !important; }
    .hero h1 { font-size: 25px !important; }
    .hero h2 { font-size: 20px !important; }
}
</style>

<div class="marquee">
    <marquee behavior="scroll" direction="left">WEST AFRICAN SOCIAL AND MANAGEMENT SCIENCES REVIEW</marquee>
</div>

<!-- HERO -->
<section class="hero card" style="background:linear-gradient(135deg, #0d3b66, #1a4971); color:white; text-align:center; padding:100px 20px; border-radius:20px; margin:40px 0 60px;">
    <div style="max-width:1200px; margin:0 auto;">
        <h1 style="font-size:48px; margin:15px 0; font-weight:900; text-shadow:0 3px 12px rgba(0,0,0,0.5);">
            West African Social and Management Sciences Review
        </h1>
        <h2 style="font-size:36px; margin:20px 0; font-weight:700;">
            Volume <?= $current_volume ?> 
            <span style="font-size:28px; opacity:0.9;">(No <?= $current_issue_number ?>)</span>
        </h2>
        <p style="font-size:20px; opacity:0.95; margin:20px 0;">
            Peer-reviewed • Open Access • Bi-Annual Publication
        </p>
    </div>
</section>

<!-- TOC Button -->
<div style="text-align:center; margin:50px 0;">
    <a href="issue-toc.php" class="btn" style="background:#166534; padding:18px 50px; font-size:20px; font-weight:bold; border-radius:50px; box-shadow:0 10px 30px rgba(22,101,52,0.3);">
        View Full Table of Contents (Print-Ready)
    </a>
</div>

<!-- Title -->
<div style="text-align:center; margin:70px 0;">
    <h2 style="font-size:48px; color:#0d3b66; margin:0; font-weight:900; letter-spacing:-1.5px;">
        Table of Contents
    </h2>
    <p style="font-size:21px; color:#444; margin:15px 0;">
        <?= count($papers) ?> Published Article(s) in Volume <?= $current_volume ?> No <?= $current_issue_number ?>
        &nbsp; | &nbsp; 
        <a href="archives.php" style="color:#6B21A8; font-weight:600; text-decoration:none;">View Archives</a>
    </p>
    <div style="width:180px; height:6px; background:linear-gradient(90deg, #0d3b66, #5e8cff); margin:25px auto; border-radius:3px;"></div>
</div>

<!-- Full Issue Notice -->
<div class="card" style="text-align:center; padding:45px; background:#f8fbff; border:2px dashed #bbdefb; max-width:900px; margin:0 auto 70px; border-radius:20px; box-shadow:0 8px 25px rgba(0,0,0,0.05);">
    <p style="margin:0; font-size:18px; color:#444; line-height:1.8;">
        Full-issue PDF compilation is in preparation.<br>
        All articles are available for individual download below.
    </p>
</div>

<!-- Papers Grid -->
<?php if (empty($papers)): ?>
    <div style="text-align:center; padding:100px 40px; background:white; border-radius:20px; box-shadow:0 20px 50px rgba(0,0,0,0.08); margin:0 auto; max-width:800px;">
        <p style="font-size:24px; color:#666;">
            No papers published yet in Volume <?= $current_volume ?> No <?= $current_issue_number ?>.<br><br>
            Approve papers from the Admin Dashboard.
        </p>
    </div>
<?php else: ?>
    <div style="max-width:1400px; margin:0 auto; padding:0 20px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:40px;">
            <?php foreach ($papers as $p): ?>
                <article style="background:white; border-radius:22px; overflow:hidden; box-shadow:0 15px 45px rgba(0,0,0,0.1); transition:all 0.5s ease; position:relative; border:1px solid #eef2f7;"
                         onmouseover="this.style.transform='translateY(-12px)'; this.style.boxShadow='0 25px 60px rgba(0,0,0,0.18)';"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 15px 45px rgba(0,0,0,0.1)';">

                    <div style="height:220px; overflow:hidden; background:#f0f4f8; position:relative;">
                        <?php if (!empty($p['cover_image']) && file_exists('uploads/'.$p['cover_image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($p['cover_image']) ?>" alt="Cover" 
                                 style="width:100%; height:100%; object-fit:cover; transition:transform 0.8s;"
                                 onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <?php else: ?>
                            <div style="height:100%; background:linear-gradient(135deg, #667eea, #764ba2); display:flex; align-items:center; justify-content:center;">
                                <span style="color:white; font-size:80px; font-weight:bold; opacity:0.3;">PDF</span>
                            </div>
                        <?php endif; ?>

                        <div style="position:absolute; top:16px; right:16px; background:rgba(13,59,102,0.95); color:white; padding:10px 18px; border-radius:30px; font-weight:700; font-size:14px;">
                            Vol <?= $p['volume'] ?> • No <?= $p['issue_number'] ?>
                        </div>
                    </div>

                    <div style="padding:30px;">
                        <h3 style="font-size:22px; margin:0 0 14px; color:#0d3b66; font-weight:800; line-height:1.35;">
                            <?= htmlspecialchars($p['title']) ?>
                        </h3>
                        <p style="margin:0 0 12px; color:#444; font-size:16px; font-weight:600;">
                            By <?= htmlspecialchars($p['author_name']) ?>
                        </p>
                        <p style="margin:0 0 20px; color:#666; font-size:15px; line-height:1.7;">
                            <?= htmlspecialchars(substr(strip_tags($p['abstract']), 0, 180)) ?>
                            <?= strlen(strip_tags($p['abstract'])) > 180 ? '…' : '' ?>
                        </p>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <a href="view_paper.php?id=<?= $p['id'] ?>" 
                               style="background:linear-gradient(135deg, #0d3b66, #1a4971); color:white; padding:14px 32px; border-radius:50px; text-decoration:none; font-weight:700; box-shadow:0 8px 25px rgba(13,59,102,0.3);">
                                View & Download Paper
                            </a>
                            <small style="color:#999; font-size:13px;">
                                <?= date('d M Y', strtotime($p['published_at'])) ?>
                            </small>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>