<?php
session_start();

if (!isset($_SESSION['exam_records'])) {
    $_SESSION['exam_records'] = [];
}

$activeAnalysis = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim(htmlspecialchars($_POST['student_name'] ?? 'Kaelen Voss'));
    $rollNumber  = trim(htmlspecialchars($_POST['roll_number'] ?? 'EX-2026-8810'));
    $examTerm    = trim(htmlspecialchars($_POST['exam_term'] ?? 'Mid-Term Semester Evaluation'));
    $stream      = trim(htmlspecialchars($_POST['stream'] ?? 'Computer Science & AI'));

    // Subject Scores (Out of 100)
    $sMath     = max(0, min(100, floatval($_POST['score_math'] ?? 95)));
    $sPhysics  = max(0, min(100, floatval($_POST['score_physics'] ?? 88)));
    $sCS       = max(0, min(100, floatval($_POST['score_cs'] ?? 98)));
    $sDataAlgo = max(0, min(100, floatval($_POST['score_data_algo'] ?? 92)));
    $sEthicsAI = max(0, min(100, floatval($_POST['score_ethics_ai'] ?? 90)));

    $subjects = [
        'Advanced Mathematics' => $sMath,
        'Quantum Physics'      => $sPhysics,
        'Computer Science'     => $sCS,
        'Data Structures & Algo' => $sDataAlgo,
        'AI Ethics & Safety'   => $sEthicsAI
    ];

    // Statistical Computations
    $totalObtained = array_sum($subjects);
    $maxPossible   = count($subjects) * 100;
    $percentage    = round(($totalObtained / $maxPossible) * 100, 2);

    // Letter Grade & Honors Designation
    if ($percentage >= 90) {
        $grade = 'A+';
        $status = 'Passed with High Distinction';
        $gradeBadge = '🏆 SUMMA CUM LAUDE';
        $accentColor = '#ff007f'; // Electric Pink
    } elseif ($percentage >= 80) {
        $grade = 'A';
        $status = 'Passed with Merit';
        $gradeBadge = '⭐ MAGNA CUM LAUDE';
        $accentColor = '#00f2fe'; // Neon Cyan
    } elseif ($percentage >= 70) {
        $grade = 'B';
        $status = 'Passed';
        $gradeBadge = '🔷 HONORS';
        $accentColor = '#10b981'; // Emerald
    } elseif ($percentage >= 60) {
        $grade = 'C';
        $status = 'Satisfactory';
        $gradeBadge = '🟡 STANDARD PASS';
        $accentColor = '#ffb800'; // Amber Gold
    } else {
        $grade = 'F';
        $status = 'Needs Retake / Academic Review';
        $gradeBadge = '🔴 ACADEMIC WARNING';
        $accentColor = '#ff3366'; // Red Coral
    }

    // Rank Estimation percentile based on curve
    $estimatedPercentile = round(min(99.9, max(10, ($percentage * 1.05) - 3)), 1);

    $activeAnalysis = [
        'analysis_id'     => 'EXAM-' . strtoupper(substr(md5($rollNumber . time()), 0, 8)),
        'student_name'    => $studentName,
        'roll_number'     => $rollNumber,
        'exam_term'       => $examTerm,
        'stream'          => $stream,
        'subjects'        => $subjects,
        'total_obtained'  => $totalObtained,
        'max_possible'    => $maxPossible,
        'percentage'      => $percentage,
        'grade'           => $grade,
        'status'          => $status,
        'grade_badge'     => $gradeBadge,
        'accent_color'    => $accentColor,
        'percentile'      => $estimatedPercentile,
        'analyzed_at'     => date('H:i | M d, Y')
    ];

    // Store in Session Audit Log
    array_unshift($_SESSION['exam_records'], $activeAnalysis);
    $_SESSION['exam_records'] = array_slice($_SESSION['exam_records'], 0, 5);
}

