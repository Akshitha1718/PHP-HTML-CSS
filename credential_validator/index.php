<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Credential Validator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="validator-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- VALIDATION REPORT VIEW -->
            <?php
            $username        = htmlspecialchars(trim($_POST['username'] ?? ''));
            $email           = htmlspecialchars(trim($_POST['email'] ?? ''));
            $password        = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';

            // Rule Verification Checks
            $checks = [
                'username_length' => [
                    'label' => 'Username length (5-20 characters)',
                    'passed' => strlen($username) >= 5 && strlen($username) <= 20
                ],
                'username_format' => [
                    'label' => 'Username alphanumeric or underscore',
                    'passed' => preg_match('/^[a-zA-Z0-9_]+$/', $username)
                ],
                'email_format' => [
                    'label' => 'Valid email syntax',
                    'passed' => filter_var($email, FILTER_VALIDATE_EMAIL) !== false
                ],
                'pass_length' => [
                    'label' => 'Password length (at least 8 characters)',
                    'passed' => strlen($password) >= 8
                ],
                'pass_upper' => [
                    'label' => 'Contains uppercase letter (A-Z)',
                    'passed' => preg_match('/[A-Z]/', $password)
                ],
                'pass_lower' => [
                    'label' => 'Contains lowercase letter (a-z)',
                    'passed' => preg_match('/[a-z]/', $password)
                ],
                'pass_number' => [
                    'label' => 'Contains numerical digit (0-9)',
                    'passed' => preg_match('/[0-9]/', $password)
                ],
                'pass_special' => [
                    'label' => 'Contains special character (@, #, $, etc.)',
                    'passed' => preg_match('/[\W_]/', $password)
                ],
                'pass_match' => [
                    'label' => 'Password and Confirmation match',
                    'passed' => ($password !== '') && ($password === $confirmPassword)
                ]
            ];

            // Overall Compliance Score
            $passedCount = 0;
            foreach ($checks as $check) {
                if ($check['passed']) $passedCount++;
            }
            $totalChecks = count($checks);
            $scorePercent = round(($passedCount / $totalChecks) * 100);
            $allPassed = ($passedCount === $totalChecks);
            ?>

            <div class="card-header">
                <span class="badge <?php echo $allPassed ? 'badge-success' : 'badge-alert'; ?>">
                    <?php echo $allPassed ? 'Account Approved' : 'Validation Failed'; ?>
                </span>
                <h2>Security Compliance</h2>
                <p>User Ref: @<?php echo $username ?: 'anonymous'; ?> | Score: <?php echo $scorePercent; ?>%</p>
            </div>

            <div class="user-meta-banner">
                <div><span>Username:</span> <strong><?php echo $username ?: 'N/A'; ?></strong></div>
                <div><span>Email:</span> <strong><?php echo $email ?: 'N/A'; ?></strong></div>
            </div>

            <div class="checks-list">
                <?php foreach ($checks as $key => $check): ?>
                    <div class="check-item <?php echo $check['passed'] ? 'check-pass' : 'check-fail'; ?>">
                        <span class="status-icon"><?php echo $check['passed'] ? '&#10003;' : '&#10007;'; ?></span>
                        <span class="check-label"><?php echo $check['label']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="score-bar-container">
                <div class="score-bar-fill" style="width: <?php echo $scorePercent; ?>%;"></div>
            </div>

            <a href="index.php" class="back-btn">&larr; Return to Registration</a>

        <?php else: ?>
            <!-- CREDENTIAL INPUT FORM -->
            <div class="card-header">
                <span class="badge">AuthShield v11.0</span>
                <h2>Credential Validator</h2>
                <p>Test user account inputs against enterprise password policy rules</p>
            </div>

            <form action="index.php" method="POST" class="validator-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="e.g. alex_vance" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="e.g. alex.vance@domain.com" required>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Run Compliance Check</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>