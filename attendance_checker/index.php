<?php
session_start();

if (!isset($_SESSION['attendance_audit_log'])) {
    $_SESSION['attendance_audit_log'] = [];
}

// Preset Academic Criteria & Thresholds
$defaultThreshold = 75; // 75% standard compliance

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName   = trim(htmlspecialchars($_POST['student_name'] ?? 'Siddharth Rao'));
    $studentId     = trim(htmlspecialchars($_POST['student_id'] ?? 'REG-2026-8891'));
    $courseName    = trim(htmlspecialchars($_POST['course_name'] ?? 'Advanced Data Structures'));
    $totalClasses  = max(1, intval($_POST['total_classes'] ?? 40));
    $attendedCount = max(0, min($totalClasses, intval($_POST['attended_classes'] ?? 28)));
    $requiredPct   = max(50, min(95, floatval($_POST['threshold_pct'] ?? $defaultThreshold)));

    // Calculation Logic
    $currentPct = ($attendedCount / $totalClasses) * 100;
    
    // Consecutive Classes Needed or Skips Allowed
    $classesNeeded = 0;
    $skipsAllowed = 0;

    if ($currentPct < $requiredPct) {
        // Required classes formula: (Threshold * Conducted - 100 * Attended) / (100 - Threshold)
        $targetDecimal = $requiredPct / 100;
        $needed = ceil(($targetDecimal * $totalClasses - $attendedCount) / (1 - $targetDecimal));
        $classesNeeded = max(0, $needed);
    } else {
        // Skips allowed formula: (100 * Attended - Threshold * Conducted) / Threshold
        $targetDecimal = $requiredPct / 100;
        $allowed = floor(($attendedCount - $targetDecimal * $totalClasses) / $targetDecimal);
        $skipsAllowed = max(0, $allowed);
    }

    // Eligibility Status Category
    if ($currentPct >= $requiredPct) {
        $status = 'COMPLIANT';
        $badge = '✅ EXAM ELIGIBLE';
        $color = '#10b981'; // Lime Emerald
        $summary = "Student meets the $requiredPct% threshold. Allowed to sit for final end-term examinations.";
    } elseif ($currentPct >= ($requiredPct - 10)) {
        $status = 'WARNING';
        $badge = '⚠️ AT RISK / WARNING';
        $color = '#ffb800'; // Electric Amber
        $summary = "Attendance is currently below the required threshold. Immediate recovery plan needed.";
    } else {
        $status = 'CRITICAL';
        $badge = '🚫 DISQUALIFIED';
        $color = '#ff2a75'; // Crimson Neon
        $summary = "Attendance is severely deficient. Subject to academic condonation or exam debarment.";
    }

    $result = [
        'id'             => 'AUD-' . strtoupper(substr(md5(time() . $studentId), 0, 6)),
        'student_name'   => $studentName,
        'student_id'     => $studentId,
        'course_name'    => $courseName,
        'total_classes'  => $totalClasses,
        'attended_count' => $attendedCount,
        'missed_count'   => ($totalClasses - $attendedCount),
        'current_pct'    => round($currentPct, 2),
        'required_pct'   => $requiredPct,
        'status'         => $status,
        'badge'          => $badge,
        'color'          => $color,
        'summary'        => $summary,
        'classes_needed' => $classesNeeded,
        'skips_allowed'  => $skipsAllowed,
        'timestamp'      => date('H:i:s | M d, Y')
    ];

    array_unshift($_SESSION['attendance_audit_log'], $result);
    $_SESSION['attendance_audit_log'] = array_slice($_SESSION['attendance_audit_log'], 0, 5);
}

