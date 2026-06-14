<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';
?>

<!-- HERO -->
<section style="background:linear-gradient(135deg, #0d3b66 0%, #1a1a2e 100%); padding:80px 20px; text-align:center;">
    <div style="max-width:800px; margin:0 auto;">
        <h1 style="font-size:50px; font-weight:900; color:#D4AF37; margin:0 0 16px; letter-spacing:-1px; text-shadow:0 4px 20px rgba(212,175,55,0.3);">
            Make a Submission
        </h1>
        <p style="font-size:19px; color:rgba(255,255,255,0.8); line-height:1.7; margin:0;">
            Join a growing community of scholars advancing social and management sciences research across West Africa and beyond.
        </p>
        <div style="width:120px; height:5px; background:linear-gradient(90deg, #D4AF37, #f0d060); margin:28px auto 0; border-radius:3px;"></div>
    </div>
</section>

<!-- MAIN CONTENT -->
<div style="max-width:900px; margin:60px auto; padding:0 20px;">

    <!-- Checklist Card -->
    <div style="background:#fff; border-radius:24px; box-shadow:0 15px 50px rgba(0,0,0,0.1); overflow:hidden; margin-bottom:40px;">

        <!-- Card Header -->
        <div style="background:linear-gradient(135deg, #0d3b66, #1a4971); padding:36px 44px;">
            <h2 style="font-size:30px; font-weight:800; color:#D4AF37; margin:0 0 8px; display:flex; align-items:center; gap:12px;">
                <span>&#9989;</span> Submission Preparation Checklist
            </h2>
            <p style="color:rgba(255,255,255,0.7); font-size:15px; margin:0;">
                Please review all requirements carefully before submitting your manuscript.
            </p>
        </div>

        <!-- Card Body -->
        <div style="padding:44px;">

            <h3 style="font-size:22px; font-weight:800; color:#0d3b66; margin:0 0 30px; padding-bottom:16px; border-bottom:3px solid #D4AF37; display:inline-block;">
                &#128221; Submission Guidelines
            </h3>

            <div style="display:flex; flex-direction:column; gap:22px; margin-top:10px;">

                <!-- Item 1 -->
                <div style="display:flex; align-items:flex-start; gap:18px; padding:22px 24px; background:#f8fbff; border-radius:14px; border-left:5px solid #D4AF37;">
                    <span style="font-size:22px; flex-shrink:0; margin-top:2px;">&#128214;</span>
                    <div>
                        <strong style="color:#0d3b66; font-size:16px; display:block; margin-bottom:6px;">Word Count</strong>
                        <span style="color:#444; font-size:15px; line-height:1.7;">
                            Full-length articles should normally be <strong>5,000–8,000 words</strong> (excluding references, tables, and appendices). Short reports, book reviews, and grant or project notes should not exceed <strong>3,000 words</strong>.
                        </span>
                    </div>
                </div>

                <!-- Item 2 -->
                <div style="display:flex; align-items:flex-start; gap:18px; padding:22px 24px; background:#f8fbff; border-radius:14px; border-left:5px solid #D4AF37;">
                    <span style="font-size:22px; flex-shrink:0; margin-top:2px;">&#128203;</span>
                    <div>
                        <strong style="color:#0d3b66; font-size:16px; display:block; margin-bottom:6px;">Abstract & Keywords</strong>
                        <span style="color:#444; font-size:15px; line-height:1.7;">
                            Each manuscript must include an abstract of <strong>150–250 words</strong> and <strong>5–6 keywords</strong>.
                        </span>
                    </div>
                </div>

                <!-- Item 3 -->
                <div style="display:flex; align-items:flex-start; gap:18px; padding:22px 24px; background:#f8fbff; border-radius:14px; border-left:5px solid #D4AF37;">
                    <span style="font-size:22px; flex-shrink:0; margin-top:2px;">&#128196;</span>
                    <div>
                        <strong style="color:#0d3b66; font-size:16px; display:block; margin-bottom:6px;">Formatting</strong>
                        <span style="color:#444; font-size:15px; line-height:1.7;">
                            Manuscripts should be prepared in <strong>12pt Times New Roman</strong>, with <strong>1.5 line spacing</strong> and standard margins, submitted in an editable word processing format (e.g. Microsoft Word).
                        </span>
                    </div>
                </div>

                <!-- Item 4 -->
                <div style="display:flex; align-items:flex-start; gap:18px; padding:22px 24px; background:#f8fbff; border-radius:14px; border-left:5px solid #D4AF37;">
                    <span style="font-size:22px; flex-shrink:0; margin-top:2px;">&#128279;</span>
                    <div>
                        <strong style="color:#0d3b66; font-size:16px; display:block; margin-bottom:6px;">References</strong>
                        <span style="color:#444; font-size:15px; line-height:1.7;">
                            Authors must ensure that every <strong>in-text citation</strong> has a corresponding, complete reference entry in the reference list.
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- CTA -->
    <div style="text-align:center; padding:50px 40px; background:linear-gradient(135deg, #0d3b66, #1a1a2e); border-radius:24px; box-shadow:0 15px 50px rgba(0,0,0,0.15);">
        <h3 style="font-size:28px; font-weight:800; color:#fff; margin:0 0 12px;">
            Ready to Submit?
        </h3>
        <p style="color:rgba(255,255,255,0.7); font-size:16px; margin:0 0 32px;">
            Log in or register to submit your manuscript to WASMASR.
        </p>
        <a href="login.php"
           style="display:inline-block; background:linear-gradient(135deg, #D4AF37, #f0d060); color:#0d3b66; padding:18px 65px; border-radius:50px; font-size:19px; font-weight:900; text-decoration:none; box-shadow:0 10px 35px rgba(212,175,55,0.4); transition:all 0.3s;"
           onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 18px 50px rgba(212,175,55,0.55)';"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 35px rgba(212,175,55,0.4)';">
            &#128393; Submit Your Manuscript
        </a>
        <p style="color:rgba(255,255,255,0.4); font-size:13px; margin-top:16px;">
            You will be directed to log in or register before submitting.
        </p>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>