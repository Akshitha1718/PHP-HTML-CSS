<?php
session_start();

if (!isset($_SESSION['appraisal_records'])) {
    $_SESSION['appraisal_records'] = [];
}

$activeEvaluation = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empName      = trim(htmlspecialchars($_POST['emp_name'] ?? 'Elena Rostova'));
    $empId        = trim(htmlspecialchars($_POST['emp_id'] ?? 'EMP-9021'));
    $department   = trim(htmlspecialchars($_POST['department'] ?? 'Product Engineering'));
    $reviewer     = trim(htmlspecialchars($_POST['reviewer'] ?? 'Marcus Vance'));
    $reviewPeriod = trim(htmlspecialchars($_POST['review_period'] ?? '2025 Annual Review'));
    
    // Competency Scores (1 to 5)
    $qTechnical     = max(1, min(5, intval($_POST['score_technical'] ?? 5)));
    $qCollaboration = max(1, min(5, intval($_POST['score_collaboration'] ?? 4)));
    $qDelivery      = max(1, min(5, intval($_POST['score_delivery'] ?? 5)));
    $qLeadership    = max(1, min(5, intval($_POST['score_leadership'] ?? 4)));
    $qInnovation    = max(1, min(5, intval($_POST['score_innovation'] ?? 5)));

    $goalsAchieved  = max(0, min(100, intval($_POST['goals_achieved'] ?? 92)));
    $feedback       = trim(htmlspecialchars($_POST['feedback'] ?? 'Elena consistently delivers top-tier distributed architecture code, demonstrates strong technical leadership, and mentors junior engineers effectively.'));

    // Weighted Score Calculation (Max 100)
    // Competency Average (70% weight) + Goals Achieved (30% weight)
    $competencyAvg = ($qTechnical + $qCollaboration + $qDelivery + $qLeadership + $qInnovation) / 5;
    $competencyScorePercent = ($competencyAvg / 5) * 100;
    
    $overallScore = round(($competencyScorePercent * 0.7) + ($goalsAchieved * 0.3), 1);

    // Appraisal Tier & Bonus Multiplier Determination
    if ($overallScore >= 90) {
        $tierTitle = 'Tier 1 - Outstanding / Exceeds Expectations';
        $tierBadge = '🌟 OUTSTANDING';
        $tierColor = '#10b981'; // Vibrant Emerald
        $bonusPercent = '15% - 20% Base Salary';
        $promotionStatus = 'Highly Recommended for Promotion';
    } elseif ($overallScore >= 75) {
        $tierTitle = 'Tier 2 - Strong / Meets All Expectations';
        $tierBadge = '🔹 STRONG PERFORMER';
        $tierColor = '#00f2fe'; // Neon Cyan
        $bonusPercent = '8% - 12% Base Salary';
        $promotionStatus = 'Eligible for Advancement Next Cycle';
    } elseif ($overallScore >= 60) {
        $tierTitle = 'Tier 3 - Satisfactory / Developing';
        $tierBadge = '🟡 SATISFACTORY';
        $tierColor = '#ffb800'; // Amber Gold
        $bonusPercent = '3% - 5% Base Salary';
        $promotionStatus = 'Maintain Current Grade';
    } else {
        $tierTitle = 'Tier 4 - Action Plan Required';
        $tierBadge = '🔴 NEEDS IMPROVEMENT';
        $tierColor = '#ff007f'; // Vivid Pink
        $bonusPercent = '0% (Ineligible)';
        $promotionStatus = 'Performance Improvement Plan (PIP)';
    }

    $activeEvaluation = [
        'evaluation_id'    => 'APP-' . strtoupper(substr(md5($empId . time()), 0, 8)),
        'emp_name'         => $empName,
        'emp_id'           => $empId,
        'department'       => $department,
        'reviewer'         => $reviewer,
        'review_period'    => $reviewPeriod,
        'scores'           => [
            'Technical Proficiency' => $qTechnical,
            'Team Collaboration'    => $qCollaboration,
            'Project Delivery'      => $qDelivery,
            'Leadership & Drive'    => $qLeadership,
            'Innovation & Quality'  => $qInnovation
        ],
        'goals_achieved'   => $goalsAchieved,
        'competency_avg'   => $competencyAvg,
        'overall_score'    => $overallScore,
        'tier_title'       => $tierTitle,
        'tier_badge'       => $tierBadge,
        'tier_color'       => $tierColor,
        'bonus_percent'    => $bonusPercent,
        'promotion_status' => $promotionStatus,
        'feedback'         => $feedback,
        'evaluated_at'     => date('H:i | M d, Y')
    ];

    // Store in Session Audit Log (Keep recent 5)
    array_unshift($_SESSION['appraisal_records'], $activeEvaluation);
    $_SESSION['appraisal_records'] = array_slice($_SESSION['appraisal_records'], 0, 5);
}

