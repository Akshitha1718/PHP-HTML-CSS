<?php
session_start();

if (!isset($_SESSION['onboarded_customers'])) {
    $_SESSION['onboarded_customers'] = [];
}

$activeCustomer = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName    = trim(htmlspecialchars($_POST['full_name'] ?? 'Elena Rostova'));
    $email       = trim(htmlspecialchars($_POST['email'] ?? 'elena.r@cyberstore.io'));
    $city        = trim(htmlspecialchars($_POST['city'] ?? 'Neo Tokyo'));
    $accountType = trim(htmlspecialchars($_POST['account_type'] ?? 'Personal Shopper'));
    
    // Preferences Array
    $categories  = $_POST['categories'] ?? ['Tech & Gadgets', 'Fashion & Apparel'];
    if (!is_array($categories)) {
        $categories = [$categories];
    }
    
    $budgetRange = floatval($_POST['budget_range'] ?? 1500);

    // Calculate Onboarding Score & Tier
    $preferenceCount = count($categories);
    $onboardingScore = min(100, 50 + ($preferenceCount * 10) + ($budgetRange > 1000 ? 20 : 10));

    if ($budgetRange >= 2000 || $preferenceCount >= 4) {
        $tier = 'PLATINUM PIONEER';
        $discountCode = 'WELCOME-PLATINUM-30';
        $perkDiscount = '30% OFF First Order';
        $badgeColor = '#a3e635'; // Lime Neon
        $accentGradient = 'linear-gradient(135deg, #a3e635, #10b981)';
    } elseif ($budgetRange >= 800) {
        $tier = 'GOLD EARLY BIRD';
        $discountCode = 'WELCOME-GOLD-20';
        $perkDiscount = '20% OFF First Order';
        $badgeColor = '#06b6d4'; // Cyber Cyan
        $accentGradient = 'linear-gradient(135deg, #06b6d4, #3b82f6)';
    } else {
        $tier = 'SILVER MEMBER';
        $discountCode = 'WELCOME-SILVER-10';
        $perkDiscount = '10% OFF First Order';
        $badgeColor = '#fbbf24'; // Solar Amber
        $accentGradient = 'linear-gradient(135deg, #fbbf24, #f97316)';
    }

    $activeCustomer = [
        'customer_id'      => 'CUST-' . strtoupper(substr(md5($email . time()), 0, 8)),
        'full_name'        => $fullName,
        'email'            => $email,
        'city'             => $city,
        'account_type'     => $accountType,
        'categories'       => $categories,
        'budget_range'     => $budgetRange,
        'onboarding_score' => $onboardingScore,
        'tier'             => $tier,
        'discount_code'    => $discountCode,
        'perk_discount'    => $perkDiscount,
        'badge_color'      => $badgeColor,
        'accent_gradient'  => $accentGradient,
        'joined_at'        => date('H:i | M d, Y')
    ];

    // Store in Session History
    array_unshift($_SESSION['onboarded_customers'], $activeCustomer);
    $_SESSION['onboarded_customers'] = array_slice($_SESSION['onboarded_customers'], 0, 5);
}

