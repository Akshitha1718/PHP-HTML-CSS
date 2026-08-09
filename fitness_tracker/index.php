<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Tracker & Calorie Burn Calculator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="fit-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- WORKOUT REPORT VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Session Logged</span>
                <h2>Workout Summary</h2>
                <p>Track Ref: #FIT-<?php echo strtoupper(substr(md5(uniqid()), 0, 6)); ?> | <?php echo date("M d, Y"); ?></p>
            </div>

            <?php
            $athleteName = htmlspecialchars(trim($_POST['athleteName'] ?? 'Athlete'));
            $weight      = floatval($_POST['weight'] ?? 70);
            $activity    = htmlspecialchars(trim($_POST['activity'] ?? 'Running'));
            $duration    = intval($_POST['duration'] ?? 30);
            $intensity   = htmlspecialchars(trim($_POST['intensity'] ?? 'Moderate'));

            // Base MET (Metabolic Equivalent of Task) Mapping
            $metTable = [
                'Running'       => ['Low' => 7.0, 'Moderate' => 9.8, 'High' => 11.5, 'Extreme' => 14.0],
                'Cycling'       => ['Low' => 5.5, 'Moderate' => 7.5, 'High' => 10.0, 'Extreme' => 12.0],
                'Swimming'      => ['Low' => 6.0, 'Moderate' => 8.0, 'High' => 10.0, 'Extreme' => 11.0],
                'HIIT'          => ['Low' => 6.5, 'Moderate' => 8.5, 'High' => 11.0, 'Extreme' => 13.0],
                'Weightlifting' => ['Low' => 3.5, 'Moderate' => 5.0, 'High' => 6.0, 'Extreme' => 8.0]
            ];

            $met = $metTable[$activity][$intensity] ?? 7.0;

            // Calculations
            // Calories = MET * Weight (kg) * Duration (hours)
            $caloriesBurned = round($met * $weight * ($duration / 60));
            $hydrationMl    = round($duration * 12.5); // ~12.5ml per minute of exercise
            $burnRate       = round($caloriesBurned / ($duration / 60));

            // Intensity Badge Mapping
            $intensityBadges = [
                'Low' => 'badge-low',
                'Moderate' => 'badge-mod',
                'High' => 'badge-high',
                'Extreme' => 'badge-extreme'
            ];
            ?>

            <div class="athlete-banner">
                <div><span>Athlete:</span> <strong><?php echo $athleteName; ?></strong></div>
                <div><span>Body Weight:</span> <strong><?php echo $weight; ?> kg</strong></div>
            </div>

            <div class="metrics-grid">
                <div class="metric-box">
                    <span>Calories Burned</span>
                    <h3 class="highlight-fire"><?php echo $caloriesBurned; ?> <small>kcal</small></h3>
                </div>
                <div class="metric-box">
                    <span>Hydration Target</span>
                    <h3 class="highlight-water"><?php echo $hydrationMl; ?> <small>ml</small></h3>
                </div>
                <div class="metric-box">
                    <span>Burn Rate</span>
                    <h3><?php echo $burnRate; ?> <small>kcal/hr</small></h3>
                </div>
            </div>

            <div class="workout-details">
                <div class="detail-row">
                    <span>Activity Type:</span>
                    <strong><?php echo $activity; ?></strong>
                </div>
                <div class="detail-row">
                    <span>Duration:</span>
                    <strong><?php echo $duration; ?> Minutes</strong>
                </div>
                <div class="detail-row">
                    <span>Exertion Level:</span>
                    <span class="intensity-tag <?php echo $intensityBadges[$intensity]; ?>"><?php echo $intensity; ?></span>
                </div>
            </div>

            <a href="index.php" class="back-btn">&larr; Log Another Workout</a>

        <?php else: ?>
            <!-- INPUT FORM VIEW -->
            <div class="card-header">
                <span class="badge">Performance Lab</span>
                <h2>Workout Logger</h2>
                <p>Calculate caloric burn and hydration needs for your session</p>
            </div>

            <form action="index.php" method="POST" class="fit-form">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="athleteName">Athlete Name</label>
                        <input type="text" id="athleteName" name="athleteName" placeholder="e.g. Marcus Vance" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" min="30" max="250" step="0.5" placeholder="72.5" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="activity">Activity Type</label>
                        <select id="activity" name="activity" required>
                            <option value="Running">Running</option>
                            <option value="Cycling">Cycling</option>
                            <option value="Swimming">Swimming</option>
                            <option value="HIIT">HIIT Workout</option>
                            <option value="Weightlifting">Weightlifting</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="duration">Duration (Mins)</label>
                        <input type="number" id="duration" name="duration" min="5" max="300" placeholder="45" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="intensity">Exertion / Intensity Level</label>
                    <select id="intensity" name="intensity" required>
                        <option value="Low">Low Intensity (Light Effort)</option>
                        <option value="Moderate" selected>Moderate Intensity (Paced Effort)</option>
                        <option value="High">High Intensity (Vigorous Effort)</option>
                        <option value="Extreme">Extreme Intensity (Max Performance)</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Calculate Metrics & Log Session</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>