// Clear Audit Log
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['attendance_audit_log'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Attendance Threshold Checker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app-viewport">
        
        <!-- TOP HEADER -->
        <header class="app-header">
            <span class="header-pill">Institutional Compliance Engine</span>
            <h1>Academic Attendance Threshold Checker</h1>
            <p>Evaluate course participation, audit exam eligibility, and compute corrective attendance projections.</p>
        </header>

        <!-- AUDIT REPORT CARD -->
        <?php if ($result): ?>
            <div class="result-card" style="border-top-color: <?php echo $result['color']; ?>;">
                <div class="result-top">
                    <div>
                        <span class="status-badge" style="background: <?php echo $result['color']; ?>20; color: <?php echo $result['color']; ?>; border-color: <?php echo $result['color']; ?>88;">
                            <?php echo $result['badge']; ?>
                        </span>
                        <h2><?php echo htmlspecialchars($result['student_name']); ?> <small>(<?php echo htmlspecialchars($result['student_id']); ?>)</small></h2>
                        <p class="course-title">📚 Course: <strong><?php echo htmlspecialchars($result['course_name']); ?></strong></p>
                    </div>
                    <div class="pct-ring-box">
                        <span class="pct-value" style="color: <?php echo $result['color']; ?>;"><?php echo $result['current_pct']; ?>%</span>
                        <span class="pct-label">Current Rate</span>
                    </div>
                </div>

                <!-- PROGRESS METER -->
                <div class="meter-wrapper">
                    <div class="meter-labels">
                        <span>0%</span>
                        <span class="target-marker" style="left: <?php echo $result['required_pct']; ?>%;">Target: <?php echo $result['required_pct']; ?>%</span>
                        <span>100%</span>
                    </div>
                    <div class="meter-bar">
                        <div class="meter-fill" style="width: <?php echo min(100, $result['current_pct']); ?>%; background: <?php echo $result['color']; ?>;"></div>
                        <div class="threshold-line" style="left: <?php echo $result['required_pct']; ?>%;"></div>
                    </div>
                </div>

                <!-- METRICS GRID -->
                <div class="metrics-grid">
                    <div class="m-box">
                        <span>Total Conducted</span>
                        <strong><?php echo $result['total_classes']; ?> Hrs</strong>
                    </div>
                    <div class="m-box">
                        <span>Attended</span>
                        <strong class="text-green"><?php echo $result['attended_count']; ?> Hrs</strong>
                    </div>
                    <div class="m-box">
                        <span>Absences</span>
                        <strong class="text-red"><?php echo $result['missed_count']; ?> Hrs</strong>
                    </div>
                    <div class="m-box">
                        <span>Required Minimum</span>
                        <strong><?php echo $result['required_pct']; ?>%</strong>
                    </div>
                </div>

                <!-- ACTIONABLE FORECAST -->
                <div class="projection-banner">
                    <?php if ($result['current_pct'] < $result['required_pct']): ?>
                        <div class="proj-box deficit">
                            <span class="proj-icon">🚨</span>
                            <div>
                                <h4>Attendance Deficit Recovery Action</h4>
                                <p>You must attend <strong><?php echo $result['classes_needed']; ?> consecutive upcoming classes</strong> without missing any to reach the minimum <strong><?php echo $result['required_pct']; ?>%</strong> threshold.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="proj-box safe">
                            <span class="proj-icon">🛡️</span>
                            <div>
                                <h4>Attendance Safety Cushion</h4>
                                <p>You can miss up to <strong><?php echo $result['skips_allowed']; ?> consecutive future classes</strong> while remaining above your <strong><?php echo $result['required_pct']; ?>%</strong> threshold.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer">
                    <span>Audit Tracking Reference: <code><?php echo $result['id']; ?></code></span>
                    <span><?php echo $result['timestamp']; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- AUDIT FORM -->
        <form action="index.php" method="POST" class="audit-form">
            <span class="form-section-title">Academic Audit Inputs</span>
            
            <div class="form-grid">
                <div class="field-group">
                    <label for="student_name">Student Full Name</label>
                    <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($_POST['student_name'] ?? 'Siddharth Rao'); ?>" required>
                </div>
                
                <div class="field-group">
                    <label for="student_id">Student Registration / Roll ID</label>
                    <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($_POST['student_id'] ?? 'REG-2026-8891'); ?>" required>
                </div>

                <div class="field-group span-2">
                    <label for="course_name">Subject / Course Module Title</label>
                    <input type="text" id="course_name" name="course_name" value="<?php echo htmlspecialchars($_POST['course_name'] ?? 'Advanced Data Structures & Algorithms'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="total_classes">Total Classes Conducted to Date</label>
                    <input type="number" id="total_classes" name="total_classes" min="1" max="500" value="<?php echo htmlspecialchars($_POST['total_classes'] ?? 45); ?>" required>
                </div>

                <div class="field-group">
                    <label for="attended_classes">Classes Attended by Student</label>
                    <input type="number" id="attended_classes" name="attended_classes" min="0" max="500" value="<?php echo htmlspecialchars($_POST['attended_classes'] ?? 31); ?>" required>
                </div>

                <div class="field-group span-2">
                    <label for="threshold_pct">Institutional Minimum Threshold</label>
                    <div class="radio-presets">
                        <label class="preset-option">
                            <input type="radio" name="threshold_pct" value="75" <?php echo (($_POST['threshold_pct'] ?? 75) == 75) ? 'checked' : ''; ?>>
                            <span>75% (Standard University)</span>
                        </label>
                        <label class="preset-option">
                            <input type="radio" name="threshold_pct" value="80" <?php echo (($_POST['threshold_pct'] ?? 75) == 80) ? 'checked' : ''; ?>>
                            <span>80% (Honors & Medical)</span>
                        </label>
                        <label class="preset-option">
                            <input type="radio" name="threshold_pct" value="85" <?php echo (($_POST['threshold_pct'] ?? 75) == 85) ? 'checked' : ''; ?>>
                            <span>85% (Strict Technical)</span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Run Attendance Audit & Projection</button>
        </form>

        <!-- AUDIT LOG HISTORY -->
        <?php if (!empty($_SESSION['attendance_audit_log'])): ?>
            <div class="history-panel">
                <div class="history-head">
                    <span>Recent Session Audits</span>
                    <a href="index.php?action=clear" class="clear-link">Clear Audit History</a>
                </div>
                <div class="history-grid">
                    <?php foreach ($_SESSION['attendance_audit_log'] as $log): ?>
                        <div class="history-card">
                            <div class="h-top">
                                <strong><?php echo htmlspecialchars($log['student_name']); ?></strong>
                                <span class="h-pct" style="color: <?php echo $log['color']; ?>;"><?php echo $log['current_pct']; ?>%</span>
                            </div>
                            <div class="h-sub"><?php echo htmlspecialchars($log['course_name']); ?> • Target: <?php echo $log['required_pct']; ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>