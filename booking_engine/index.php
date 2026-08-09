<?php
session_start();

if (!isset($_SESSION['bookings'])) {
    $_SESSION['bookings'] = [];
}

// Curated Excursion Packages Data
$packages = [
    'alpine_fly' => [
        'id' => 'alpine_fly',
        'name' => 'Alpine Helicopter & Glacier Tour',
        'location' => 'Interlaken, Switzerland',
        'duration' => '3 Hours',
        'price' => 380,
        'tag' => 'High Adventure',
        'color' => '#ff2a75',
        'desc' => 'Fly over snow-capped peaks with a dramatic glacier landing and champagne toast.'
    ],
    'coral_dive' => [
        'name' => 'Tropical Coral Reef Snorkeling',
        'location' => 'Bora Bora, French Polynesia',
        'duration' => '5 Hours',
        'price' => 195,
        'tag' => 'Water Sport',
        'color' => '#00e5ff',
        'desc' => 'Explore vibrant marine sanctuaries guided by marine biologists with full gear provided.'
    ],
    'desert_safari' => [
        'name' => 'Sunset Desert Dune Buggy Safari',
        'location' => 'Dubai, United Arab Emirates',
        'duration' => '6 Hours',
        'price' => 230,
        'tag' => 'Bestseller',
        'color' => '#ffb800',
        'desc' => 'High-octane dune bashing followed by traditional bedouin campfire BBQ dinner.'
    ],
    'rainforest_trek' => [
        'name' => 'Canopy Zipline & Rainforest Trek',
        'location' => 'Monteverde, Costa Rica',
        'duration' => '4 Hours',
        'price' => 145,
        'tag' => 'Eco Tour',
        'color' => '#10b981',
        'desc' => 'Soar across 11 canopy cables and discover rare cloud forest flora and fauna.'
    ]
];

// Experience Add-ons
$addonsList = [
    'photo' => ['name' => 'Pro Action Photo & Drone Package', 'price' => 50],
    'transport' => ['name' => 'Private Luxury Hotel Pick-up', 'price' => 65],
    'lunch' => ['name' => 'Gourmet Local Tasting Lunch', 'price' => 40],
    'vip' => ['name' => 'Fast-Track VIP Access & Lounge', 'price' => 55]
];

$activeBooking = null;

// Handle Form Submission & Calculation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pkgKey = $_POST['package'] ?? 'alpine_fly';
    if (!isset($packages[$pkgKey])) {
        $pkgKey = 'alpine_fly';
    }
    $selectedPkg = $packages[$pkgKey];

    $travelDate  = $_POST['travel_date'] ?? date('Y-m-d', strtotime('+3 days'));
    $adults      = max(1, min(20, intval($_POST['adults'] ?? 1)));
    $children    = max(0, min(10, intval($_POST['children'] ?? 0)));
    $guestName   = trim(htmlspecialchars($_POST['guest_name'] ?? 'Guest Traveler'));
    $guestEmail  = trim(htmlspecialchars($_POST['guest_email'] ?? 'guest@example.com'));

    $selectedAddons = $_POST['addons'] ?? [];
    $addonsTotalPerPerson = 0;
    $addonsDetails = [];

    foreach ($selectedAddons as $addonKey) {
        if (isset($addonsList[$addonKey])) {
            $addonsTotalPerPerson += $addonsList[$addonKey]['price'];
            $addonsDetails[] = $addonsList[$addonKey]['name'];
        }
    }

    $totalGuests = $adults + $children;
    $adultTotal  = $selectedPkg['price'] * $adults;
    $childTotal  = ($selectedPkg['price'] * 0.70) * $children; // 30% discount for children
    $addonsTotal = $addonsTotalPerPerson * $totalGuests;

    $subtotal   = $adultTotal + $childTotal + $addonsTotal;
    $taxFee     = $subtotal * 0.10; // 10% Local Eco-Tax & Booking Fee
    $grandTotal = $subtotal + $taxFee;

    $bookingId = 'EXC-' . strtoupper(substr(md5(uniqid()), 0, 8));

    $activeBooking = [
        'id'          => $bookingId,
        'package'     => $selectedPkg['name'],
        'location'    => $selectedPkg['location'],
        'duration'    => $selectedPkg['duration'],
        'date'        => date('M d, Y', strtotime($travelDate)),
        'adults'      => $adults,
        'children'    => $children,
        'guest_name'  => $guestName,
        'guest_email' => $guestEmail,
        'addons'      => $addonsDetails,
        'subtotal'    => $subtotal,
        'tax'         => $taxFee,
        'total'       => $grandTotal,
        'color'       => $selectedPkg['color'],
        'created_at'  => date('H:i | M d, Y')
    ];

    array_unshift($_SESSION['bookings'], $activeBooking);
    $_SESSION['bookings'] = array_slice($_SESSION['bookings'], 0, 5);
}

