<?php
session_start();

if (!isset($_SESSION['quote_history'])) {
    $_SESSION['quote_history'] = [];
}

// Policy Categories & Actuarial Parameters
$policyTypes = [
    'health' => [
        'name' => 'Comprehensive Health Shield',
        'icon' => '🩺',
        'base_rate' => 0.012, // 1.2% of coverage
        'color' => '#00f2fe',
        'add_ons' => [
            'critical_illness' => ['name' => 'Critical Illness Cover', 'flat' => 150],
            'maternity'        => ['name' => 'Maternity & Newborn Rider', 'flat' => 120],
            'hospital_cash'    => ['name' => 'Daily Hospital Cash Allowance', 'flat' => 60]
        ]
    ],
    'auto' => [
        'name' => 'Motor Vehicle Guard',
        'icon' => '🚗',
        'base_rate' => 0.025, // 2.5% of vehicle value
        'color' => '#ff007f',
        'add_ons' => [
            'zero_dep'        => ['name' => 'Zero Depreciation Cover', 'flat' => 180],
            'roadside_assist' => ['name' => '24/7 Roadside Assistance', 'flat' => 45],
            'engine_protect'  => ['name' => 'Engine & Gearbox Protection', 'flat' => 95]
        ]
    ],
    'life' => [
        'name' => 'Term Life Assurance',
        'icon' => '🛡️',
        'base_rate' => 0.0035, // 0.35% of sum assured
        'color' => '#7928ca',
        'add_ons' => [
            'accidental_death' => ['name' => 'Accidental Death Benefit (2x)', 'flat' => 85],
            'disability'       => ['name' => 'Total Disability Rider', 'flat' => 70],
            'terminal_illness' => ['name' => 'Terminal Illness Accelerated Payout', 'flat' => 110]
        ]
    ],
    'property' => [
        'name' => 'Home & Asset Protection',
        'icon' => '🏠',
        'base_rate' => 0.0018, // 0.18% of property value
        'color' => '#ffb700',
        'add_ons' => [
            'fire_flood' => ['name' => 'Natural Calamity & Flood Cover', 'flat' => 90],
            'burglary'   => ['name' => 'Burglary & High-Value Contents', 'flat' => 75],
            'structure'  => ['name' => 'Architectural Structural Protection', 'flat' => 130]
        ]
    ]
];

$quote = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $policyKey   = $_POST['policy_type'] ?? 'health';
    if (!isset($policyTypes[$policyKey])) {
        $policyKey = 'health';
    }
    $selectedType = $policyTypes[$policyKey];

    $applicantName = trim(htmlspecialchars($_POST['applicant_name'] ?? 'Clara Oswald'));
    $applicantAge  = max(18, min(80, intval($_POST['applicant_age'] ?? 34)));
    $coverageSum   = max(5000, floatval($_POST['coverage_sum'] ?? 250000));
    $deductible    = max(0, floatval($_POST['deductible'] ?? 1000));
    $riskTier      = $_POST['risk_tier'] ?? 'low';
    $selectedAddOns= $_POST['add_ons'] ?? [];

    // Actuarial Risk Multipliers
    $ageMultiplier = 1.0;
    if ($applicantAge > 50) {
        $ageMultiplier = 1.65;
    } elseif ($applicantAge > 35) {
        $ageMultiplier = 1.30;
    } elseif ($applicantAge > 25) {
        $ageMultiplier = 1.10;
    }

    $riskMultiplier = 1.0;
    if ($riskTier === 'medium') {
        $riskMultiplier = 1.25;
    } elseif ($riskTier === 'high') {
        $riskMultiplier = 1.60;
    }

    // Deductible Discount (Higher deductible reduces monthly premium)
    $deductibleDiscountPct = min(0.30, ($deductible / $coverageSum) * 5); // Up to 30% discount

    // Base Premium Calculation
    $rawBasePremium = $coverageSum * $selectedType['base_rate'];
    $riskAdjustedBase = $rawBasePremium * $ageMultiplier * $riskMultiplier;
    $afterDeductibleBase = $riskAdjustedBase * (1 - $deductibleDiscountPct);

    // Riders / Add-ons Calculation
    $addOnTotal = 0;
    $addOnDetails = [];
    foreach ($selectedAddOns as $addonKey) {
        if (isset($selectedType['add_ons'][$addonKey])) {
            $addon = $selectedType['add_ons'][$addonKey];
            $addOnTotal += $addon['flat'];
            $addOnDetails[] = $addon['name'];
        }
    }

    $annualPremium  = $afterDeductibleBase + $addOnTotal;
    $monthlyPremium = $annualPremium / 12;

    $quote = [
        'id'            => 'EST-' . strtoupper(substr(md5(time() . $applicantName), 0, 8)),
        'name'          => $applicantName,
        'age'           => $applicantAge,
        'policy_title'  => $selectedType['name'],
        'icon'          => $selectedType['icon'],
        'color'         => $selectedType['color'],
        'coverage_sum'  => $coverageSum,
        'deductible'    => $deductible,
        'risk_tier'     => ucfirst($riskTier),
        'add_ons'       => $addOnDetails,
        'annual_total'  => $annualPremium,
        'monthly_total' => $monthlyPremium,
        'disc_pct'      => round($deductibleDiscountPct * 100, 1),
        'timestamp'     => date('H:i | M d, Y')
    ];

    array_unshift($_SESSION['quote_history'], $quote);
    $_SESSION['quote_history'] = array_slice($_SESSION['quote_history'], 0, 5);
}

