<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corporate Email Alias Generator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="alias-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- ALIAS GENERATED VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Generation Complete</span>
                <h2>Corporate Aliases</h2>
                <p>Configured routing identities for employee profile</p>
            </div>

            <?php
            // Sanitize inputs
            $rawFirstName = trim($_POST['firstName'] ?? '');
            $rawLastName  = trim($_POST['lastName'] ?? '');
            $rawDept      = trim($_POST['department'] ?? '');
            $rawDomain    = trim($_POST['domain'] ?? '');

            $firstName  = strtolower(preg_replace('/[^a-zA-Z]/', '', $rawFirstName));
            $lastName   = strtolower(preg_replace('/[^a-zA-Z]/', '', $rawLastName));
            $dept       = strtolower(preg_replace('/[^a-zA-Z]/', '', $rawDept));
            $domain     = strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $rawDomain));

            $fInitial   = substr($firstName, 0, 1);
            $lInitial   = substr($lastName, 0, 1);

            // Generate variations
            $primaryEmail   = "{$firstName}.{$lastName}@{$domain}";
            $shortEmail     = "{$fInitial}{$lastName}@{$domain}";
            $deptEmail      = "{$firstName}.{$dept}@{$domain}";
            $execEmail      = "{$fInitial}{$lInitial}-exec@{$domain}";
            $securityAlias  = "sec-{$lastName}{$fInitial}@{$domain}";
            ?>

            <div class="employee-summary">
                <span>Employee: <strong><?php echo ucfirst($rawFirstName) . " " . ucfirst($rawLastName); ?></strong></span>
                <span>Department: <strong><?php echo strtoupper($rawDept); ?></strong></span>
            </div>

            <div class="alias-list">
                <div class="alias-item">
                    <span class="alias-type">Primary Standard</span>
                    <code><?php echo $primaryEmail; ?></code>
                </div>
                <div class="alias-item">
                    <span class="alias-type">Short Handle</span>
                    <code><?php echo $shortEmail; ?></code>
                </div>
                <div class="alias-item">
                    <span class="alias-type">Department Routing</span>
                    <code><?php echo $deptEmail; ?></code>
                </div>
                <div class="alias-item">
                    <span class="alias-type">Executive Tag</span>
                    <code><?php echo $execEmail; ?></code>
                </div>
                <div class="alias-item">
                    <span class="alias-type">Security Token ID</span>
                    <code><?php echo $securityAlias; ?></code>
                </div>
            </div>

            <a href="index.php" class="back-btn">&larr; Generate New Employee Aliases</a>

        <?php else: ?>
            <!-- INPUT FORM VIEW -->
            <div class="card-header">
                <span class="badge">Identity Provisioning</span>
                <h2>Email Alias Generator</h2>
                <p>Generate standardized enterprise email handles and routing aliases</p>
            </div>

            <form action="index.php" method="POST" class="alias-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" placeholder="e.g. Samantha" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" placeholder="e.g. Reed" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department Code</label>
                        <input type="text" id="department" name="department" placeholder="e.g. Dev, HR, Sales" required>
                    </div>
                    <div class="form-group">
                        <label for="domain">Corporate Domain</label>
                        <input type="text" id="domain" name="domain" placeholder="e.g. enterprise.com" required>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Generate Corporate Aliases</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>