// Clear History Action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['bookings'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excursion Package Booking Engine</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="engine-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <span class="header-tag">Wanderlust Engine v2.0</span>
            <h1>Curated Excursion Expeditions</h1>
            <p>Customize your journey, select add-ons, and reserve live dynamic adventure packages.</p>
        </header>

        <!-- CONFIRMATION VOUCHER DISPLAY -->
        <?php if ($activeBooking): ?>
            <div class="voucher-card" style="border-top-color: <?php echo $activeBooking['color']; ?>;">
                <div class="voucher-badge">Booking Confirmed</div>
                <div class="voucher-header">
                    <div>
                        <h2><?php echo htmlspecialchars($activeBooking['package']); ?></h2>
                        <span class="v-location">📍 <?php echo htmlspecialchars($activeBooking['location']); ?> • ⏱️ <?php echo htmlspecialchars($activeBooking['duration']); ?></span>
                    </div>
                    <div class="v-id">
                        <span>Voucher Ref</span>
                        <strong><?php echo $activeBooking['id']; ?></strong>
                    </div>
                </div>

                <div class="voucher-details-grid">
                    <div class="v-item">
                        <span>Traveler Name</span>
                        <strong><?php echo htmlspecialchars($activeBooking['guest_name']); ?></strong>
                    </div>
                    <div class="v-item">
                        <span>Expedition Date</span>
                        <strong><?php echo $activeBooking['date']; ?></strong>
                    </div>
                    <div class="v-item">
                        <span>Guests</span>
                        <strong><?php echo $activeBooking['adults']; ?> Adults<?php echo $activeBooking['children'] > 0 ? ', ' . $activeBooking['children'] . ' Kids' : ''; ?></strong>
                    </div>
                    <div class="v-item">
                        <span>Email Contact</span>
                        <strong><?php echo htmlspecialchars($activeBooking['guest_email']); ?></strong>
                    </div>
                </div>

                <?php if (!empty($activeBooking['addons'])): ?>
                    <div class="v-addons">
                        <span>Included Extras:</span>
                        <ul>
                            <?php foreach ($activeBooking['addons'] as $addonName): ?>
                                <li>✨ <?php echo htmlspecialchars($addonName); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="v-pricing">
                    <div class="price-row"><span>Subtotal:</span><span>$<?php echo number_format($activeBooking['subtotal'], 2); ?></span></div>
                    <div class="price-row"><span>Eco-Tax & Service Fee (10%):</span><span>$<?php echo number_format($activeBooking['tax'], 2); ?></span></div>
                    <div class="price-row total-row"><span>Grand Total Paid:</span><span class="total-amount">$<?php echo number_format($activeBooking['total'], 2); ?></span></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- BOOKING FORM -->
        <form action="index.php" method="POST" class="booking-form">
            
            <span class="section-title">1. Choose Your Excursion</span>
            <div class="package-grid">
                <?php 
                $defaultPkg = $_POST['package'] ?? 'alpine_fly';
                foreach ($packages as $key => $pkg): 
                    $isSelected = ($defaultPkg === $key);
                ?>
                    <label class="package-card <?php echo $isSelected ? 'selected' : ''; ?>" style="--accent-color: <?php echo $pkg['color']; ?>">
                        <input type="radio" name="package" value="<?php echo $key; ?>" <?php echo $isSelected ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <div class="card-badge" style="background: <?php echo $pkg['color']; ?>;"><?php echo $pkg['tag']; ?></div>
                        <h3><?php echo $pkg['name']; ?></h3>
                        <p class="pkg-loc">📍 <?php echo $pkg['location']; ?> | ⏱️ <?php echo $pkg['duration']; ?></p>
                        <p class="pkg-desc"><?php echo $pkg['desc']; ?></p>
                        <div class="pkg-price">$<?php echo $pkg['price']; ?> <small>/ adult</small></div>
                    </label>
                <?php endforeach; ?>
            </div>

            <span class="section-title">2. Travel Date & Guests</span>
            <div class="inputs-grid">
                <div class="form-group">
                    <label for="guest_name">Full Name</label>
                    <input type="text" id="guest_name" name="guest_name" value="<?php echo htmlspecialchars($_POST['guest_name'] ?? 'Elena Rostova'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="guest_email">Email Address</label>
                    <input type="email" id="guest_email" name="guest_email" value="<?php echo htmlspecialchars($_POST['guest_email'] ?? 'elena@expeditions.com'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="travel_date">Expedition Date</label>
                    <input type="date" id="travel_date" name="travel_date" value="<?php echo $_POST['travel_date'] ?? date('Y-m-d', strtotime('+5 days')); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group guests-counter">
                    <div>
                        <label for="adults">Adults ($)</label>
                        <input type="number" id="adults" name="adults" min="1" max="20" value="<?php echo $_POST['adults'] ?? 2; ?>">
                    </div>
                    <div>
                        <label for="children">Children (-30%)</label>
                        <input type="number" id="children" name="children" min="0" max="10" value="<?php echo $_POST['children'] ?? 1; ?>">
                    </div>
                </div>
            </div>

            <span class="section-title">3. Tailor Your Experience (Add-ons)</span>
            <div class="addons-grid">
                <?php 
                $postedAddons = $_POST['addons'] ?? ['photo', 'transport'];
                foreach ($addonsList as $aKey => $aItem): 
                    $isChecked = in_array($aKey, $postedAddons);
                ?>
                    <label class="addon-card">
                        <input type="checkbox" name="addons[]" value="<?php echo $aKey; ?>" <?php echo $isChecked ? 'checked' : ''; ?>>
                        <div class="addon-info">
                            <strong><?php echo $aItem['name']; ?></strong>
                            <span>+$<?php echo $aItem['price']; ?> per guest</span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-submit">Confirm Excursion Reservation</button>
        </form>

        <!-- RECENT BOOKINGS SESSION HISTORY -->
        <?php if (!empty($_SESSION['bookings'])): ?>
            <div class="history-block">
                <div class="history-header">
                    <span>Recent Session Reservations</span>
                    <a href="index.php?action=clear" class="clear-btn">Clear Vault</a>
                </div>
                <div class="history-list">
                    <?php foreach ($_SESSION['bookings'] as $b): ?>
                        <div class="history-item">
                            <div>
                                <strong><?php echo htmlspecialchars($b['package']); ?></strong>
                                <span><?php echo $b['id']; ?> • <?php echo $b['date']; ?> • <?php echo $b['guest_name']; ?></span>
                            </div>
                            <div class="hist-price">$<?php echo number_format($b['total'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>