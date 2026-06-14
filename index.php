<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

// Handle search
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT p.*, COALESCE(p.author_name, u.name, 'Unknown Author') AS author_name
        FROM papers p LEFT JOIN users u ON p.author_id = u.id
        WHERE p.status='published' 
          AND (p.title LIKE :q OR p.abstract LIKE :q OR u.name LIKE :q OR p.keywords LIKE :q OR p.author_name LIKE :q)
        ORDER BY p.published_at DESC LIMIT 6
    ");
    $q = "%{$search}%";
    $stmt->bindParam(':q', $q);
    $stmt->execute();
    $latest_papers = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT p.*, COALESCE(p.author_name, u.name, 'Unknown Author') AS author_name
        FROM papers p LEFT JOIN users u ON p.author_id = u.id
        WHERE p.status='published'
        ORDER BY p.published_at DESC LIMIT 6
    ");
    $latest_papers = $stmt->fetchAll();
}

// Get current volume for display
$current_volume = $pdo->query("SELECT volume FROM issues WHERE is_current = 1 LIMIT 1")->fetchColumn() ?: 11;
?>

<!-- MARQUEE -->
<div class="marquee">
    <marquee behavior="scroll" direction="left" style="background:#0d3b66; color:white; padding:12px 0; font-weight:bold; font-size:18px;">
        WEST AFRICAN SOCIAL AND MANAGEMENT SCIENCES REVIEW &bull; ISSN: 2141-5048 &bull; PEER-REVIEWED &bull; OPEN ACCESS &bull; Bi-Annual
        <a href="call-for-paper.php" style="color:#D4AF37; text-decoration:underline; font-weight:bold; padding:0 8px; background:rgba(212,175,55,0.15); border-radius:4px;">
            CALL FOR PAPERS &ndash; VOLUME 11, NUMBER 1
        </a>
        &bull; We are currently accepting original manuscripts &bull; Contribute to advancing social &amp; management sciences scholarship in West Africa &bull; Submit your paper today via wajsmsr@biu.edu.ng &bull; PEER-REVIEWED &bull; OPEN ACCESS &bull; QUARTERLY
    </marquee>
</div>

<!-- HERO -->
<section class="hero card" style="background:linear-gradient(135deg, #0d3b66, #1a4971); color:white; text-align:center; padding:80px 20px; border-radius:20px; margin:40px 0;">
    <h2 style="font-size:46px; margin:0 0 20px; font-weight:900;">Welcome to WASMSR Journal Portal</h2>
    <p style="font-size:22px; max-width:900px; margin:0 auto 30px; opacity:0.95;">
        Discover peer-reviewed research articles, submit your work, and explore high-quality scholarly publications in social and management sciences.
    </p>
    <form method="GET" style="max-width:720px; margin:0 auto;">
        <input name="search" type="search" placeholder="Search by title, author, keywords or abstract..."
               value="<?= htmlspecialchars($search) ?>"
               style="width:100%; padding:18px 20px; border-radius:50px 0 0 50px; border:none; font-size:17px; outline:none;">
        <button class="btn" type="submit" style="border-radius:0 50px 50px 0; padding:18px 40px; margin-left:-4px; font-weight:bold;">
            Search
        </button>
    </form>
</section>

<!-- ABOUT -->
<section class="card" style="padding:50px 30px; text-align:center; background:#f8fbff; border-radius:18px;">
    <h2 class="page-title" style="color:#0d3b66; font-size:38px;">About the Journal</h2>
    <p style="font-size:18px; line-height:1.8; max-width:900px; margin:20px auto; color:#444;">
        The <strong>West African Social and Management Sciences Review (WASMASR)</strong> is a peer-reviewed, open-access journal dedicated to publishing high-quality research in social sciences and management studies across West Africa and beyond. The Journal is owned by the Faculty of Social and Management Sciences, <strong>Benson Idahosa University.</strong>
    </p>
</section>

<!-- MAKE A SUBMISSION LINK -->
<div style="text-align:center; margin:50px 0 20px;">
    <a href="submission.php"
       style="display:inline-block; background:linear-gradient(135deg, #D4AF37, #f0d060); color:#0d3b66; padding:18px 65px; border-radius:50px; font-size:20px; font-weight:900; text-decoration:none; box-shadow:0 10px 35px rgba(212,175,55,0.4); transition:all 0.3s;"
       onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 18px 50px rgba(212,175,55,0.55)';"
       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 35px rgba(212,175,55,0.4)';">
        &#128393; Make a Submission
    </a>
</div>

