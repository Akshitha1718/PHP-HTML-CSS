<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cellular Tariff Billing Engine</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="billing-container">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- ITEMIZED CELLULAR INVOICE VIEW -->
            <div class="billing-card invoice-card">
                <div class="card-header">
                    <span class="badge badge-neon">Invoice Statement</span>
                    <h2>Cellular Billing Summary</h2>
                    <p>Account Ref: #CEL-<?php echo rand(100000, 999999); ?> | Statement Cycle: <?php echo date('M Y'); ?></p>
                </div>

                <?php
                $subscriberName = htmlspecialchars(trim($_POST['subscriberName'] ?? 'Subscriber'));
                $phoneNumber    = htmlspecialchars(trim($_POST['phoneNumber'] ?? '+1 (555) 019-2834'));
                $planType       = $_POST['planType'] ?? 'pro';
                $voiceMinutes   = max(0, intval($_POST['voiceMinutes'] ?? 0));
                $smsCount       = max(0, intval($_POST['smsCount'] ?? 0));
                $dataGb         = max(0, floatval($_POST['dataGb'] ?? 0));
                $roamingAddon   = isset($_POST['roamingAddon']);
                $speedPassAddon = isset($_POST['speedPassAddon']);

                // Tariff Configurations
                $plans = [
                    'starter' => [
                        'name' => 'Starter Connect',
                        'base_fee' => 19.99,
                        'inc_min' => 300,
                        'inc_sms' => 500,
                        'inc_gb'  => 5.0,
                    ],
                    'pro' => [
                        'name' => 'Pro Unlimited Voice',
                        'base_fee' => 49.99,
                        'inc_min' => 1000,
                        'inc_sms' => 2000,
                        'inc_gb'  => 25.0,
                    ],
                    'ultra' => [
                        'name' => 'Executive Ultra 5G',
                        'base_fee' => 79.99,
                        'inc_min' => 99999, // Virtually unlimited
                        'inc_sms' => 99999,
                        'inc_gb'  => 100.0,
                    ]
                ];

                $selectedPlan = $plans[$planType] ?? $plans['pro'];

                // Rate Charges for Excess Usage
                $rateMinOver = 0.05; // $0.05 per extra minute
                $rateSmsOver = 0.02; // $0.02 per extra SMS
                $rateGbOver  = 10.00; // $10.00 per extra GB

                // Calculate Overage Units & Costs
                $excessMin  = max(0, $voiceMinutes - $selectedPlan['inc_min']);
                $excessSms  = max(0, $smsCount - $selectedPlan['inc_sms']);
                $excessGb   = max(0.0, $dataGb - $selectedPlan['inc_gb']);

                $chargeExcessMin = $excessMin * $rateMinOver;
                $chargeExcessSms = $excessSms * $rateSmsOver;
                $chargeExcessGb  = $excessGb * $rateGbOver;
                $totalOverage    = $chargeExcessMin + $chargeExcessSms + $chargeExcessGb;

                // Add-ons
                $chargeRoaming   = $roamingAddon ? 15.00 : 0.00;
                $chargeSpeedPass = $speedPassAddon ? 9.99 : 0.00;
                $totalAddons     = $chargeRoaming + $chargeSpeedPass;

                // Subtotal & Taxes
                $subtotal       = $selectedPlan['base_fee'] + $totalOverage + $totalAddons;
                $regulatoryFee  = 2.50; // Flat regulatory compliance fee
                $telecomTax     = $subtotal * 0.125; // 12.5% Tax
                $totalAmountDue = $subtotal + $regulatoryFee + $telecomTax;
                ?>

                <div class="subscriber-info">
                    <div>
                        <span class="info-label">Subscriber</span>
                        <strong><?php echo $subscriberName; ?></strong>
                    </div>
                    <div>
                        <span class="info-label">Mobile Number</span>
                        <strong><?php echo $phoneNumber; ?></strong>
                    </div>
                    <div>
                        <span class="info-label">Subscription Tier</span>
                        <strong class="text-neon"><?php echo $selectedPlan['name']; ?></strong>
                    </div>
                </div>

                <div class="statement-breakdown">
                    <span class="section-title">Itemized Billing Breakdown</span>

                    <div class="line-item">
                        <span>Base Plan Subscription (<?php echo $selectedPlan['name']; ?>)</span>
                        <strong>$<?php echo number_format($selectedPlan['base_fee'], 2); ?></strong>
                    </div>

                    <!-- Usage Details -->
                    <div class="usage-summary-box">
                        <div class="usage-col">
                            <span>Voice Minutes</span>
                            <small><?php echo $voiceMinutes; ?> / <?php echo ($selectedPlan['inc_min'] > 10000) ? '∞' : $selectedPlan['inc_min']; ?> min</small>
                            <?php if ($excessMin > 0): ?>
                                <span class="sur-text">+<?php echo $excessMin; ?> min excess ($<?php echo number_format($chargeExcessMin, 2); ?>)</span>
                            <?php else: ?>
                                <span class="ok-text">Within Allowance</span>
                            <?php endif; ?>
                        </div>

                        <div class="usage-col">
                            <span>SMS Messages</span>
                            <small><?php echo $smsCount; ?> / <?php echo ($selectedPlan['inc_sms'] > 10000) ? '∞' : $selectedPlan['inc_sms']; ?> msgs</small>
                            <?php if ($excessSms > 0): ?>
                                <span class="sur-text">+<?php echo $excessSms; ?> msgs excess ($<?php echo number_format($chargeExcessSms, 2); ?>)</span>
                            <?php else: ?>
                                <span class="ok-text">Within Allowance</span>
                            <?php endif; ?>
                        </div>

                        <div class="usage-col">
                            <span>Data Usage</span>
                            <small><?php echo number_format($dataGb, 1); ?> / <?php echo $selectedPlan['inc_gb']; ?> GB</small>
                            <?php if ($excessGb > 0): ?>
                                <span class="sur-text">+<?php echo number_format($excessGb, 1); ?> GB excess ($<?php echo number_format($chargeExcessGb, 2); ?>)</span>
                            <?php else: ?>
                                <span class="ok-text">Within Allowance</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($totalOverage > 0): ?>
                        <div class="line-item warning-line">
                            <span>Total Usage Overage Surcharges</span>
                            <strong>$<?php echo number_format($totalOverage, 2); ?></strong>
                        </div>
                    <?php endif; ?>

                    <?php if ($totalAddons > 0): ?>
                        <div class="line-item">
                            <span>Plan Add-ons (<?php echo ($roamingAddon ? 'Roaming ' : '') . ($speedPassAddon ? '5G Pass' : ''); ?>)</span>
                            <strong>$<?php echo number_format($totalAddons, 2); ?></strong>
                        </div>
                    <?php endif; ?>

                    <div class="line-item">
                        <span>Subtotal</span>
                        <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                    </div>

                    <div class="line-item subtle">
                        <span>Regulatory & Universal Service Fee</span>
                        <strong>$<?php echo number_format($regulatoryFee, 2); ?></strong>
                    </div>

                    <div class="line-item subtle">
                        <span>Federal & State Telecom Tax (12.5%)</span>
                        <strong>$<?php echo number_format($telecomTax, 2); ?></strong>
                    </div>
                </div>

                <div class="total-due-box">
                    <div class="due-label">
                        <span>Total Monthly Invoice Due</span>
                        <small>Auto-Pay scheduled for <?php echo date('M 28, Y'); ?></small>
                    </div>
                    <div class="due-amount">
                        $<?php echo number_format($totalAmountDue, 2); ?>
                    </div>
                </div>

                <a href="index.php" class="btn-reset">&larr; Recalculate Another Cycle</a>
            </div>

        <?php else: ?>
            <!-- TARIFF & USAGE INPUT FORM VIEW -->
            <div class="billing-card form-card">
                <div class="card-header">
                    <span class="badge">Engine Portal</span>
                    <h2>Cellular Tariff Billing Engine</h2>
                    <p>Enter subscriber profile & monthly usage metrics to evaluate tariffs</p>
                </div>

                <form action="index.php" method="POST" class="tariff-form">
                    <div class="form-row">
                        <div class="form-group flex-2">
                            <label for="subscriberName">Subscriber Name</label>
                            <input type="text" id="subscriberName" name="subscriberName" placeholder="e.g. Jordan Vance" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="phoneNumber">Mobile Number</label>
                            <input type="text" id="phoneNumber" name="phoneNumber" placeholder="+1 (555) 019-2834" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="planType">Select Base Subscription Plan</label>
                        <select id="planType" name="planType" required>
                            <option value="starter">Starter Connect ($19.99/mo | 300 Min, 500 SMS, 5 GB)</option>
                            <option value="pro" selected>Pro Unlimited Voice ($49.99/mo | 1000 Min, 2000 SMS, 25 GB)</option>
                            <option value="ultra">Executive Ultra 5G ($79.99/mo | Unlimited Min/SMS, 100 GB)</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="voiceMinutes">Voice Minutes Used</label>
                            <input type="number" id="voiceMinutes" name="voiceMinutes" min="0" value="450" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="smsCount">SMS Messages Sent</label>
                            <input type="number" id="smsCount" name="smsCount" min="0" value="850" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="dataGb">Data Used (GB)</label>
                            <input type="number" id="dataGb" name="dataGb" step="0.1" min="0" value="28.5" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Optional Network Add-ons</label>
                        <div class="checkbox-group">
                            <label class="checkbox-card">
                                <input type="checkbox" name="roamingAddon" value="1">
                                <div class="check-text">
                                    <strong>Global Roaming Pass</strong>
                                    <small>+$15.00 / month</small>
                                </div>
                            </label>

                            <label class="checkbox-card">
                                <input type="checkbox" name="speedPassAddon" value="1" checked>
                                <div class="check-text">
                                    <strong>5G Ultra Speed Priority</strong>
                                    <small>+$9.99 / month</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-calculate">Generate Cellular Billing Statement</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>