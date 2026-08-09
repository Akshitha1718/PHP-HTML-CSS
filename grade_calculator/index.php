<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Grade Calculator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="grade-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- TRANSCRIPT VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Evaluation Complete</span>
                <h2>Academic Transcript</h2>
                <p>Term Performance Report</p>
            </div>

            <?php
            $studentName = htmlspecialchars(trim($_POST['studentName'] ?? 'Student'));
            $rollNo      = htmlspecialchars(trim($_POST['rollNo'] ?? 'N/A'));
            
            $m1 = floatval($_POST['m1'] ?? 0);
            $m2 = floatval($_POST['m2'] ?? 0);
            $m3 = floatval($_POST['m3'] ?? 0);
            $m4 = floatval($_POST['m4'] ?? 0);

            $totalObtained = $m1 + $m2 + $m3 + $m4;
            $maxMarks      = 400;
            $percentage    = ($totalObtained / $maxMarks) * 100;

            // Grade Logic
            if ($percentage >= 90) {
                $grade = "A+";
                $remark = "Outstanding Performance";
                $badgeColor = "grade-aplus";
            } elseif ($percentage >= 80) {
                $grade = "A";
                $remark = "Excellent Achievement";
                $badgeColor = "grade-a";
            } elseif ($percentage >= 70) {
                $grade = "B";
                $remark = "Very Good Effort";
                $badgeColor = "grade-b";
            } elseif ($percentage >= 60) {
                $grade = "C";
                $remark = "Satisfactory Progress";
                $badgeColor = "grade-c";
            } else {
                $grade = "F";
                $remark = "Needs Immediate Improvement";
                $badgeColor = "grade-f";
            }

            $isPassed = ($m1 >= 40 && $m2 >= 40 && $m3 >= 40 && $m4 >= 40);
            $statusText = $isPassed ? "PASSED" : "FAILED (Subject Backlog)";
            ?>

            <div class="student-meta">
                <div><span>Student:</span> <strong><?php echo $studentName; ?></strong></div>
                <div><span>Roll No:</span> <strong><?php echo $rollNo; ?></strong></div>
            </div>

            <table class="marks-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Max Marks</th>
                        <th>Obtained</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Web Development & Design</td><td>100</td><td><?php echo $m1; ?></td></tr>
                    <tr><td>Database Management Systems</td><td>100</td><td><?php echo $m2; ?></td></tr>
                    <tr><td>Data Structures & Algorithms</td><td>100</td><td><?php echo $m3; ?></td></tr>
                    <tr><td>Computer Networks</td><td>100</td><td><?php echo $m4; ?></td></tr>
                </tbody>
            </table>

            <div class="results-grid">
                <div class="res-box">
                    <span>Total Marks</span>
                    <h4><?php echo $totalObtained; ?> / <?php echo $maxMarks; ?></h4>
                </div>
                <div class="res-box">
                    <span>Percentage</span>
                    <h4><?php echo number_format($percentage, 2); ?>%</h4>
                </div>
                <div class="res-box">
                    <span>Grade</span>
                    <h4 class="<?php echo $badgeColor; ?>"><?php echo $grade; ?></h4>
                </div>
            </div>

            <div class="status-box <?php echo $isPassed ? 'status-pass' : 'status-fail'; ?>">
                <strong>Status: <?php echo $statusText; ?></strong> — <?php echo $remark; ?>
            </div>

            <a href="index.php" class="recalc-btn">&larr; Evaluate Another Student</a>

        <?php else: ?>
            <!-- INPUT FORM VIEW -->
            <div class="card-header">
                <span class="badge">Assessment Portal</span>
                <h2>Grade Calculator</h2>
                <p>Enter subject marks to calculate final GPA & percentage</p>
            </div>

            <form action="index.php" method="POST" class="grade-form">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="studentName">Student Name</label>
                        <input type="text" id="studentName" name="studentName" placeholder="e.g. Liam Vance" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="rollNo">Roll Number</label>
                        <input type="text" id="rollNo" name="rollNo" placeholder="24CS102" required>
                    </div>
                </div>

                <div class="marks-block">
                    <h4>Course Marks (Out of 100)</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Web Dev & Design</label>
                            <input type="number" name="m1" min="0" max="100" placeholder="0-100" required>
                        </div>
                        <div class="form-group">
                            <label>DBMS</label>
                            <input type="number" name="m2" min="0" max="100" placeholder="0-100" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Data Structures</label>
                            <input type="number" name="m3" min="0" max="100" placeholder="0-100" required>
                        </div>
                        <div class="form-group">
                            <label>Networks</label>
                            <input type="number" name="m4" min="0" max="100" placeholder="0-100" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Calculate Grade & Generate Transcript</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>