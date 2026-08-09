<?php
session_start();

if (!isset($_SESSION['members'])) {
    $_SESSION['members'] = [];
}

// Library Tier Definitions
$membershipTiers = [
    'standard' => [
        'name' => 'Standard Reader',
        'badge' => '📘 Casual',
        'color' => '#00e5ff',
        'monthly_fee' => 0,
        'book_limit' => 5,
        'digital_access' => ['E-Books', 'Audiobooks'],
        'desc' => 'Ideal for standard reading, general literature, and popular fiction.'
    ],
    'scholar' => [
        'name' => 'Scholar Pass',
        'badge' => '📗 Academic',
        'color' => '#10b981',
        'monthly_fee' => 15,
        'book_limit' => 15,
        'digital_access' => ['E-Books', 'Audiobooks', 'Peer-Reviewed Journals', 'Research Papers'],
        'desc' => 'Tailored for university students and independent scholars needing paper access.'
    ],
    'fellow' => [
        'name' => 'Research Fellow',
        'badge' => '📙 Specialist',
        'color' => '#ff2a75',
        'monthly_fee' => 35,
        'book_limit' => 30,
        'digital_access' => ['E-Books', 'Audiobooks', 'Peer-Reviewed Journals', 'Historical Archives', 'Dataset Vaults'],
        'desc' => 'Full access to high-impact primary archives and global research data repositories.'
    ],
    'institutional' => [
        'name' => 'Institutional VIP',
        'badge' => '👑 Unlimited',
        'color' => '#ffb800',
        'monthly_fee' => 75,
        'book_limit' => 100,
        'digital_access' => ['All Archives', 'Inter-Library Loans', 'Priority Research Desk', 'API Datasets'],
        'desc' => 'Unrestricted institutional privileges with dedicated research desk support.'
    ]
];

// Special Interest Badges / Genres
$genresList = [
    'sci_tech' => 'Quantum Computing & AI',
    'history'  => 'World History & Manuscripts',
    'bio_med'  => 'Biomedical & Life Sciences',
    'arts'     => 'Fine Arts & Architecture',
    'philos'   => 'Philosophy & Humanities'
];

$activeCard = null;

// Handle Registration Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tierKey = $_POST['tier'] ?? 'standard';
    if (!isset($membershipTiers[$tierKey])) {
        $tierKey = 'standard';
    }
    $selectedTier = $membershipTiers[$tierKey];

    $fullName      = trim(htmlspecialchars($_POST['full_name'] ?? 'Alex Vance'));
    $email         = trim(htmlspecialchars($_POST['email'] ?? 'alex.vance@archival.org'));
    $institution   = trim(htmlspecialchars($_POST['institution'] ?? 'Independent Researcher'));
    $durationMonths= max(1, min(36, intval($_POST['duration_months'] ?? 12)));
    $selectedGenres= $_POST['genres'] ?? ['sci_tech'];

    $genreNames = [];
    foreach ($selectedGenres as $gKey) {
        if (isset($genresList[$gKey])) {
            $genreNames[] = $genresList[$gKey];
        }
    }

    // Fee & Expiry Calculations
    $monthlyCost  = $selectedTier['monthly_fee'];
    $subtotal     = $monthlyCost * $durationMonths;
    $discount     = ($durationMonths >= 12) ? ($subtotal * 0.15) : 0; // 15% Annual Discount
    $totalFee     = $subtotal - $discount;

    $issueDate    = date('Y-m-d');
    $expiryDate   = date('Y-m-d', strtotime("+$durationMonths months"));
    $memberCode   = 'LIB-' . strtoupper(substr(md5($email . time()), 0, 8));

    $activeCard = [
        'id'          => $memberCode,
        'full_name'   => $fullName,
        'email'       => $email,
        'institution' => $institution,
        'tier_name'   => $selectedTier['name'],
        'tier_color'  => $selectedTier['color'],
        'tier_badge'  => $selectedTier['badge'],
        'book_limit'  => $selectedTier['book_limit'],
        'access_list' => $selectedTier['digital_access'],
        'genres'      => $genreNames,
        'duration'    => $durationMonths,
        'total_fee'   => $totalFee,
        'issue_date'  => date('M d, Y', strtotime($issueDate)),
        'expiry_date' => date('M d, Y', strtotime($expiryDate)),
        'created_at'  => date('H:i | M d, Y')
    ];

    array_unshift($_SESSION['members'], $activeCard);
    $_SESSION['members'] = array_slice($_SESSION['members'], 0, 5);
}