// Clear Session Log
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['appraisal_records'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Appraisal Evaluation System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="portal-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <span class="header-pill">Task 25 • HR Performance Analytics</span>
            <h1>Employee Appraisal Evaluation System</h1>
            <p>Compute performance scores, evaluate competency indicators, assign tier ratings, and compute merit increases.</p>
        </header>

        <!-- APPRAISAL RESULT CARD -->
        <?php if ($activeEvaluation): ?>
            <div class="result-card" style="border-top-color: <?php echo $activeEvaluation['tier_color']; ?>;">
                <div class="card-top">
                    <div>
                        <span class="eval-id">EVALUATION ID: <?php echo $activeEvaluation['evaluation_id']; ?></span>
                        <h2><?php echo htmlspecialchars($activeEvaluation['emp_name']); ?></h2>
                        <p class="emp-meta"><?php echo htmlspecialchars($activeEvaluation['emp_id']); ?> • <?php echo htmlspecialchars($activeEvaluation['department']); ?></p>
                    </div>
                    <span class="tier-pill" style="background: <?php echo $activeEvaluation['tier_color']; ?>22; color: <?php echo $activeEvaluation['tier_color']; ?>; border-color: <?php echo $activeEvaluation['tier_color']; ?>88;">
                        <?php echo $activeEvaluation['tier_badge']; ?>
                    </span>
                </div>

                <!-- OVERALL SCORE DISPLAY -->
                <div class="score-banner">
                    <div class="score-circle" style="border-color: <?php echo $activeEvaluation['tier_color']; ?>;">
                        <span class="score-num"><?php echo $activeEvaluation['overall_score']; ?></span>
                        <span class="score-max">/ 100</span>
                    </div>
                    <div class="score-details">
                        <h3 style="color: <?php echo $activeEvaluation['tier_color']; ?>;"><?php echo $activeEvaluation['tier_title']; ?></h3>
                        <div class="metric-row">
                            <span>Target KPI Achievement: <strong><?php echo $activeEvaluation['goals_achieved']; ?>%</strong></span>
                            <span>Competency Avg: <strong><?php echo $activeEvaluation['competency_avg']; ?> / 5.0</strong></span>
                        </div>
                        <div class="metric-row">
                            <span>Merit Bonus Tier: <strong><?php echo $activeEvaluation['bonus_percent']; ?></strong></span>
                            <span>Advancement Status: <strong><?php echo $activeEvaluation['promotion_status']; ?></strong></span>
                        </div>
                    </div>
                </div>

                <!-- COMPETENCY BAR BREAKDOWN -->
                <div class="competencies-grid">
                    <h4>Core Competency Ratings</h4>
                    <div class="bars-container">
                        <?php foreach ($activeEvaluation['scores'] as $compName => $compScore): ?>
                            <div class="comp-item">
                                <div class="comp-label">
                                    <span><?php echo $compName; ?></span>
                                    <strong><?php echo $compScore; ?> / 5</strong>
                                </div>
                                <div class="progress-track">
                                    <div class="progress-fill" style="width: <?php echo ($compScore / 5) * 100; ?>%; background: <?php echo $activeEvaluation['tier_color']; ?>;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- FEEDBACK -->
                <div class="feedback-box">
                    <h4>Evaluator Executive Remarks</h4>
                    <p>"<?php echo htmlspecialchars($activeEvaluation['feedback']); ?>"</p>
                    <span class="reviewer-tag">Evaluated by: <?php echo htmlspecialchars($activeEvaluation['reviewer']); ?> (<?php echo $activeEvaluation['review_period']; ?>)</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- APPRAISAL INPUT FORM -->
        <form action="index.php" method="POST" class="appraisal-form">
            <span class="form-title">Submit Performance Review Data</span>
            
            <div class="form-grid">
                <div class="field-group">
                    <label for="emp_name">Employee Name</label>
                    <input type="text" id="emp_name" name="emp_name" value="<?php echo htmlspecialchars($_POST['emp_name'] ?? 'Elena Rostova'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="emp_id">Employee ID</label>
                    <input type="text" id="emp_id" name="emp_id" value="<?php echo htmlspecialchars($_POST['emp_id'] ?? 'EMP-9021'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($_POST['department'] ?? 'Product Engineering'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="reviewer">Reviewer / Manager</label>
                    <input type="text" id="reviewer" name="reviewer" value="<?php echo htmlspecialchars($_POST['reviewer'] ?? 'Marcus Vance'); ?>" required>
                </div>

                <div class="field-group span-2">
                    <label for="review_period">Review Period Cycle</label>
                    <input type="text" id="review_period" name="review_period" value="<?php echo htmlspecialchars($_POST['review_period'] ?? '2025 Annual Performance Cycle'); ?>" required>
                </div>

                <div class="field-group span-2 ratings-container">
                    <label>Core Competencies Assessment (Scale 1 to 5)</label>
                    <div class="ratings-grid">
                        <div class="rating-box">
                            <span>Technical Proficiency</span>
                            <input type="number" name="score_technical" min="1" max="5" value="<?php echo $_POST['score_technical'] ?? 5; ?>" required>
                        </div>
                        <div class="rating-box">
                            <span>Team Collaboration</span>
                            <input type="number" name="score_collaboration" min="1" max="5" value="<?php echo $_POST['score_collaboration'] ?? 4; ?>" required>
                        </div>
                        <div class="rating-box">
                            <span>Project Delivery</span>
                            <input type="number" name="score_delivery" min="1" max="5" value="<?php echo $_POST['score_delivery'] ?? 5; ?>" required>
                        </div>
                        <div class="rating-box">
                            <span>Leadership & Drive</span>
                            <input type="number" name="score_leadership" min="1" max="5" value="<?php echo $_POST['score_leadership'] ?? 4; ?>" required>
                        </div>
                        <div class="rating-box">
                            <span>Innovation & Quality</span>
                            <input type="number" name="score_innovation" min="1" max="5" value="<?php echo $_POST['score_innovation'] ?? 5; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="field-group span-2">
                    <label for="goals_achieved">Target Goals / KPI Achievement Rate (%)</label>
                    <input type="number" id="goals_achieved" name="goals_achieved" min="0" max="100" value="<?php echo $_POST['goals_achieved'] ?? 92; ?>" required>
                </div>

                <div class="field-group span-2">
                    <label for="feedback">Executive Summary & Feedback</label>
                    <textarea id="feedback" name="feedback" rows="3" required><?php echo htmlspecialchars($_POST['feedback'] ?? 'Elena consistently delivers top-tier distributed architecture code, demonstrates strong technical leadership, and mentors junior engineers effectively.'); ?></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">Calculate Score & Save Appraisal</button>
        </form>

        <!-- SESSION HISTORY -->
        <?php if (!empty($_SESSION['appraisal_records'])): ?>
            <div class="history-panel">
                <div class="history-head">
                    <span>Recent Appraisal Evaluations</span>
                    <a href="index.php?action=clear" class="clear-btn">Clear Log</a>
                </div>
                <div class="history-list">
                    <?php foreach ($_SESSION['appraisal_records'] as $rec): ?>
                        <div class="history-item">
                            <div>
                                <strong><?php echo htmlspecialchars($rec['emp_name']); ?> (<?php echo htmlspecialchars($rec['emp_id']); ?>)</strong>
                                <span class="sub"><?php echo htmlspecialchars($rec['department']); ?> • Overall: <?php echo $rec['overall_score']; ?>/100</span>
                            </div>
                            <span class="mini-badge" style="color: <?php echo $rec['tier_color']; ?>; border-color: <?php echo $rec['tier_color']; ?>66;">
                                <?php echo $rec['tier_badge']; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>