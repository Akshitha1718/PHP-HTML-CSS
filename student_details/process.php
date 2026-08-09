<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details Result</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="glass-card">
        <div class="card-header">
            <span class="badge" style="background: linear-gradient(135deg, #10b981, #059669);">Verified Record</span>
            <h2>Submitted Profile</h2>
            <p>Data transferred securely via POST</p>
        </div>

        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $fullName   = htmlspecialchars(trim($_POST['fullName'] ?? ''));
            $rollNo     = htmlspecialchars(trim($_POST['rollNo'] ?? ''));
            $department = htmlspecialchars(trim($_POST['department'] ?? ''));
            $semester   = htmlspecialchars(trim($_POST['semester'] ?? ''));

            echo "<table class='details-table'>";
            echo "<tr><th>Field</th><th>Submitted Detail</th></tr>";
            echo "<tr><td><strong>Full Name</strong></td><td>" . $fullName . "</td></tr>";
            echo "<tr><td><strong>Roll Number</strong></td><td>" . $rollNo . "</td></tr>";
            echo "<tr><td><strong>Department</strong></td><td>" . $department . "</td></tr>";
            echo "<tr><td><strong>Semester</strong></td><td>Semester " . $semester . "</td></tr>";
            echo "</table>";
        } else {
            echo "<p style='color: #ef4444; text-align: center; font-weight: 600;'>Invalid Request Method.</p>";
        }
        ?>

        <a href="index.html" class="back-btn">&larr; Return to Form</a>
    </div>
</body>
</html>