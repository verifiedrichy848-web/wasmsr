<?php
require_once 'db.php';
require_once 'header.php';

// Fetch 3 latest papers for sidebar
$current = $pdo->query("SELECT volume, issue_number FROM issues WHERE is_current = 1 LIMIT 1")->fetch();

if ($current) {
    $stmt = $pdo->prepare("
        SELECT id, title FROM papers 
        WHERE status = 'published' 
          AND volume = ? 
          AND issue_number = ?
        ORDER BY published_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$current['volume'], $current['issue_number']]);
} else {
    $stmt = $pdo->query("SELECT id, title FROM papers WHERE status='published' ORDER BY published_at DESC LIMIT 3");
}
$latest_papers = $stmt->fetchAll();
?>

<!-- MARQUEE -->
<div class="marquee">
    <marquee behavior="scroll" direction="left">WEST AFRICAN SOCIAL AND MANAGEMENT SCIENCES REVIEW</marquee>
</div>

<div style="display:flex; flex-wrap:wrap; gap:30px; max-width:1200px; margin:40px auto;">

    <!-- MAIN CONTENT -->
    <div style="flex:1; min-width:600px;">
        <div class="card" style="line-height:1.8;">
            <h2>About the Journal</h2>
            <p>
                The <strong>West African Social and Management Sciences Review (WASMSR)</strong> is a bi-annual, peer-reviewed journal of the Faculty of Social and Management Sciences, Benson Idahosa University. It publishes high-quality research on contemporary issues in the social and management sciences, with a particular focus on Africa and its engagement with the wider global context.
            </p>

            <p>
                The journal provides a forum for original empirical, theoretical, and review articles that advance knowledge, inform policy, and enrich professional practice in fields such as economics, accounting, business administration, public administration, sociology, political science, and related disciplines.
            </p>

            <h3>Readership</h3>
            <ul>
                <li>Scholars, researchers, and postgraduate students seeking a reputable outlet for rigorous research on African and global issues.</li>
                <li>Practitioners, policymakers, development professionals, and organizational leaders who require evidence-based insights.</li>
            </ul>

            <h3>Intended Contributors</h3>
            <p>
                Contributors include university academics, research institute staff, doctoral and master’s students, and professionals, provided their submissions meet international standards of originality, methodological rigor, and ethical research practice.
            </p>

            <h3>Article Types and Methodological Standards</h3>
            <p>
                The journal publishes full-length research articles, review papers, short reports, research notes, and scholarly book reviews demonstrating clear research questions, appropriate methods, robust analysis, and transparent reporting. Quantitative, qualitative, and mixed methods designs are all welcome.
            </p>

            <h3>Publication Frequency, Audience, and Access</h3>
            <p>
                WASMSR is published twice a year. By disseminating high-quality research from West Africa and beyond, the journal seeks to strengthen scholarly networks, support evidence-informed policy debates, and contribute to sustainable socio-economic development.
            </p>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div style="width:300px; flex-shrink:0;">
        <div class="card" style="padding:20px;">
            <h3>Latest Articles</h3>
            <?php if(empty($latest_papers)): ?>
                <p>No recent articles.</p>
            <?php else: ?>
                <ul>
                    <?php foreach($latest_papers as $p): ?>
                        <li><a href="view_paper.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="card" style="padding:20px; margin-top:20px;">
            <h3>Submission Guidelines</h3>
            <p>Authors are invited to submit original research articles, review papers, and short reports. Please ensure your manuscript follows international standards of originality, methodology, and ethical research practice.</p>
            <a class="btn" href="submit_paper.php">Submit a Paper</a>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
