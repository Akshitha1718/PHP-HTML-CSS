<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Metrics & BMI Evaluator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="evaluator-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- BIOMETRIC REPORT VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Evaluation Complete</span>
                <h2>Biometric Health Summary</h2>
                <p>Profile ID: #BIO-<?php echo rand(10000, 99999); ?> | Health Metrics Report</p>
            </div>

            <?php
            $fullName  = htmlspecialchars(trim($_POST['fullName'] ?? 'Individual'));
            $age       = max(1, intval($_POST['age'] ?? 25));
            $gender    = $_POST['gender'] ?? 'male';
            $heightCm  = floatval($_POST['heightCm'] ?? 170);
            $weightKg  = floatval($_POST['weightKg'] ?? 70);
            $activity  = $_POST['activity'] ?? 'moderate';

            // BMI Calculation: Weight (kg) / (Height (m))^2
            $heightM = $heightCm / 100;
            $bmi = ($heightM > 0) ? ($weightKg / ($heightM * $heightM)) : 0;

            // BMI Classification & Dynamic Styling
            if ($bmi < 18.5) {
                $bmiCategory = "Underweight";
                $bmiClass    = "status-blue";
                $bmiAdvice   = "Consider increasing caloric intake with nutrient-dense foods and strength training.";
            } elseif ($bmi < 25.0) {
                $bmiCategory = "Normal Weight";
                $bmiClass    = "status-green";
                $bmiAdvice   = "Excellent baseline! Maintain balanced nutrition and consistent physical activity.";
            } elseif ($bmi < 30.0) {
                $bmiCategory = "Overweight";
                $bmiClass    = "status-amber";
                $bmiAdvice   = "Incorporate regular cardio, resistance training, and slight caloric deficits.";
            } else {
                $bmiCategory = "Obese";
                $bmiClass    = "status-red";
                $bmiAdvice   = "Consult a health professional for personalized nutritional and exercise guidance.";
            }

            // BMR Calculation (Mifflin-St Jeor Equation)
            if ($gender === 'female') {
                $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) - 161;
            } else {
                $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) + 5;
            }

            // TDEE Activity Multipliers
            $activityMultipliers = [
                'sedentary'  => 1.2,
                'light'      => 1.375,
                'moderate'   => 1.55,
                'active'     => 1.725,
                'extra'      => 1.9
            ];

            $multiplier = $activityMultipliers[$activity] ?? 1.55;
            $tdee = $bmr * $multiplier;
            ?>

            <div class="user-profile-banner">
                <div><span>Name:</span> <strong><?php echo $fullName; ?></strong></div>
                <div><span>Profile:</span> <strong><?php echo ucfirst($gender); ?>, <?php echo $age; ?> yrs</strong></div>
            </div>

            <div class="metrics-grid">
                <div class="metric-box">
                    <span>Body Mass Index</span>
                    <h3><?php echo number_format($bmi, 1); ?></h3>
                    <span class="status-tag <?php echo $bmiClass; ?>"><?php echo $bmiCategory; ?></span>
                </div>
                <div class="metric-box">
                    <span>Basal Metabolic Rate</span>
                    <h3><?php echo number_format($bmr, 0); ?> <small>kcal/day</small></h3>
                    <span class="sub-label">Base energy burn</span>
                </div>
            </div>

            <div class="tdee-card">
                <div class="tdee-header">
                    <span>Total Daily Energy Expenditure (TDEE)</span>
                    <strong><?php echo number_format($tdee, 0); ?> kcal/day</strong>
                </div>
                <p>Estimated calories required daily based on your <em><?php echo ucfirst($activity); ?></em> activity profile.</p>
            </div>

            <div class="biometric-details">
                <span class="section-label">Recorded Vital Inputs</span>
                <div class="detail-row">
                    <span>Height:</span>
                    <strong><?php echo number_format($heightCm, 1); ?> cm</strong>
                </div>
                <div class="detail-row">
                    <span>Weight:</span>
                    <strong><?php echo number_format($weightKg, 1); ?> kg</strong>
                </div>
                <div class="detail-row">
                    <span>Target Caloric Balance (Maintenance):</span>
                    <strong><?php echo number_format($tdee, 0); ?> kcal</strong>
                </div>
            </div>

            <div class="advice-box">
                <span>Recommendations</span>
                <p><?php echo $bmiAdvice; ?></p>
            </div>

            <a href="index.php" class="back-btn">&larr; Recalculate Health Metrics</a>

        <?php else: ?>
            <!-- BIOMETRIC INPUT FORM -->
            <div class="card-header">
                <span class="badge">Biometric Portal</span>
                <h2>Health Metrics & BMI Evaluator</h2>
                <p>Calculate your BMI, BMR, and daily energy intake requirements</p>
            </div>

            <form action="index.php" method="POST" class="evaluator-form">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" placeholder="e.g. Alex Morgan" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="age">Age (Years)</label>
                        <input type="number" id="age" name="age" min="1" max="120" value="28" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="gender">Biological Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="heightCm">Height (cm)</label>
                        <input type="number" id="heightCm" name="heightCm" step="0.1" min="50" max="250" placeholder="175" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="weightKg">Weight (kg)</label>
                        <input type="number" id="weightKg" name="weightKg" step="0.1" min="20" max="300" placeholder="72" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="activity">Daily Activity Level</label>
                    <select id="activity" name="activity" required>
                        <option value="sedentary">Sedentary (Little or no exercise)</option>
                        <option value="light">Lightly Active (Exercise 1-3 days/week)</option>
                        <option value="moderate" selected>Moderately Active (Exercise 3-5 days/week)</option>
                        <option value="active">Very Active (Hard exercise 6-7 days/week)</option>
                        <option value="extra">Extra Active (Physical job or hard training)</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Evaluate Biometric Profile</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>