// Clear Session Action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['exam_records'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Result Analysis Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="portal-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <span class="header-pill">Task 26 • Academic Performance Engine</span>
            <h1>Examination Result Analysis Dashboard</h1>
            <p>Compute aggregate scores, percentile curves, grade honor distributions, and subject mastery profiles.</p>
        </header>

        <!-- RESULT ANALYSIS CARD -->
        <?php if ($activeAnalysis): ?>
            <div class="analysis-card" style="border-top-color: <?php echo $activeAnalysis['accent_color']; ?>;">
                <div class="card-top">
                    <div>
                        <span class="record-id">ANALYTICS TOKEN: <?php echo $activeAnalysis['analysis_id']; ?></span>
                        <h2><?php echo htmlspecialchars($activeAnalysis['student_name']); ?></h2>
                        <p class="student-meta"><?php echo htmlspecialchars($activeAnalysis['roll_number']); ?> • <?php echo htmlspecialchars($activeAnalysis['stream']); ?></p>
                    </div>
                    <span class="honor-tag" style="background: <?php echo $activeAnalysis['accent_color']; ?>22; color: <?php echo $activeAnalysis['accent_color']; ?>; border-color: <?php echo $activeAnalysis['accent_color']; ?>88;">
                        <?php echo $activeAnalysis['grade_badge']; ?>
                    </span>
                </div>

                <!-- METRICS HIGHLIGHT GRID -->
                <div class="metrics-grid">
                    <div class="m-box">
                        <span class="m-title">Grade Rank</span>
                        <strong class="m-big" style="color: <?php echo $activeAnalysis['accent_color']; ?>;"><?php echo $activeAnalysis['grade']; ?></strong>
                        <span class="m-sub"><?php echo $activeAnalysis['status']; ?></span>
                    </div>

                    <div class="m-box">
                        <span class="m-title">Overall Score</span>
                        <strong class="m-big"><?php echo $activeAnalysis['percentage']; ?>%</strong>
                        <span class="m-sub"><?php echo $activeAnalysis['total_obtained']; ?> / <?php echo $activeAnalysis['max_possible']; ?> Total Marks</span>
                    </div>

                    <div class="m-box">
                        <span class="m-title">Batch Curve Rank</span>
                        <strong class="m-big text-pink"><?php echo $activeAnalysis['percentile']; ?>th</strong>
                        <span class="m-sub">Estimated Cohort Percentile</span>
                    </div>
                </div>

                <!-- SUBJECT PROFICIENCY SPECTRUM -->
                <div class="subject-breakdown">
                    <h4>Subject Competency Scorecard</h4>
                    <div class="subject-list">
                        <?php foreach ($activeAnalysis['subjects'] as $subName => $subScore): ?>
                            <div class="subject-item">
                                <div class="s-info">
                                    <span><?php echo $subName; ?></span>
                                    <strong><?php echo $subScore; ?> / 100</strong>
                                </div>
                                <div class="s-bar-track">
                                    <div class="s-bar-fill" style="width: <?php echo $subScore; ?>%; background: linear-gradient(90deg, <?php echo $activeAnalysis['accent_color']; ?>, #00f2fe);"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card-footer">
                    <span>Term: <strong><?php echo htmlspecialchars($activeAnalysis['exam_term']); ?></strong></span>
                    <span>Processed: <?php echo $activeAnalysis['analyzed_at']; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- EXAMINATION DATA INPUT FORM -->
        <form action="index.php" method="POST" class="exam-form">
            <span class="form-title">Enter Examination Scorecard Data</span>
            
            <div class="form-grid">
                <div class="field-group">
                    <label for="student_name">Student Full Name</label>
                    <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($_POST['student_name'] ?? 'Kaelen Voss'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="roll_number">Roll / Registration Number</label>
                    <input type="text" id="roll_number" name="roll_number" value="<?php echo htmlspecialchars($_POST['roll_number'] ?? 'EX-2026-8810'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="stream">Academic Stream / Major</label>
                    <input type="text" id="stream" name="stream" value="<?php echo htmlspecialchars($_POST['stream'] ?? 'Computer Science & AI'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="exam_term">Evaluation Term / Examination</label>
                    <input type="text" id="exam_term" name="exam_term" value="<?php echo htmlspecialchars($_POST['exam_term'] ?? 'Mid-Term Semester Evaluation'); ?>" required>
                </div>

                <div class="field-group span-2 subject-inputs">
                    <label>Subject Marks Allocation (Max 100 per subject)</label>
                    <div class="scores-grid">
                        <div class="score-input-box">
                            <span>Advanced Math</span>
                            <input type="number" step="0.5" name="score_math" min="0" max="100" value="<?php echo $_POST['score_math'] ?? 95; ?>" required>
                        </div>
                        <div class="score-input-box">
                            <span>Quantum Physics</span>
                            <input type="number" step="0.5" name="score_physics" min="0" max="100" value="<?php echo $_POST['score_physics'] ?? 88; ?>" required>
                        </div>
                        <div class="score-input-box">
                            <span>Computer Science</span>
                            <input type="number" step="0.5" name="score_cs" min="0" max="100" value="<?php echo $_POST['score_cs'] ?? 98; ?>" required>
                        </div>
                        <div class="score-input-box">
                            <span>Data Structures</span>
                            <input type="number" step="0.5" name="score_data_algo" min="0" max="100" value="<?php echo $_POST['score_data_algo'] ?? 92; ?>" required>
                        </div>
                        <div class="score-input-box">
                            <span>AI Ethics</span>
                            <input type="number" step="0.5" name="score_ethics_ai" min="0" max="100" value="<?php echo $_POST['score_ethics_ai'] ?? 90; ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Run Analytics & Generate Dashboard Card</button>
        </form>

        <!-- HISTORY PANEL -->
        <?php if (!empty($_SESSION['exam_records'])): ?>
            <div class="history-panel">
                <div class="history-head">
                    <span>Recent Examination Reports</span>
                    <a href="index.php?action=clear" class="clear-btn">Clear Log</a>
                </div>
                <div class="history-grid">
                    <?php foreach ($_SESSION['exam_records'] as $rec): ?>
                        <div class="history-card">
                            <div class="h-top">
                                <strong><?php echo htmlspecialchars($rec['student_name']); ?></strong>
                                <span class="h-grade" style="color: <?php echo $rec['accent_color']; ?>; border-color: <?php echo $rec['accent_color']; ?>66;">
                                    <?php echo $rec['grade']; ?> (<?php echo $rec['percentage']; ?>%)
                                </span>
                            </div>
                            <span class="h-sub"><?php echo htmlspecialchars($rec['roll_number']); ?> • <?php echo htmlspecialchars($rec['stream']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>