<?php
session_start();

if (!isset($_SESSION['staff_directory'])) {
    $_SESSION['staff_directory'] = [];
}

// Clearance Level Definitions
$clearanceTiers = [
    'public' => [
        'title' => 'Tier 1 - Public Clearance',
        'badge' => '🟢 LEVEL 1 (STANDARD)',
        'color' => '#10b981',
        'perks' => 'General office floor, public portals, guest networks'
    ],
    'confidential' => [
        'title' => 'Tier 2 - Confidential Access',
        'badge' => '🔵 LEVEL 2 (CONFIDENTIAL)',
        'color' => '#00f2fe',
        'perks' => 'Internal databases, team repositories, staging servers'
    ],
    'secret' => [
        'title' => 'Tier 3 - Secret / Restricted',
        'badge' => '🟡 LEVEL 3 (RESTRICTED)',
        'color' => '#ffb800',
        'perks' => 'Production API keys, financial ledgers, R&D vaults'
    ],
    'top_secret' => [
        'title' => 'Tier 4 - Executive Top Secret',
        'badge' => '🔴 LEVEL 4 (TOP SECRET)',
        'color' => '#ff007f',
        'perks' => 'Root infrastructure access, strategic acquisitions, executive board rooms'
    ]
];

$activeProfile = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName    = trim(htmlspecialchars($_POST['full_name'] ?? 'Aria Vance'));
    $empId       = trim(htmlspecialchars($_POST['emp_id'] ?? 'NX-8049-CORP'));
    $department  = trim(htmlspecialchars($_POST['department'] ?? 'Cyber Security & AI'));
    $designation = trim(htmlspecialchars($_POST['designation'] ?? 'Principal Security Architect'));
    $email       = trim(htmlspecialchars($_POST['email'] ?? 'aria.vance@nexuscorp.io'));
    $joinYear    = max(1990, min(2026, intval($_POST['join_year'] ?? 2021)));
    $clearance   = $_POST['clearance_tier'] ?? 'confidential';
    $bio         = trim(htmlspecialchars($_POST['bio'] ?? 'Specializing in zero-trust architecture, threat modeling, and distributed AI safety systems.'));
    $rawSkills   = trim(htmlspecialchars($_POST['skills'] ?? 'Zero-Trust, Cloud Security, Threat Hunting, PHP Engine, Penetration Testing'));
    
    // Parse skills into structured array
    $skillList = array_filter(array_map('trim', explode(',', $rawSkills)));
    if (empty($skillList)) {
        $skillList = ['Enterprise Operations', 'System Design'];
    }

    // Tenure & Seniority Calculation
    $currentYear = 2026;
    $tenureYears = max(0, $currentYear - $joinYear);

    if ($tenureYears >= 10) {
        $seniorityTitle = 'Veteran Principal Staff';
        $tenureBadge = '🏅 10+ Yr Veteran';
    } elseif ($tenureYears >= 5) {
        $seniorityTitle = 'Senior Executive Member';
        $tenureBadge = '⭐ Senior Tier';
    } elseif ($tenureYears >= 2) {
        $seniorityTitle = 'Established Associate';
        $tenureBadge = '🔷 Mid-Tier';
    } else {
        $seniorityTitle = 'Newly Onboarded Specialist';
        $tenureBadge = '🌱 Junior Onboarding';
    }

    $clearanceData = $clearanceTiers[$clearance] ?? $clearanceTiers['confidential'];

    // Generate Initials Avatar Text
    $nameParts = explode(' ', $fullName);
    $initials = strtoupper(substr($nameParts[0] ?? 'N', 0, 1) . substr($nameParts[1] ?? 'X', 0, 1));

    $activeProfile = [
        'badge_id'        => 'BADGE-' . strtoupper(substr(md5($empId . time()), 0, 8)),
        'full_name'       => $fullName,
        'emp_id'          => $empId,
        'department'      => $department,
        'designation'     => $designation,
        'email'           => $email,
        'join_year'       => $joinYear,
        'tenure_years'    => $tenureYears,
        'seniority_title' => $seniorityTitle,
        'tenure_badge'    => $tenureBadge,
        'clearance_key'   => $clearance,
        'clearance_title' => $clearanceData['title'],
        'clearance_badge' => $clearanceData['badge'],
        'clearance_color' => $clearanceData['color'],
        'clearance_perks' => $clearanceData['perks'],
        'bio'             => $bio,
        'skills'          => $skillList,
        'initials'        => $initials,
        'issued_at'       => date('H:i | M d, Y')
    ];

    // Store in Session Staff Directory (keep top 6 profiles)
    array_unshift($_SESSION['staff_directory'], $activeProfile);
    $_SESSION['staff_directory'] = array_slice($_SESSION['staff_directory'], 0, 6);
}

