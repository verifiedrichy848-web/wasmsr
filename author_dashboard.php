<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'author') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// FIXED: Only select columns that actually exist in your DB
$stmt = $pdo->prepare("SELECT name, email, is_verified, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$author = $stmt->fetch();

if (!$author) {
    die("User not found");
}

// Build profile picture path (checks multiple possible locations)
$profile_pic_path = null;
if (!empty($author['profile_picture'])) {
    $filename = $author['profile_picture'];
    $paths = [
        "uploads/profile/$filename",
        "uploads/$filename",
        "papers/$filename",
        "profile/$filename"
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            $profile_pic_path = $path;
            break;
        }
    }
}

// Count submissions
$total = $pdo->query("SELECT COUNT(*) FROM papers WHERE author_id = $user_id")->fetchColumn();
$published = $pdo->query("SELECT COUNT(*) FROM papers WHERE author_id = $user_id AND status = 'published'")->fetchColumn();
$under_review = $total - $published;

// Fetch papers
$papers_stmt = $pdo->prepare("SELECT p.* FROM papers p WHERE p.author_id = ? ORDER BY p.submitted_at DESC");
$papers_stmt->execute([$user_id]);
$papers = $papers_stmt->fetchAll();

require_once __DIR__ . '/header.php';
?>

<!-- HERO WITH REAL PROFILE PICTURE -->
<section class="hero card" style="background:linear-gradient(135deg, #6B21A8, #1E3A8A); color:white; padding:90px 20px; text-align:center; border-radius:20px; margin:30px 20px;">
    <div style="max-width:1200px; margin:0 auto;">
        <!-- PROFILE PICTURE -->
        <div style="width:150px; height:150px; border-radius:50%; margin:0 auto 30px; overflow:hidden; border:6px solid rgba(255,255,255,0.3); box-shadow:0 15px 40px rgba(0,0,0,0.4); background:#8e44ad;">
            <?php if ($profile_pic_path): ?>
                <img src="<?= $profile_pic_path ?>?v=<?= time() ?>" alt="Profile" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:80px; font-weight:900; color:white;">
                    <?= strtoupper(substr($author['name'], 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>

        <h1 style="font-size:46px; margin:0 0 15px; font-weight:900;">My Dashboard</h1>
        <p style="font-size:24px; opacity:0.95; margin:0 0 10px;">
            Welcome back, <strong><?= htmlspecialchars($author['name']) ?></strong>
        </p>
        <p style="font-size:18px; opacity:0.9;">
            <?= htmlspecialchars($author['email']) ?>
            <?php if (!$author['is_verified']): ?>
                <span style="background:#c62828; color:white; padding:8px 18px; border-radius:30px; font-size:15px; margin-left:15px;">
                    Verification Pending
                </span>
            <?php else: ?>
                <span style="color:#b2ff59; margin-left:15px; font-weight:bold;">Verified</span>
            <?php endif; ?>
        </p>
    </div>
</section>

<!-- STATS -->
<div style="max-width:1400px; margin:0 auto; padding:20px;">
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:30px; margin:60px 0;">
        <div class="card" style="text-align:center;padding:35px;background:#f3e8ff;border-radius:22px;">
            <h3 style="font-size:52px;margin:0;color:#6B21A8;font-weight:900;"><?= $total ?></h3>
            <p style="margin:15px 0 0;color:#555;font-size:18px;">Total Submissions</p>
        </div>
        <div class="card" style="text-align:center;padding:35px;background:#e8f5e8;border-radius:22px;">
            <h3 style="font-size:52px;margin:0;color:#1b5e20;font-weight:900;"><?= $published ?></h3>
            <p style="margin:15px 0 0;color:#555;font-size:18px;">Published</p>
        </div>
        <div class="card" style="text-align:center;padding:35px;background:#fff3e0;border-radius:22px;">
            <h3 style="font-size:52px;margin:0;color:#e65100;font-weight:900;"><?= $under_review ?></h3>
            <p style="margin:15px 0 0;color:#555;font-size:18px;">Under Review</p>
        </div>
    </div>

    <!-- BUTTONS -->
    <div style="text-align:center;margin:60px 0;">
        <a href="update_profile.php" class="btn" style="background:#6B21A8;padding:20px 55px;font-size:20px;border-radius:50px;margin:0 15px;">
            Update Profile
        </a>
        <a href="submit_paper.php" class="btn" style="background:#1b5e20;padding:20px 55px;font-size:20px;border-radius:50px;margin:0 15px;">
            Submit New Paper
        </a>
    </div>

    <!-- PAPERS -->
    <div style="text-align:center;margin:80px 0;">
        <h2 style="font-size:48px;color:#0d3b66;margin:0;font-weight:900;">My Research Papers</h2>
        <p style="font-size:21px;color:#555;margin:18px 0;">View and track all your submissions</p>
        <div style="width:200px;height:6px;background:linear-gradient(90deg,#0d3b66,#5e8cff);margin:30px auto;border-radius:3px;"></div>
    </div>

    <?php if (empty($papers)): ?>
        <div class="card" style="text-align:center;padding:100px;background:#f9f9fb;border-radius:24px;">
            <p style="font-size:24px;color:#666;margin:0 0 40px;">No submissions yet</p>
            <a href="submit_paper.php" class="btn" style="background:#1b5e20;padding:20px 60px;font-size:22px;border-radius:50px;">
                Submit Your First Paper
            </a>
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(380px,1fr));gap:40px;">
            <?php foreach ($papers as $p): ?>
                <article style="background:white;border-radius:22px;overflow:hidden;box-shadow:0 15px 45px rgba(0,0,0,0.1);transition:all 0.4s;"
                onmouseover="this.style.transform='translateY(-12px)';this.style.boxShadow='0 30px 60px rgba(0,0,0,0.18)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 15px 45px rgba(0,0,0,0.1)'">
                    <div style="height:240px;overflow:hidden;position:relative;background:#f8f9fa;">
                        <?php if (!empty($p['cover_image']) && file_exists('uploads/'.$p['cover_image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($p['cover_image']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.6s;"
                            onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <?php else: ?>
                            <div style="height:100%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;">
                                <span style="color:white;font-size:80px;font-weight:bold;opacity:0.3;">PDF</span>
                            </div>
                        <?php endif; ?>
                        <div style="position:absolute;top:18px;right:18px;background:<?= $p['status']=='published'?'#166534':($p['status']=='rejected'?'#c62828':'#e65100') ?>;color:white;padding:10px 18px;border-radius:30px;font-weight:700;font-size:14px;">
                            <?= $p['status']=='published' ? "Vol {$p['volume']} • No {$p['issue_number']}" : ucfirst($p['status']) ?>
                        </div>
                    </div>
                    <div style="padding:32px;">
                        <h3 style="font-size:23px;margin:0 0 14px;color:#0d3b66;font-weight:800;line-height:1.35;">
                            <?= htmlspecialchars($p['title']) ?>
                        </h3>
                        <p style="margin:0 0 12px;color:#444;font-size:16px;font-weight:600;">
                            Submitted: <?= date('d M Y', strtotime($p['submitted_at'])) ?>
                        </p>
                        <p style="margin:0 0 22px;color:#555;font-size:15.5px;line-height:1.7;">
                            <?= htmlspecialchars(substr($p['abstract'], 0, 180)) ?><?= strlen($p['abstract']) > 180 ? '…' : '' ?>
                        </p>
                        <div style="text-align:right;">
                            <?php if ($p['status'] == 'published'): ?>
                                <a href="view_paper.php?id=<?= $p['id'] ?>" 
                                   style="display:inline-block;background:linear-gradient(135deg,#0d3b66,#1a4971);color:white;padding:15px 35px;border-radius:50px;text-decoration:none;font-weight:700;font-size:16px;box-shadow:0 10px 30px rgba(13,59,102,0.3);transition:all 0.4s;"
                                   onmouseover="this.style.background='#0a3052';this.style.transform='translateY(-3px)'"
                                   onmouseout="this.style.background='linear-gradient(135deg,#0d3b66,#1a4971)';this.style.transform='translateY(0)'">
                                    View Published Paper
                                </a>
                            <?php elseif ($p['status'] == 'rejected'): ?>
                                <span style="color:#c62828;font-weight:bold;font-size:17px;">Paper Rejected</span>
                            <?php else: ?>
                                <span style="color:#e65100;font-weight:bold;font-size:17px;">Under Review</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>