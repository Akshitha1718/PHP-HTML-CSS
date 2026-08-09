<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Merit Admission System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admission-wrapper">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- RECEIPT VIEW (Triggers after submitting) -->
            <div class="portal-header">
                <span class="tagline">Official Verification</span>
                <h1>Admission Receipt</h1>
                <p>Application Evaluation Summary</p>
            </div>

            <?php
            $fullName = htmlspecialchars(trim($_POST['fullName'] ?? ''));
            $email    = htmlspecialchars(trim($_POST['email'] ?? ''));
            $age      = intval($_POST['age'] ?? 0);
            $score    = floatval($_POST['score'] ?? 0);
            $program  = htmlspecialchars(trim($_POST['program'] ?? ''));

            $isAgeEligible = ($age >= 17);
            
            if (!$isAgeEligible) {
                $statusText = "Application Ineligible";
                $badgeClass = "status-rejected";
                $scholarship = "N/A (Age below minimum 17 requirement)";
            } else {
                if ($score >= 85) {
                    $statusText = "Provisionally Admitted (100% Merit Scholarship)";
                    $badgeClass = "status-approved";
                    $scholarship = "Full Tuition Waiver Granted";
                } elseif ($score >= 75) {
                    $statusText = "Provisionally Admitted (50% Merit Scholarship)";
                    $badgeClass = "status-approved";
                    $scholarship = "Half Tuition Waiver Granted";
                } elseif ($score >= 60) {
                    $statusText = "Provisionally Admitted (Standard)";
                    $badgeClass = "status-approved";
                    $scholarship = "Standard Fee Tier";
                } else {
                    $statusText = "Application Under Review";
                    $badgeClass = "status-rejected";
                    $scholarship = "Does not meet minimum 60% threshold";
                }
            }

            echo "<div style='text-align: center;'>";
            echo "<span class='status-badge {$badgeClass}'>{$statusText}</span>";
            echo "</div>";

            echo "<table class='receipt-table'>";
            echo "<tr><td>Applicant Name</td><td>{$fullName}</td></tr>";
            echo "<tr><td>Contact Email</td><td>{$email}</td></tr>";
            echo "<tr><td>Applicant Age</td><td>{$age} Years</td></tr>";
            echo "<tr><td>High School Score</td><td>{$score}%</td></tr>";
            echo "<tr><td>Intended Program</td><td>{$program}</td></tr>";
            echo "<tr><td>Scholarship Tier</td><td>{$scholarship}</td></tr>";
            echo "</table>";
            ?>

            <a href="index.php" class="back-link">&larr; Back to Admission Form</a>

        <?php else: ?>
            <!-- FORM VIEW (Initial Page Load) -->
            <div class="portal-header">
                <span class="tagline">Fall 2026 Admissions</span>
                <h1>University Merit Portal</h1>
                <p>Submit your academic credentials for merit scholarship evaluation</p>
            </div>

            <form action="index.php" method="POST" class="admission-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" placeholder="e.g. Sophia Anderson" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="sophia@example.com" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" min="15" max="60" placeholder="Minimum 17 years" required>
                    </div>
                    <div class="form-group">
                        <label for="score">High School Percentage (%)</label>
                        <input type="number" id="score" name="score" min="0" max="100" step="0.1" placeholder="e.g. 88.5" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="program">Intended Major</label>
                    <select id="program" name="program" required>
                        <option value="" disabled selected>Choose your major stream</option>
                        <option value="B.S. Artificial Intelligence">B.S. Artificial Intelligence</option>
                        <option value="B.S. Software Engineering">B.S. Software Engineering</option>
                        <option value="B.S. Data Science">B.S. Data Science</option>
                        <option value="B.B.A. Business Analytics">B.B.A. Business Analytics</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Evaluate & Submit Application</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>