<!-- LATEST ARTICLES -->
<section style="padding:90px 20px; background:linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%); position:relative; overflow:hidden;">
    <div style="max-width:1400px; margin:0 auto;">

        <div style="text-align:center; margin-bottom:70px;">
            <h2 style="font-size:48px; color:#0d3b66; margin:0; font-weight:900; letter-spacing:-1px;">
                Latest Published Articles
            </h2>
            <p style="font-size:21px; color:#444; margin:15px 0;">
                Fresh from <strong style="color:#0d3b66;">Volume <?= $current_volume ?></strong> &bull; Peer-Reviewed &amp; Open Access
            </p>
            <div style="width:160px; height:6px; background:linear-gradient(90deg, #0d3b66, #5e8cff); margin:25px auto; border-radius:3px;"></div>
        </div>

        <?php if (empty($latest_papers)): ?>
            <div style="text-align:center; padding:80px; background:white; border-radius:20px; box-shadow:0 15px 40px rgba(0,0,0,0.08);">
                <p style="font-size:22px; color:#666;">No articles published yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:40px;">
                <?php foreach ($latest_papers as $i => $p): ?>
                    <article style="
                        background:white;
                        border-radius:22px;
                        overflow:hidden;
                        box-shadow:0 15px 45px rgba(0,0,0,0.1);
                        transition:all 0.5s ease;
                        position:relative;
                        border:1px solid #eef2f7;"
                        onmouseover="this.style.transform='translateY(-12px)'; this.style.boxShadow='0 25px 60px rgba(0,0,0,0.18)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 15px 45px rgba(0,0,0,0.1)';">

                        <!-- Cover Image -->
                        <div style="height:220px; overflow:hidden; background:#f0f4f8; position:relative;">
                            <?php if (!empty($p['cover_image']) && file_exists('uploads/'.$p['cover_image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($p['cover_image']) ?>"
                                     alt="Cover" style="width:100%; height:100%; object-fit:cover; transition:transform 0.8s;"
                                     onmouseover="this.style.transform='scale(1.1)'"
                                     onmouseout="this.style.transform='scale(1)'">
                            <?php else: ?>
                                <div style="height:100%; background:linear-gradient(135deg, #667eea, #764ba2); display:flex; align-items:center; justify-content:center;">
                                    <span style="color:white; font-size:80px; font-weight:bold; opacity:0.3;">PDF</span>
                                </div>
                            <?php endif; ?>

                            <div style="position:absolute; top:16px; right:16px; background:rgba(13,59,102,0.95); color:white; padding:10px 18px; border-radius:30px; font-weight:700; font-size:14px; backdrop-filter:blur(8px);">
                                Vol <?= $p['volume'] ?> &bull; No <?= $p['issue_number'] ?>
                            </div>
                        </div>

                        <!-- Content -->
                        <div style="padding:30px;">
                            <h3 style="font-size:22px; margin:0 0 14px; color:#0d3b66; font-weight:800; line-height:1.35;">
                                <?= htmlspecialchars($p['title']) ?>
                            </h3>
                            <p style="margin:0 0 12px; color:#444; font-size:16px; font-weight:600;">
                                By <?= htmlspecialchars($p['author_name']) ?>
                            </p>
                            <p style="margin:0 0 20px; color:#666; font-size:15px; line-height:1.7;">
                                <?= htmlspecialchars(substr(strip_tags($p['abstract']), 0, 180)) ?>
                                <?= strlen(strip_tags($p['abstract'])) > 180 ? '&hellip;' : '' ?>
                            </p>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <a href="view_paper.php?id=<?= $p['id'] ?>"
                                   style="background:linear-gradient(135deg, #0d3b66, #1a4971); color:white; padding:14px 32px; border-radius:50px; text-decoration:none; font-weight:700; box-shadow:0 8px 25px rgba(13,59,102,0.3); transition:all 0.4s;"
                                   onmouseover="this.style.background='#0a3052'; this.style.transform='translateY(-3px)'"
                                   onmouseout="this.style.background='linear-gradient(135deg,#0d3b66,#1a4971)'; this.style.transform='translateY(0)'">
                                    Read Full Paper
                                </a>
                                <small style="color:#999; font-size:13px;">
                                    <?= date('d M Y', strtotime($p['published_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div style="text-align:center; margin-top:80px;">
                <a href="current-issue.php"
                   style="display:inline-block; background:transparent; color:#0d3b66; border:3px solid #0d3b66; padding:18px 60px; border-radius:50px; font-size:20px; font-weight:800; text-decoration:none; transition:all 0.4s;"
                   onmouseover="this.style.background='#0d3b66'; this.style.color='white';"
                   onmouseout="this.style.background='transparent'; this.style.color='#0d3b66';">
                    View Full Current Volume
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>