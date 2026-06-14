<?php
// call-for-papers.php
session_start(); // if you need session features later
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call for Papers – WAJSMSR</title>
    <link rel="stylesheet" href="style.css"> <!-- your existing stylesheet -->
    <style>
        :root {
            --blue: #1E3A8A;
            --purple: #6B21A8;
            --gold: #D4AF37;
            --white: #ffffff;
            --light-bg: #f8f8f8;
        }

        body {
            font-family: Arial, sans-serif;
            background: var(--light-bg);
            color: #333;
            line-height: 1.7;
        }

        .cfp-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .cfp-hero {
            background: linear-gradient(135deg, var(--purple), var(--blue));
            color: white;
            text-align: center;
            padding: 60px 20px;
            border-radius: 12px 12px 0 0;
            margin-bottom: 0;
        }

        .cfp-hero h1 {
            font-size: 2.8rem;
            margin-bottom: 0.8rem;
        }

        .cfp-hero p {
            font-size: 1.3rem;
            opacity: 0.95;
        }

        .cfp-content {
            background: white;
            padding: 40px 30px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        h2, h3 {
            color: var(--purple);
            margin: 2rem 0 1rem;
        }

        h2 {
            font-size: 2.1rem;
            border-bottom: 2px solid var(--gold);
            padding-bottom: 0.6rem;
            display: inline-block;
        }

        ul {
            padding-left: 25px;
            margin: 1rem 0;
        }

        li {
            margin-bottom: 0.8rem;
        }

        .highlight {
            background: rgba(212,175,55,0.1);
            padding: 1rem;
            border-left: 5px solid var(--gold);
            margin: 1.5rem 0;
            font-weight: 500;
        }

        .fee-box {
            background: #f0f8ff;
            border: 1px solid var(--blue);
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .contact-box {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2.5rem;
            border-left: 5px solid var(--purple);
        }

        .btn {
            display: inline-block;
            background: var(--purple);
            color: white;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 1.5rem;
            transition: 0.3s;
        }

        .btn:hover {
            background: var(--blue);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .cfp-hero h1 { font-size: 2.2rem; }
            .cfp-hero p { font-size: 1.1rem; }
            .cfp-content { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<div class="page-container">

    <?php require_once __DIR__ . '/header.php'; ?>

    <div class="cfp-container">
        <div class="cfp-hero">
            <h1>Call for Papers</h1>
            <p>Volume 11, Number 1 – June 2026</p>
        </div>

        <div class="cfp-content">
            <p><strong>West African Journal of Social and Management Sciences Review (WAJSMSR)</strong><br>
            Bi-annual Journal of the Faculty of Social and Management Sciences<br>
            Benson Idahosa University, Benin City, Nigeria</p>

            <div class="highlight">
                The Editorial Board invites scholars, researchers, academics, professionals, and students to submit original manuscripts for <strong>Volume 11, Number 1</strong>, scheduled for publication in <strong>June 2026</strong>.
            </div>

            <h2>Journal Scope</h2>
            <p>WAJSMSR is an interdisciplinary, peer-reviewed journal committed to advancing scholarship in the broad fields of Social and Management Sciences. The journal provides a platform for rigorous academic discourse and the dissemination of innovative research findings relevant to Africa and the global community.</p>

            <h3>Areas of Interest</h3>
            <ul>
                <li>Accounting</li>
                <li>Banking and Finance</li>
                <li>Business Administration</li>
                <li>Economics</li>
                <li>Political Science</li>
                <li>Public Administration</li>
                <li>Sociology</li>
                <li>International Relations</li>
                <li>Entrepreneurship</li>
                <li>Development Studies</li>
                <li>Peace and Conflict Studies</li>
                <li>Other related disciplines within the Social and Management Sciences</li>
            </ul>

            <h2>Review Process</h2>
            <p>WAJSMSR operates a <strong>double-blind peer review</strong> system. All submitted manuscripts are first assessed by the Editorial Board for suitability. Suitable papers are then sent to two subject experts for independent review.</p>
            <p>Authors are solely responsible for the contents of their submissions and for obtaining copyright permissions where necessary. The journal maintains a <strong>zero-tolerance policy</strong> towards plagiarism, generative AI misuse, and other forms of academic misconduct.</p>

            <h2>Submission Guidelines</h2>
            <ul>
                <li>Manuscripts must be original and written in clear, good English.</li>
                <li>Full-length articles: 5,000 – 8,000 words</li>
                <li>Short reports, reviews, grant-related writings: not exceeding 3,000 words</li>
                <li>Referencing style: APA (7th edition)</li>
                <li>Include an abstract of 200–250 words</li>
                <li>Font: 12-point Times New Roman</li>
            </ul>

            <div class="fee-box">
                <h3>Publication Fee</h3>
                <p><strong>₦35,000.00</strong> (Thirty-Five Thousand Naira Only) – payable upon acceptance (Nigeria)</p>
                <p><strong>$35</strong> (Thirty-Five Dollars Only) – Authors outside Nigeria</p>
                <p style="margin-top:1rem;"><strong>Bank Details:</strong></p>
                <p><strong>Account Name:</strong> Journal of Social and Management Sciences (JSMS)<br>
                <strong>Account Number:</strong> 1100054823<br>
                <strong>Bank:</strong> Above Only Microfinance Bank</p>
            </div>

            <h2>Submission Procedure</h2>
            <p>All manuscripts should be submitted as a Microsoft Word document via email to:</p>
            <p style="font-size:1.3rem; font-weight:bold; margin:1.5rem 0;">
                <a href="mailto:wajsmsr@biu.edu.ng" style="color:var(--purple);">wajsmsr@biu.edu.ng</a>
            </p>

            <div class="contact-box">
                <h3>Correspondence</h3>
                <p><strong>The Editor-in-Chief</strong><br>
                West African Journal of Social and Management Sciences Review (WAJSMSR)<br>
                Faculty of Social and Management Sciences<br>
                Benson Idahosa University<br>
                Benin City, Nigeria</p>
                <p style="margin-top:1rem;">
                    Mobile: +234 8053314506, +234 8038503165<br>
                    Email: <a href="mailto:wajsmsr@biu.edu.ng" style="color:var(--purple);">wajsmsr@biu.edu.ng</a>
                </p>
            </div>

            <a href="submit_paper.php" class="btn" style="display:inline-block; margin-top:2rem;">
                Submit Your Manuscript Now
            </a>
        </div>
    </div>

    <?php require_once __DIR__ . '/footer.php'; ?>

</div>
</body>
</html>