<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — WASMASR' : 'WASMASR Academic Journal' ?></title>
    <link rel="stylesheet" href="style.css">
    <?php if (isset($extra_meta)) echo $extra_meta; ?>
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <h1 class="site-title">WASMASR Academic Journal</h1>

        <button class="hamburger" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav">
            <a href="index.php">Home</a>

            <!-- Only show Register & Login when NOT logged in -->
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php">Register</a>
                <a href="login.php">Login</a>
            <?php endif; ?>

            <!-- Logged-in user: show role-based links + Logout -->
            <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_role'])): ?>
                <?php
                $role = strtolower(trim($_SESSION['user_role']));
                if ($role === 'author'):
                ?>
                    <a href="author_dashboard.php">Author Dashboard</a>
                    <a href="submit_paper.php">Submit Paper</a>
                <?php elseif ($role === 'admin'): ?>
                    <a href="admin_dashboard.php">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php endif; ?>

            <a href="contact.php">Contact</a>

            <!-- Issues Dropdown -->
            <div class="dropdown" data-dropdown>
                <button type="button" class="dropdown-trigger" data-dropdown-button>
                    Issues
                </button>
                <div class="dropdown-menu">
                    <div class="dropdown-section">
                        <a href="current-issue.php">Current Issue</a>
                        <a href="archives.php">Archives</a>
                    </div>
                </div>
            </div>

            <!-- More info Dropdown -->
            <div class="dropdown" data-dropdown>
                <button type="button" class="dropdown-trigger" data-dropdown-button>
                    More info
                </button>
                <div class="dropdown-menu">
                    <div class="dropdown-section">
                        <h4>About</h4>
                        <a href="about-journal.php">About Us</a>
                        <a href="authur-guideline.php">Authors Guidelines</a>
                    </div>
                    <div class="dropdown-section">
                        <h4>Policies</h4>
                        <a href="editorial-board.php">Editorial Board</a>
                        <a href="editorial-policy.php">Editorial & Review policy</a>
                        <a href="plagiarism.php">Ethics,Integrity,& Plagiarism</a>
                        <a href="open-policy.php">Open Access Policy</a>
                    </div>
                    <div class="dropdown-section">
                        <h4>Generative AI Policy</h4>
                        <a href="AI-authurs.php">For Authors</a>
                        <a href="reviewer-editors.php">Editors & Reviewers</a>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>
<div class="content">
    <script src="script.js" defer></script>