// Action Handler: Clear Session Log
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['onboarded_customers'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Customer Onboarding System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="portal-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <span class="header-pill">Task 27 • Customer Experience Engine</span>
            <h1>E-Commerce Customer Onboarding System</h1>
            <p>Onboard new shoppers, compute loyalty tier placement, assign personalized perks, and activate profile tokens.</p>
        </header>

        <!-- ACTIVE PROFILE SUMMARY CARD -->
        <?php if ($activeCustomer): ?>
            <div class="profile-card">
                <div class="card-top">
                    <div>
                        <span class="cust-id">SYSTEM TOKEN: <?php echo $activeCustomer['customer_id']; ?></span>
                        <h2><?php echo htmlspecialchars($activeCustomer['full_name']); ?></h2>
                        <p class="cust-meta"><?php echo htmlspecialchars($activeCustomer['email']); ?> • <?php echo htmlspecialchars($activeCustomer['city']); ?></p>
                    </div>
                    <span class="tier-badge" style="background: <?php echo $activeCustomer['badge_color']; ?>22; color: <?php echo $activeCustomer['badge_color']; ?>; border-color: <?php echo $activeCustomer['badge_color']; ?>88;">
                        <?php echo $activeCustomer['tier']; ?>
                    </span>
                </div>

                <!-- HIGHLIGHT PERKS GRID -->
                <div class="perks-grid">
                    <div class="p-box">
                        <span class="p-title">Welcome Voucher</span>
                        <strong class="p-big" style="background: <?php echo $activeCustomer['accent_gradient']; ?>; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            <?php echo $activeCustomer['discount_code']; ?>
                        </strong>
                        <span class="p-sub"><?php echo $activeCustomer['perk_discount']; ?></span>
                    </div>

                    <div class="p-box">
                        <span class="p-title">Est. Monthly Spend</span>
                        <strong class="p-big">$<?php echo number_format($activeCustomer['budget_range'], 2); ?></strong>
                        <span class="p-sub"><?php echo htmlspecialchars($activeCustomer['account_type']); ?></span>
                    </div>

                    <div class="p-box">
                        <span class="p-title">Profile Completeness</span>
                        <strong class="p-big text-lime"><?php echo $activeCustomer['onboarding_score']; ?>%</strong>
                        <span class="p-sub">Optimization Score</span>
                    </div>
                </div>

                <!-- CATEGORY PREFERENCES SPECTRUM -->
                <div class="preference-section">
                    <h4>Tailored Shopping Feed Preferences</h4>
                    <div class="tags-flex">
                        <?php foreach ($activeCustomer['categories'] as $cat): ?>
                            <span class="cat-tag"><?php echo htmlspecialchars($cat); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card-footer">
                    <span>Account Status: <strong class="text-emerald">Active & Verified</strong></span>
                    <span>Onboarded: <?php echo $activeCustomer['joined_at']; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- ONBOARDING FORM -->
        <form action="index.php" method="POST" class="onboard-form">
            <span class="form-title">Customer Onboarding & Preferences Portal</span>
            
            <div class="form-grid">
                <div class="field-group">
                    <label for="full_name">Customer Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? 'Elena Rostova'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? 'elena.r@cyberstore.io'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="city">Delivery City / Hub</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? 'Neo Tokyo'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="account_type">Shopper Account Type</label>
                    <select id="account_type" name="account_type" class="select-input">
                        <option value="Personal Shopper">Personal Shopper</option>
                        <option value="VIP Commercial Buyer">VIP Commercial Buyer</option>
                        <option value="Creator / Affiliate">Creator / Affiliate</option>
                    </select>
                </div>

                <div class="field-group span-2">
                    <label for="budget_range">Estimated Monthly E-Commerce Spend ($)</label>
                    <input type="number" id="budget_range" name="budget_range" min="100" max="10000" step="50" value="<?php echo $_POST['budget_range'] ?? 1500; ?>" required>
                </div>

                <div class="field-group span-2">
                    <label>Preferred Shopping Categories (Select multiple)</label>
                    <div class="checkbox-grid">
                        <?php 
                        $availableCats = ['Tech & Gadgets', 'Fashion & Apparel', 'Home & Smart Living', 'Fitness & Gear', 'Beauty & Wellness', 'Luxury Goods'];
                        $selectedCats = $_POST['categories'] ?? ['Tech & Gadgets', 'Fashion & Apparel'];
                        foreach ($availableCats as $catOption):
                            $checked = in_array($catOption, $selectedCats) ? 'checked' : '';
                        ?>
                            <label class="check-btn">
                                <input type="checkbox" name="categories[]" value="<?php echo $catOption; ?>" <?php echo $checked; ?>>
                                <span><?php echo $catOption; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Complete Onboarding & Generate Perks</button>
        </form>

        <!-- RECENT ONBOARDING HISTORY -->
        <?php if (!empty($_SESSION['onboarded_customers'])): ?>
            <div class="history-panel">
                <div class="history-head">
                    <span>Recent Customer Registrations</span>
                    <a href="index.php?action=clear" class="clear-btn">Clear History</a>
                </div>
                <div class="history-grid">
                    <?php foreach ($_SESSION['onboarded_customers'] as $cust): ?>
                        <div class="history-card">
                            <div class="h-top">
                                <strong><?php echo htmlspecialchars($cust['full_name']); ?></strong>
                                <span class="h-badge" style="color: <?php echo $cust['badge_color']; ?>; border-color: <?php echo $cust['badge_color']; ?>66;">
                                    <?php echo $cust['tier']; ?>
                                </span>
                            </div>
                            <span class="h-sub"><?php echo htmlspecialchars($cust['email']); ?> • Code: <?php echo $cust['discount_code']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>