<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Revenue & Subscription Calculator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="calculator-card">
        <?php
        // Pricing Configuration Matrix
        $tiers = [
            'starter'    => ['name' => 'Starter Tier', 'base' => 49.00, 'seatRate' => 12.00],
            'professional'=> ['name' => 'Professional Tier', 'base' => 199.00, 'seatRate' => 20.00],
            'enterprise' => ['name' => 'Enterprise Suite', 'base' => 499.00, 'seatRate' => 35.00]
        ];

        $availableAddons = [
            'sla' => ['name' => '24/7 Dedicated SLA Support', 'price' => 150.00],
            'security' => ['name' => 'SOC2 & HIPAA Compliance Shield', 'price' => 250.00],
            'api' => ['name' => 'Custom Webhook & API Connectors', 'price' => 180.00]
        ];
        ?>

        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- REVENUE PROJECTION RESULT VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Forecast Generated</span>
                <h2>Commercial Revenue Projections</h2>
                <p>Client: <?php echo htmlspecialchars(trim($_POST['companyName'] ?? 'Acme Corp')); ?> | Plan Model</p>
            </div>

            <?php
            $companyName = htmlspecialchars(trim($_POST['companyName'] ?? 'Enterprise Client'));
            $tierKey     = $_POST['tier'] ?? 'professional';
            $seats       = max(1, intval($_POST['seats'] ?? 1));
            $billing     = $_POST['billing'] ?? 'monthly';
            $selectedAddons = $_POST['addons'] ?? [];
            $promoCode   = strtoupper(trim($_POST['promoCode'] ?? ''));

            $tierData  = $tiers[$tierKey] ?? $tiers['professional'];
            $basePrice = $tierData['base'];
            $seatCost  = $tierData['seatRate'] * $seats;

            // Calculate Add-ons
            $addonsTotal = 0.0;
            $addonBreakdown = [];
            foreach ($selectedAddons as $addonKey) {
                if (isset($availableAddons[$addonKey])) {
                    $addonsTotal += $availableAddons[$addonKey]['price'];
                    $addonBreakdown[] = $availableAddons[$addonKey];
                }
            }

            $grossMonthly = $basePrice + $seatCost + $addonsTotal;

            // Annual Discount (20% off total monthly base if annual)
            $annualDiscount = ($billing === 'annual') ? ($grossMonthly * 0.20) : 0.0;
            $monthlyNet = $grossMonthly - $annualDiscount;

            // Promo Code Discount (e.g. "GROWTH10" for additional 10% off)
            $promoDiscount = 0.0;
            $promoApplied  = false;
            if ($promoCode === 'GROWTH10') {
                $promoDiscount = $monthlyNet * 0.10;
                $monthlyNet -= $promoDiscount;
                $promoApplied = true;
            }

            $mrr = $monthlyNet;
            $arr = $mrr * 12;
            ?>

            <div class="client-banner">
                <div><span>Account:</span> <strong><?php echo $companyName; ?></strong></div>
                <div><span>Plan:</span> <strong><?php echo $tierData['name']; ?> (<?php echo ucfirst($billing); ?>)</strong></div>
            </div>

            <div class="metrics-grid">
                <div class="metric-box mrr-box">
                    <span>Monthly Recurring Revenue (MRR)</span>
                    <h3>$<?php echo number_format($mrr, 2); ?></h3>
                </div>
                <div class="metric-box arr-box">
                    <span>Annual Recurring Revenue (ARR)</span>
                    <h3>$<?php echo number_format($arr, 2); ?></h3>
                </div>
            </div>

            <div class="breakdown-container">
                <span class="breakdown-label">Itemized Cost Structure (Monthly)</span>
                
                <div class="cost-row">
                    <span>Base Subscription (<?php echo $tierData['name']; ?>):</span>
                    <strong>$<?php echo number_format($basePrice, 2); ?></strong>
                </div>
                
                <div class="cost-row">
                    <span>Seat Licenses (<?php echo $seats; ?> seats @ $<?php echo number_format($tierData['seatRate'], 2); ?>/ea):</span>
                    <strong>$<?php echo number_format($seatCost, 2); ?></strong>
                </div>

                <?php if (!empty($addonBreakdown)): ?>
                    <?php foreach ($addonBreakdown as $addon): ?>
                        <div class="cost-row">
                            <span>Add-on: <?php echo $addon['name']; ?>:</span>
                            <strong>+$<?php echo number_format($addon['price'], 2); ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($billing === 'annual'): ?>
                    <div class="cost-row discount-row">
                        <span>Annual Billing Incentive (20% Off):</span>
                        <strong>-$<?php echo number_format($annualDiscount, 2); ?>/mo</strong>
                    </div>
                <?php endif; ?>

                <?php if ($promoApplied): ?>
                    <div class="cost-row discount-row">
                        <span>Promo Code [GROWTH10] (10% Off):</span>
                        <strong>-$<?php echo number_format($promoDiscount, 2); ?>/mo</strong>
                    </div>
                <?php endif; ?>

                <div class="cost-row grand-total-row">
                    <span>Net Monthly Commitment:</span>
                    <strong>$<?php echo number_format($mrr, 2); ?>/mo</strong>
                </div>
            </div>

            <a href="index.php" class="back-btn">&larr; Recalculate New Commercial Contract</a>

        <?php else: ?>
            <!-- REVENUE INPUT FORM -->
            <div class="card-header">
                <span class="badge">Commercial Engine</span>
                <h2>Subscription Revenue Calculator</h2>
                <p>Configure enterprise parameters to project recurring commercial yield</p>
            </div>

            <form action="index.php" method="POST" class="calc-form">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="companyName">Organization / Client Name</label>
                        <input type="text" id="companyName" name="companyName" placeholder="e.g. Nexus Dynamics" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="seats">User Seats</label>
                        <input type="number" id="seats" name="seats" min="1" max="500" value="10" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="tier">Service Tier</label>
                        <select id="tier" name="tier" required>
                            <option value="starter">Starter ($49/mo + $12/seat)</option>
                            <option value="professional" selected>Professional ($199/mo + $20/seat)</option>
                            <option value="enterprise">Enterprise ($499/mo + $35/seat)</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="billing">Billing Cadence</label>
                        <select id="billing" name="billing" required>
                            <option value="monthly">Monthly Billing</option>
                            <option value="annual" selected>Annual Billing (Save 20%)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Enterprise Add-on Modules</label>
                    <div class="addons-grid">
                        <?php foreach ($availableAddons as $key => $addon): ?>
                            <label class="checkbox-card">
                                <input type="checkbox" name="addons[]" value="<?php echo $key; ?>">
                                <div class="checkbox-content">
                                    <span class="addon-title"><?php echo $addon['name']; ?></span>
                                    <span class="addon-price">+$<?php echo number_format($addon['price'], 0); ?>/mo</span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="promoCode">Promo / Voucher Code</label>
                    <input type="text" id="promoCode" name="promoCode" placeholder="Enter code (Try: GROWTH10)">
                </div>

                <button type="submit" class="submit-btn">Compute Commercial Forecast</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>