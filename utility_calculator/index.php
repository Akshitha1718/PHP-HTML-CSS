<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiered Utility Tariff Calculator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="utility-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- ITEMIZED BILL VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Statement Generated</span>
                <h2>Utility Billing Invoice</h2>
                <p>Account Ref: #<?php echo strtoupper(htmlspecialchars($_POST['accNumber'] ?? 'ACC-0000')); ?> | <?php echo date("M Y"); ?></p>
            </div>

            <?php
            $consumerName = htmlspecialchars(trim($_POST['consumerName'] ?? 'Valued Customer'));
            $accNumber    = htmlspecialchars(trim($_POST['accNumber'] ?? 'ACC-0000'));
            $utilityType  = htmlspecialchars(trim($_POST['utilityType'] ?? 'Electricity'));
            $units        = floatval($_POST['units'] ?? 0);

            // Fixed Base Service Charges per Utility Type
            $baseFees = [
                'Electricity' => 15.00,
                'Water'       => 10.00,
                'Natural Gas' => 12.50
            ];
            $baseFee = $baseFees[$utilityType] ?? 12.00;

            // Tiered Rate Calculations (Progressive Slabs)
            // Tier 1: 0 - 100 units
            // Tier 2: 101 - 300 units
            // Tier 3: Above 300 units
            $tier1Cost = 0;
            $tier2Cost = 0;
            $tier3Cost = 0;

            if ($utilityType === 'Electricity') {
                $r1 = 0.15; $r2 = 0.25; $r3 = 0.40;
            } elseif ($utilityType === 'Water') {
                $r1 = 0.08; $r2 = 0.15; $r3 = 0.28;
            } else { // Natural Gas
                $r1 = 0.12; $r2 = 0.22; $r3 = 0.35;
            }

            if ($units > 300) {
                $tier1Cost = 100 * $r1;
                $tier2Cost = 200 * $r2;
                $tier3Cost = ($units - 300) * $r3;
            } elseif ($units > 100) {
                $tier1Cost = 100 * $r1;
                $tier2Cost = ($units - 100) * $r2;
            } else {
                $tier1Cost = $units * $r1;
            }

            $consumptionCharge = $tier1Cost + $tier2Cost + $tier3Cost;
            $subtotal          = $consumptionCharge + $baseFee;
            $utilityTax        = $subtotal * 0.07; // 7% local utility tax
            $totalAmount       = $subtotal + $utilityTax;
            ?>

            <div class="consumer-banner">
                <div><span>Consumer:</span> <strong><?php echo $consumerName; ?></strong></div>
                <div><span>Service:</span> <strong><?php echo $utilityType; ?></strong></div>
            </div>

            <div class="usage-grid">
                <div class="usage-box">
                    <span>Units Consumed</span>
                    <h3 class="highlight-units"><?php echo number_format($units, 1); ?></h3>
                </div>
                <div class="usage-box">
                    <span>Usage Charges</span>
                    <h3>$<?php echo number_format($consumptionCharge, 2); ?></h3>
                </div>
                <div class="usage-box">
                    <span>Fixed Base Fee</span>
                    <h3>$<?php echo number_format($baseFee, 2); ?></h3>
                </div>
            </div>

            <div class="slab-breakdown">
                <span class="slab-title">Tiered Rate Slab Breakdown:</span>
                <div class="slab-row">
                    <span>Tier 1 (0-100 Units @ $<?php echo number_format($r1, 2); ?>):</span>
                    <strong>$<?php echo number_format($tier1Cost, 2); ?></strong>
                </div>
                <div class="slab-row">
                    <span>Tier 2 (101-300 Units @ $<?php echo number_format($r2, 2); ?>):</span>
                    <strong>$<?php echo number_format($tier2Cost, 2); ?></strong>
                </div>
                <div class="slab-row">
                    <span>Tier 3 (>300 Units @ $<?php echo number_format($r3, 2); ?>):</span>
                    <strong>$<?php echo number_format($tier3Cost, 2); ?></strong>
                </div>
            </div>

            <div class="total-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Local Utility Tax (7%):</span>
                    <span>+$<?php echo number_format($utilityTax, 2); ?></span>
                </div>
                <div class="summary-row grand-total">
                    <span>Total Amount Due:</span>
                    <span>$<?php echo number_format($totalAmount, 2); ?></span>
                </div>
            </div>

            <a href="index.php" class="back-btn">&larr; Calculate Another Tariff Bill</a>

        <?php else: ?>
            <!-- UTILITY INPUT FORM -->
            <div class="card-header">
                <span class="badge">PowerGrid Matrix v10.0</span>
                <h2>Tariff Calculator</h2>
                <p>Compute tiered consumption costs and taxes for monthly utility usage</p>
            </div>

            <form action="index.php" method="POST" class="utility-form">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="consumerName">Consumer Name</label>
                        <input type="text" id="consumerName" name="consumerName" placeholder="e.g. Elena Rostova" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="accNumber">Account Number</label>
                        <input type="text" id="accNumber" name="accNumber" placeholder="e.g. ELEC-8821" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="utilityType">Utility Service</label>
                        <select id="utilityType" name="utilityType" required>
                            <option value="Electricity">Electricity (kWh)</option>
                            <option value="Water">Water Supply (Gallons)</option>
                            <option value="Natural Gas">Natural Gas (Therms)</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="units">Units Consumed</label>
                        <input type="number" id="units" name="units" min="0" step="0.1" placeholder="340.5" required>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Calculate Tiered Tariff & Issue Bill</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>