// Clear History
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['quote_history'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Insurance Premium Estimator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="estimator-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <span class="header-pill">Actuarial Quotation Engine v3.8</span>
            <h1>Policy Insurance Premium Estimator</h1>
            <p>Customize coverage sums, risk profiles, deductibles, and optional riders to compute actuarial quotes.</p>
        </header>

        <!-- QUOTE CARD DISPLAY -->
        <?php if ($quote): ?>
            <div class="quote-card" style="border-top-color: <?php echo $quote['color']; ?>;">
                <div class="quote-header">
                    <div>
                        <span class="policy-tag" style="background: <?php echo $quote['color']; ?>22; color: <?php echo $quote['color']; ?>; border-color: <?php echo $quote['color']; ?>66;">
                            <?php echo $quote['icon']; ?> <?php echo $quote['policy_title']; ?>
                        </span>
                        <h2>Insurance Quotation Statement</h2>
                        <p class="applicant-text">Applicant: <strong><?php echo htmlspecialchars($quote['name']); ?></strong> (Age: <?php echo $quote['age']; ?>)</p>
                    </div>
                    <div class="price-box">
                        <span class="p-label">ESTIMATED PREMIUM</span>
                        <strong class="p-monthly" style="color: <?php echo $quote['color']; ?>;">$<?php echo number_format($quote['monthly_total'], 2); ?><small>/mo</small></strong>
                        <span class="p-annual">$<?php echo number_format($quote['annual_total'], 2); ?> Billed Annually</span>
                    </div>
                </div>

                <div class="quote-grid">
                    <div class="q-item">
                        <span>Sum Insured / Coverage</span>
                        <strong>$<?php echo number_format($quote['coverage_sum'], 2); ?></strong>
                    </div>
                    <div class="q-item">
                        <span>Out-of-Pocket Deductible</span>
                        <strong>$<?php echo number_format($quote['deductible'], 2); ?></strong>
                    </div>
                    <div class="q-item">
                        <span>Risk Multiplier Tier</span>
                        <strong><?php echo $quote['risk_tier']; ?> Risk Profile</strong>
                    </div>
                    <div class="q-item">
                        <span>Deductible Discount</span>
                        <strong class="text-green">-<?php echo $quote['disc_pct']; ?>% Applied</strong>
                    </div>
                </div>

                <?php if (!empty($quote['add_ons'])): ?>
                    <div class="riders-section">
                        <span>Included Policy Riders & Add-Ons:</span>
                        <div class="riders-tags">
                            <?php foreach ($quote['add_ons'] as $rider): ?>
                                <span class="rider-pill">✨ <?php echo htmlspecialchars($rider); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="quote-footer">
                    <span>Actuarial Quote Ref: <code><?php echo $quote['id']; ?></code></span>
                    <span>Issued: <?php echo $quote['timestamp']; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- ESTIMATOR FORM -->
        <form action="index.php" method="POST" class="estimator-form">
            
            <span class="form-title">1. Choose Policy Insurance Class</span>
            <div class="policy-type-grid">
                <?php 
                $selectedTypeKey = $_POST['policy_type'] ?? 'health';
                foreach ($policyTypes as $key => $type): 
                    $isSelected = ($selectedTypeKey === $key);
                ?>
                    <label class="type-card <?php echo $isSelected ? 'active' : ''; ?>" style="--accent: <?php echo $type['color']; ?>">
                        <input type="radio" name="policy_type" value="<?php echo $key; ?>" <?php echo $isSelected ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <span class="t-icon"><?php echo $type['icon']; ?></span>
                        <strong class="t-name"><?php echo $type['name']; ?></strong>
                    </label>
                <?php endforeach; ?>
            </div>

            <span class="form-title">2. Policyholder Risk Profile</span>
            <div class="form-grid">
                <div class="form-group">
                    <label for="applicant_name">Applicant Full Name</label>
                    <input type="text" id="applicant_name" name="applicant_name" value="<?php echo htmlspecialchars($_POST['applicant_name'] ?? 'Clara Oswald'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="applicant_age">Applicant Age</label>
                    <input type="number" id="applicant_age" name="applicant_age" min="18" max="80" value="<?php echo htmlspecialchars($_POST['applicant_age'] ?? 34); ?>" required>
                </div>

                <div class="form-group">
                    <label for="coverage_sum">Requested Coverage / Sum Assured ($)</label>
                    <input type="number" step="5000" id="coverage_sum" name="coverage_sum" value="<?php echo htmlspecialchars($_POST['coverage_sum'] ?? 250000); ?>" required>
                </div>

                <div class="form-group">
                    <label for="deductible">Deductible Selection ($)</label>
                    <input type="number" step="250" id="deductible" name="deductible" value="<?php echo htmlspecialchars($_POST['deductible'] ?? 1000); ?>" required>
                </div>

                <div class="form-group full-width">
                    <label>Actuarial Risk Assessment Tier</label>
                    <div class="risk-options">
                        <label class="risk-radio">
                            <input type="radio" name="risk_tier" value="low" <?php echo (($_POST['risk_tier'] ?? 'low') === 'low') ? 'checked' : ''; ?>>
                            <span>🟢 Low Risk (Standard / Non-Smoker)</span>
                        </label>
                        <label class="risk-radio">
                            <input type="radio" name="risk_tier" value="medium" <?php echo (($_POST['risk_tier'] ?? 'low') === 'medium') ? 'checked' : ''; ?>>
                            <span>🟡 Moderate Risk (Occasional Hazards)</span>
                        </label>
                        <label class="risk-radio">
                            <input type="radio" name="risk_tier" value="high" <?php echo (($_POST['risk_tier'] ?? 'low') === 'high') ? 'checked' : ''; ?>>
                            <span>🔴 High Risk (Pre-existing / Hazardous Work)</span>
                        </label>
                    </div>
                </div>
            </div>

            <span class="form-title">3. Optional Policy Add-Ons (Riders)</span>
            <div class="addons-grid">
                <?php 
                $activeType = $policyTypes[$selectedTypeKey];
                $postedAddOns = $_POST['add_ons'] ?? array_keys($activeType['add_ons']);
                foreach ($activeType['add_ons'] as $aKey => $aData): 
                    $isChecked = in_array($aKey, $postedAddOns);
                ?>
                    <label class="addon-card">
                        <input type="checkbox" name="add_ons[]" value="<?php echo $aKey; ?>" <?php echo $isChecked ? 'checked' : ''; ?>>
                        <div class="a-info">
                            <strong><?php echo $aData['name']; ?></strong>
                            <span>+$<?php echo $aData['flat']; ?>/yr flat rider</span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="submit-btn">Compute Premium & Issue Quotation</button>
        </form>

        <!-- SESSION HISTORY -->
        <?php if (!empty($_SESSION['quote_history'])): ?>
            <div class="history-panel">
                <div class="history-head">
                    <span>Recent Session Quotations</span>
                    <a href="index.php?action=clear" class="clear-btn">Clear History</a>
                </div>
                <div class="history-list">
                    <?php foreach ($_SESSION['quote_history'] as $q): ?>
                        <div class="history-row">
                            <div>
                                <strong><?php echo $q['icon']; ?> <?php echo htmlspecialchars($q['name']); ?> — <?php echo $q['policy_title']; ?></strong>
                                <span>Coverage: $<?php echo number_format($q['coverage_sum']); ?> • <?php echo $q['risk_tier']; ?> Risk</span>
                            </div>
                            <div class="hist-price" style="color: <?php echo $q['color']; ?>;">$<?php echo number_format($q['monthly_total'], 2); ?>/mo</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>