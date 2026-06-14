<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<div style='text-align:center;padding:100px;font-size:22px;color:#c62828;'>Invalid paper ID.</div>");
}

$paper_id = (int)$_GET['id'];

// LEFT JOIN so paper shows even if author_id doesn't match a user row
// COALESCE: tries p.author_name first (manually uploaded papers),
//           then u.name (registered author papers), then falls back
$stmt = $pdo->prepare("
    SELECT p.*,
           COALESCE(p.author_name, u.name, 'Unknown Author') AS author_name
    FROM papers p
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.id = ? AND p.status = 'published'
");
$stmt->execute([$paper_id]);
$paper = $stmt->fetch();

if (!$paper) {
    die("<div style='text-align:center;padding:100px;font-size:22px;color:#c62828;'>Paper not found or not published.</div>");
}

$file_path = __DIR__ . "/" . $paper['file_path'];
$file_url  = $paper['file_path'];
$filename  = basename($paper['file_path']);
$ext       = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Page title and Google Scholar citation meta tags
$page_title = $paper['title'];

$extra_meta = '
<meta name="citation_title" content="' . htmlspecialchars($paper['title']) . '">
<meta name="citation_author" content="' . htmlspecialchars($paper['author_name']) . '">
<meta name="citation_publication_date" content="' . date('Y/m/d', strtotime($paper['published_at'])) . '">
<meta name="citation_journal_title" content="West African Social and Management Sciences Review">
<meta name="citation_issn" content="2141-5048">
<meta name="citation_volume" content="' . $paper['volume'] . '">
<meta name="citation_issue" content="' . $paper['issue_number'] . '">
<meta name="citation_pdf_url" content="https://wasmsr.org/' . htmlspecialchars($paper['file_path']) . '">
';

include 'header.php';
?>

<!-- HERO SECTION -->
<section class="hero card" style="background:linear-gradient(135deg, #0d3b66, #1a4971); color:white; text-align:center; padding:90px 20px; border-radius:20px; margin:40px 20px;">
    <div style="max-width:1000px; margin:0 auto;">
        <h1 style="font-size:42px; margin:0 0 20px; font-weight:900; line-height:1.2;">
            <?= htmlspecialchars($paper['title']) ?>
        </h1>
        <p style="font-size:22px; margin:15px 0; opacity:0.95;">
            <strong>By</strong> <?= htmlspecialchars($paper['author_name']) ?>
        </p>
        <p style="font-size:18px; margin:15px 0; opacity:0.9;">
            Volume <?= $paper['volume'] ?> &bull; Issue No <?= $paper['issue_number'] ?> &bull;
            <?= date('F Y', strtotime($paper['published_at'])) ?>
        </p>
    </div>
</section>

<!-- MAIN CONTENT -->
<div style="max-width:1000px; margin:0 auto; padding:0 20px;">
    <div class="card" style="padding:40px; background:white; border-radius:20px; box-shadow:0 15px 45px rgba(0,0,0,0.1);">

        <!-- Abstract -->
        <div style="margin-bottom:40px;">
            <h2 style="font-size:28px; color:#0d3b66; margin:0 0 20px; font-weight:800; border-bottom:3px solid #0d3b66; padding-bottom:10px;">
                Abstract
            </h2>
            <p style="font-size:17px; line-height:1.8; color:#444;">
                <?= nl2br(htmlspecialchars($paper['abstract'])) ?>
            </p>
        </div>

        <!-- Keywords -->
        <?php if (!empty($paper['keywords'])): ?>
            <div style="margin-bottom:40px;">
                <h3 style="font-size:22px; color:#0d3b66; margin:0 0 15px; font-weight:700;">
                    Keywords
                </h3>
                <p style="font-size:16px; color:#555;">
                    <?= htmlspecialchars($paper['keywords']) ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- File Actions -->
        <div style="text-align:center; padding:40px 20px; background:#f8fbff; border-radius:16px; border:2px dashed #bbdefb;">
            <?php if (file_exists($file_path)): ?>

                <?php if ($ext === 'pdf'): ?>
                    <div style="margin:20px 0;">
                        <a href="<?= $file_url ?>" target="_blank" class="btn"
                           style="background:#166534; padding:18px 50px; font-size:20px; margin:10px; border-radius:50px; box-shadow:0 10px 30px rgba(22,101,52,0.3);">
                            &#128196; View PDF in Browser
                        </a>
                    </div>
                    <div>
                        <a href="<?= $file_url ?>" download="<?= htmlspecialchars($filename) ?>" class="btn"
                           style="background:#0d3b66; padding:18px 50px; font-size:20px; margin:10px; border-radius:50px; box-shadow:0 10px 30px rgba(13,59,102,0.3);">
                            &#8595; Download PDF
                        </a>
                    </div>

                <?php elseif ($ext === 'doc' || $ext === 'docx'): ?>
                    <p style="font-size:18px; color:#555; margin:0 0 20px;">
                        &#128196; This paper is available as a Word document.
                    </p>
                    <div>
                        <a href="<?= $file_url ?>" download="<?= htmlspecialchars($filename) ?>" class="btn"
                           style="background:#1565c0; padding:18px 50px; font-size:20px; margin:10px; border-radius:50px; box-shadow:0 10px 30px rgba(21,101,192,0.3);">
                            &#8595; Download Word Document
                        </a>
                    </div>

                <?php else: ?>
                    <div>
                        <a href="<?= $file_url ?>" download="<?= htmlspecialchars($filename) ?>" class="btn"
                           style="background:#0d3b66; padding:18px 50px; font-size:20px; margin:10px; border-radius:50px;">
                            &#8595; Download Paper
                        </a>
                    </div>
                <?php endif; ?>

                <p style="margin-top:25px; color:#666; font-size:15px;">
                    File: <strong><?= htmlspecialchars($filename) ?></strong>
                </p>

            <?php else: ?>
                <p style="color:#c62828; font-size:20px; font-weight:bold;">
                    File not found on server.<br>
                    <small style="font-size:14px; color:#888;">Expected: <?= htmlspecialchars($file_path) ?></small>
                </p>
            <?php endif; ?>
        </div>

        <!-- Back Button -->
        <div style="text-align:center; margin-top:50px;">
            <a href="javascript:history.back()" class="btn"
               style="background:transparent; color:#0d3b66; border:3px solid #0d3b66; padding:14px 40px; font-size:18px;">
                &larr; Back to Issue
            </a>
        </div>
    </div>
</div>

<!-- MOBILE FIX -->
<style>
@media (max-width: 768px) {
    .hero.card { padding: 70px 20px; margin: 20px; }
    .hero h1 { font-size: 32px !important; }
    .hero p { font-size: 18px !important; }
    .card { padding: 30px 20px !important; margin: 20px !important; }
    .btn {
        display: block !important;
        width: 90% !important;
        max-width: 340px !important;
        margin: 15px auto !important;
        padding: 16px 20px !important;
        font-size: 18px !important;
    }
}
</style>

<?php include 'footer.php'; ?>