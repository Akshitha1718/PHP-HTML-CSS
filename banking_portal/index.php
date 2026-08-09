<?php
session_start();

// Mock User Vault Data
define('MOCK_USER', 'alex_banker');
define('MOCK_ACCOUNT', '8849002134');
define('MOCK_PASS_HASH', password_hash('VaultSecret2026!', PASSWORD_DEFAULT));
define('MOCK_PIN', '9042');

$authError = '';
$isAuthenticated = false;

// Track failed attempts in session
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}

// Process Authentication Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId    = htmlspecialchars(trim($_POST['userId'] ?? ''));
    $password  = $_POST['password'] ?? '';
    $secPin    = htmlspecialchars(trim($_POST['secPin'] ?? ''));

    // Check rate limiting mock
    if ($_SESSION['failed_attempts'] >= 3) {
        $authError = "Account temporarily locked due to 3 failed attempts. Please contact security.";
    } else {
        // Validation logic
        $isUserValid = ($userId === MOCK_USER || $userId === MOCK_ACCOUNT);
        $isPassValid = password_verify($password, MOCK_PASS_HASH);
        $isPinValid  = ($secPin === MOCK_PIN);

        if ($isUserValid && $isPassValid && $isPinValid) {
            $isAuthenticated = true;
            $_SESSION['failed_attempts'] = 0;
            $_SESSION['user_authenticated'] = true;
            $_SESSION['last_login'] = date('Y-m-d H:i:s T');
        } else {
            $_SESSION['failed_attempts']++;
            $remaining = 3 - $_SESSION['failed_attempts'];
            $authError = "Invalid security credentials. Remaining attempts: {$remaining}";
        }
    }
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apex Vault - Secure Banking Sign-In</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="portal-wrapper">
        <?php if ($isAuthenticated): ?>
            <!-- SECURE BANKING DASHBOARD VIEW -->
            <div class="vault-card dashboard-card">
                <div class="card-header">
                    <div class="security-indicator">
                        <span class="shield-dot"></span> 256-Bit SSL Encrypted Session
                    </div>
                    <h2>Apex Capital Vault</h2>
                    <p class="subtitle">Welcome back, Alex Morgan</p>
                </div>

                <div class="account-overview">
                    <div class="account-box">
                        <span class="acc-label">Primary Wealth Account</span>
                        <h3 class="acc-number">•••• •••• 2134</h3>
                        <div class="balance-display">
                            <span class="currency">$</span>
                            <span class="amount">142,850.45</span>
                            <span class="denom">USD</span>
                        </div>
                    </div>
                    <div class="account-box glow-amber">
                        <span class="acc-label">Active Investment Portfolio</span>
                        <h3 class="acc-number">•••• •••• 9801</h3>
                        <div class="balance-display">
                            <span class="currency">$</span>
                            <span class="amount">68,410.12</span>
                            <span class="denom">USD</span>
                        </div>
                    </div>
                </div>

                <div class="audit-section">
                    <span class="section-title">Recent Security Activity</span>
                    <ul class="audit-list">
                        <li>
                            <span class="audit-event">Session Initiated via 2FA Token</span>
                            <span class="audit-time"><?php echo $_SESSION['last_login']; ?></span>
                        </li>
                        <li>
                            <span class="audit-event">Biometric Auth Synchronized</span>
                            <span class="audit-time">Yesterday, 18:42 UTC</span>
                        </li>
                        <li>
                            <span class="audit-event">Wire Transfer Approved ($2,500.00)</span>
                            <span class="audit-time">05 Aug 2026, 11:15 UTC</span>
                        </li>
                    </ul>
                </div>

                <div class="card-footer">
                    <a href="index.php?action=logout" class="btn-logout">Terminate Secure Session</a>
                </div>
            </div>

        <?php else: ?>
            <!-- AUTHENTICATION FORM VIEW -->
            <div class="vault-card login-card">
                <div class="card-header">
                    <div class="portal-badge">Apex Gateway</div>
                    <h2>Secure Banking Sign-In</h2>
                    <p>Enter your authorization credentials to access your vault</p>
                </div>

                <?php if (!empty($authError)): ?>
                    <div class="alert-box error">
                        <span class="alert-icon">⚠️</span>
                        <span><?php echo $authError; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Demo Hint Box -->
                <div class="demo-hint">
                    <strong>Demo Credentials:</strong><br>
                    ID/Account: <code>alex_banker</code> or <code>8849002134</code><br>
                    Password: <code>VaultSecret2026!</code> | Security PIN: <code>9042</code>
                </div>

                <form action="index.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="userId">Username or Account Number</label>
                        <input type="text" id="userId" name="userId" placeholder="e.g. alex_banker" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="password">Account Vault Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••••••" required>
                    </div>

                    <div class="form-group">
                        <label for="secPin">4-Digit Security PIN / 2FA</label>
                        <input type="password" id="secPin" name="secPin" maxlength="4" pattern="\d{4}" placeholder="e.g. 9042" required autocomplete="off">
                    </div>

                    <button type="submit" class="btn-submit" <?php echo ($_SESSION['failed_attempts'] >= 3) ? 'disabled' : ''; ?>>
                        Authorize & Access Vault
                    </button>
                </form>

                <div class="login-footer">
                    <span>Protected by Hardware Token & Multi-Factor Guard</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>