// Clear Session Directory Action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['staff_directory'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corporate Staff Profile Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="portal-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <span class="header-pill">Enterprise Identity & Security Portal</span>
            <h1>Corporate Staff Profile Portal</h1>
            <p>Generate digital credentials, compute security clearance parameters, and manage employee profile records.</p>
        </header>

        <!-- DIGITAL STAFF BADGE DISPLAY -->
        <?php if ($activeProfile): ?>
            <div class="badge-card" style="border-top-color: <?php echo $activeProfile['clearance_color']; ?>;">
                <div class="badge-top">
                    <div class="corp-branding">
                        <span class="corp-symbol">⚡ NEXUS ENTERPRISE</span>
                        <h2>Digital Credential Pass</h2>
                    </div>
                    <span class="clearance-tag" style="background: <?php echo $activeProfile['clearance_color']; ?>22; color: <?php echo $activeProfile['clearance_color']; ?>; border-color: <?php echo $activeProfile['clearance_color']; ?>88;">
                        <?php echo $activeProfile['clearance_badge']; ?>
                    </span>
                </div>

                <div class="profile-hero">
                    <div class="avatar-circle" style="background: linear-gradient(135deg, <?php echo $activeProfile['clearance_color']; ?>, #7928ca);">
                        <span><?php echo $activeProfile['initials']; ?></span>
                    </div>
                    <div class="hero-info">
                        <h3><?php echo htmlspecialchars($activeProfile['full_name']); ?></h3>
                        <p class="designation-text"><?php echo htmlspecialchars($activeProfile['designation']); ?></p>
                        <p class="dept-text">🏬 Department: <strong><?php echo htmlspecialchars($activeProfile['department']); ?></strong></p>
                    </div>
                </div>

                <!-- BADGE METRICS GRID -->
                <div class="badge-metrics">
                    <div class="bm-box">
                        <span>Staff ID</span>
                        <strong><?php echo htmlspecialchars($activeProfile['emp_id']); ?></strong>
                    </div>
                    <div class="bm-box">
                        <span>Company Tenure</span>
                        <strong class="text-cyan"><?php echo $activeProfile['tenure_years']; ?> Years (Joined <?php echo $activeProfile['join_year']; ?>)</strong>
                    </div>
                    <div class="bm-box">
                        <span>Seniority Status</span>
                        <strong><?php echo $activeProfile['tenure_badge']; ?></strong>
                    </div>
                    <div class="bm-box">
                        <span>Official Email</span>
                        <strong class="email-truncate"><?php echo htmlspecialchars($activeProfile['email']); ?></strong>
                    </div>
                </div>

                <!-- BIO & SKILLS -->
                <div class="bio-section">
                    <h4>Professional Bio Statement</h4>
                    <p><?php echo htmlspecialchars($activeProfile['bio']); ?></p>
                </div>

                <div class="skills-section">
                    <h4>Verified Skill Matrix & Competencies</h4>
                    <div class="skills-flex">
                        <?php foreach ($activeProfile['skills'] as $skill): ?>
                            <span class="skill-pill">🔹 <?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="perks-banner" style="border-color: <?php echo $activeProfile['clearance_color']; ?>55;">
                    <span class="p-icon">🔒</span>
                    <div>
                        <strong>Security Clearance Privileges:</strong>
                        <p><?php echo htmlspecialchars($activeProfile['clearance_perks']); ?></p>
                    </div>
                </div>

                <div class="badge-footer">
                    <span>Cryptographic Identity Token: <code><?php echo $activeProfile['badge_id']; ?></code></span>
                    <span>Verified: <?php echo $activeProfile['issued_at']; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- PROFILE FORM -->
        <form action="index.php" method="POST" class="profile-form">
            <span class="form-section-title">1. Employee Personal & Corporate Data</span>
            
            <div class="form-grid">
                <div class="field-group">
                    <label for="full_name">Staff Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? 'Aria Vance'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="emp_id">Employee Corporate ID</label>
                    <input type="text" id="emp_id" name="emp_id" value="<?php echo htmlspecialchars($_POST['emp_id'] ?? 'NX-8049-CORP'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="designation">Official Job Title / Designation</label>
                    <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($_POST['designation'] ?? 'Principal Security Architect'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="department">Assigned Department</label>
                    <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($_POST['department'] ?? 'Cyber Security & AI Safety'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="email">Corporate Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? 'aria.vance@nexuscorp.io'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="join_year">Onboarding / Join Year</label>
                    <input type="number" id="join_year" name="join_year" min="1990" max="2026" value="<?php echo htmlspecialchars($_POST['join_year'] ?? 2021); ?>" required>
                </div>

                <div class="field-group span-2">
                    <label>Assigned Security Clearance Tier</label>
                    <div class="clearance-options">
                        <?php 
                        $selectedClearance = $_POST['clearance_tier'] ?? 'confidential';
                        foreach ($clearanceTiers as $cKey => $cVal): 
                        ?>
                            <label class="c-option">
                                <input type="radio" name="clearance_tier" value="<?php echo $cKey; ?>" <?php echo ($selectedClearance === $cKey) ? 'checked' : ''; ?>>
                                <span class="c-dot" style="background: <?php echo $cVal['color']; ?>;"></span>
                                <span><?php echo $cVal['title']; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="field-group span-2">
                    <label for="bio">Professional Summary / Bio</label>
                    <textarea id="bio" name="bio" rows="3" required><?php echo htmlspecialchars($_POST['bio'] ?? 'Specializing in zero-trust architecture, threat modeling, and distributed AI safety systems.'); ?></textarea>
                </div>

                <div class="field-group span-2">
                    <label for="skills">Skills Matrix (Comma Separated)</label>
                    <input type="text" id="skills" name="skills" value="<?php echo htmlspecialchars($_POST['skills'] ?? 'Zero-Trust, Cloud Security, Threat Hunting, PHP Engine, Penetration Testing'); ?>" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">Generate Digital Badge & Save Profile</button>
        </form>

        <!-- STAFF DIRECTORY SESSION HISTORY -->
        <?php if (!empty($_SESSION['staff_directory'])): ?>
            <div class="directory-panel">
                <div class="directory-head">
                    <span>Active Corporate Directory (Recent Session Profiles)</span>
                    <a href="index.php?action=clear" class="clear-btn">Clear Directory</a>
                </div>
                <div class="directory-grid">
                    <?php foreach ($_SESSION['staff_directory'] as $staff): ?>
                        <div class="directory-card">
                            <div class="d-top">
                                <div class="d-avatar" style="background: <?php echo $staff['clearance_color']; ?>;">
                                    <?php echo $staff['initials']; ?>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($staff['full_name']); ?></strong>
                                    <span class="d-title"><?php echo htmlspecialchars($staff['designation']); ?></span>
                                </div>
                            </div>
                            <div class="d-bottom">
                                <span class="d-badge" style="color: <?php echo $staff['clearance_color']; ?>; border-color: <?php echo $staff['clearance_color']; ?>66;">
                                    <?php echo $staff['clearance_badge']; ?>
                                </span>
                                <span class="d-dept"><?php echo htmlspecialchars($staff['department']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>