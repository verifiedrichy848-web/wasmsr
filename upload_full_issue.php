<?php
session_start();
if ($_SESSION['user_role'] !== 'admin') die("Access denied.");

if ($_POST && isset($_FILES['full_pdf'])) {
    $issue_code = trim($_POST['issue_code']);
    if (!preg_match('/^[a-zA-Z0-9\-]+$/', $issue_code)) {
        die("Invalid issue code.");
    }

    $target_dir = __DIR__ . "/../archives/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    $filename = $issue_code . ".pdf";
    $target = $target_dir . $filename;

    if (move_uploaded_file($_FILES["full_pdf"]["tmp_name"], $target)) {
        echo "<h2 style='color:green; text-align:center; margin:50px;'>Full Issue PDF Uploaded Successfully!<br>
              <a href='../archives/$filename' target='_blank'>Click here to view</a></h2>";
        echo "<p style='text-align:center;'><a href='dashboard.php'>← Back to Dashboard</a></p>";
    } else {
        echo "<h2 style='color:red;'>Upload failed.</h2>";
    }
}
?>