// Clear History Action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['members'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Library Membership Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="portal-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <span class="header-tag">Archival Network v4.2</span>
            <h1>Digital Library Membership Portal</h1>
            <p>Enroll in specialized research tiers, issue digital credentials, and unlock global data vaults.</p>
        </header>

        <!-- ISSUED DIGITAL MEMBERSHIP BADGE -->
        <?php if ($activeCard): ?>
            <div class="id-card" style="border-top-color: <?php echo $activeCard['tier_color']; ?>;">
                <div class="card-chip"></div>
                <div class="card-header">
                    <div>
                        <span class="tier-pill" style="background: <?php echo $activeCard['tier_color']; ?>22; color: <?php echo $activeCard['tier_color']; ?>; border-color: <?php echo $activeCard['tier_color']; ?>66;">
                            <?php echo $activeCard['tier_badge']; ?> • <?php echo $activeCard['tier_name']; ?>
                        </span>
                        <h2><?php echo htmlspecialchars($activeCard['full_name']); ?></h2>
                        <p class="institution-text">🏛️ <?php echo htmlspecialchars($activeCard['institution']); ?></p>
                    </div>
                    <div class="id-number">
                        <span>Member ID</span>
                        <strong><?php echo $activeCard['id']; ?></strong>
                    </div>
                </div>

                <div class="card-grid">
                    <div class="c-item">
                        <span>Email Contact</span>
                        <strong><?php echo htmlspecialchars($activeCard['email']); ?></strong>
                    </div>
                    <div class="c-item">
                        <span>Borrowing Cap</span>
                        <strong><?php echo $activeCard['book_limit']; ?> Items at once</strong>
                    </div>
                    <div class="c-item">
                        <span>Valid From</span>
                        <strong><?php echo $activeCard['issue_date']; ?></strong>
                    </div>
                    <div class="c-item">
                        <span>Expiration</span>
                        <strong><?php echo $activeCard['expiry_date']; ?></strong>
                    </div>
                </div>

                <div class="card-permissions">
                    <div class="perm-group">
                        <span>Digital Vault Access:</span>
                        <div class="tags-row">
                            <?php foreach ($activeCard['access_list'] as $access): ?>
                                <span class="tag-badge">✓ <?php echo htmlspecialchars($access); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (!empty($activeCard['genres'])): ?>
                        <div class="perm-group">
                            <span>Specialist Domains:</span>
                            <div class="tags-row">
                                <?php foreach ($activeCard['genres'] as $genre): ?>
                                    <span class="tag-badge spec">📚 <?php echo htmlspecialchars($genre); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer">
                    <span>Subscription Fee Paid: <strong>$<?php echo number_format($activeCard['total_fee'], 2); ?></strong> (<?php echo $activeCard['duration']; ?> Months)</span>
                    <span class="status-live">● ACTIVE MEMBER</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- ENROLLMENT FORM -->
        <form action="index.php" method="POST" class="registration-form">
            
            <span class="section-title">1. Select Membership Tier</span>
            <div class="tier-grid">
                <?php 
                $defaultTier = $_POST['tier'] ?? 'scholar';
                foreach ($membershipTiers as $key => $tier): 
                    $isSelected = ($defaultTier === $key);
                ?>
                    <label class="tier-card <?php echo $isSelected ? 'selected' : ''; ?>" style="--accent-color: <?php echo $tier['color']; ?>">
                        <input type="radio" name="tier" value="<?php echo $key; ?>" <?php echo $isSelected ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <div class="tier-top">
                            <span class="tier-badge-label" style="background: <?php echo $tier['color']; ?>;"><?php echo $tier['badge']; ?></span>
                            <span class="tier-price">$<?php echo $tier['monthly_fee']; ?><small>/mo</small></span>
                        </div>
                        <h3><?php echo $tier['name']; ?></h3>
                        <p class="tier-desc"><?php echo $tier['desc']; ?></p>
                        <div class="tier-cap">📖 Limit: <strong><?php echo $tier['book_limit']; ?> Titles</strong></div>
                    </label>
                <?php endforeach; ?>
            </div>

            <span class="section-title">2. Scholar Credentials</span>
            <div class="inputs-grid">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? 'Dr. Aris Thorne'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Academic / Personal Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? 'thorne@cybernex.edu'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="institution">Affiliated University / Organization</label>
                    <input type="text" id="institution" name="institution" value="<?php echo htmlspecialchars($_POST['institution'] ?? 'Institute for Quantum Studies'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="duration_months">Membership Period (15% Annual Disc.)</label>
                    <select name="duration_months" id="duration_months">
                        <option value="1" <?php echo (($_POST['duration_months'] ?? 12) == 1) ? 'selected' : ''; ?>>1 Month Pass</option>
                        <option value="6" <?php echo (($_POST['duration_months'] ?? 12) == 6) ? 'selected' : ''; ?>>6 Months Pass</option>
                        <option value="12" <?php echo (($_POST['duration_months'] ?? 12) == 12) ? 'selected' : ''; ?>>12 Months Annual Pass (Best Value)</option>
                        <option value="24" <?php echo (($_POST['duration_months'] ?? 12) == 24) ? 'selected' : ''; ?>>24 Months Fellow Pass</option>
                    </select>
                </div>
            </div>

            <span class="section-title">3. Focus Archives & Specialist Domains</span>
            <div class="genres-grid">
                <?php 
                $postedGenres = $_POST['genres'] ?? ['sci_tech', 'history'];
                foreach ($genresList as $gKey => $gLabel): 
                    $isChecked = in_array($gKey, $postedGenres);
                ?>
                    <label class="genre-card">
                        <input type="checkbox" name="genres[]" value="<?php echo $gKey; ?>" <?php echo $isChecked ? 'checked' : ''; ?>>
                        <span><?php echo $gLabel; ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-submit">Issue Digital Library Credentials</button>
        </form>

        <!-- SESSION MEMBER REGISTRY -->
        <?php if (!empty($_SESSION['members'])): ?>
            <div class="history-block">
                <div class="history-header">
                    <span>Recent Session Issued Passports</span>
                    <a href="index.php?action=clear" class="clear-btn">Purge Registry</a>
                </div>
                <div class="history-list">
                    <?php foreach ($_SESSION['members'] as $m): ?>
                        <div class="history-item">
                            <div>
                                <strong><?php echo htmlspecialchars($m['full_name']); ?> (<?php echo $m['tier_name']; ?>)</strong>
                                <span><?php echo $m['id']; ?> • Expires: <?php echo $m['expiry_date']; ?></span>
                            </div>
                            <div class="hist-price">$<?php echo number_format($m['total_fee'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>