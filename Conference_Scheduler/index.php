<?php
session_start();

if (!isset($_SESSION['conference_bookings'])) {
    $_SESSION['conference_bookings'] = [];
}

$activeBooking = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parentName   = trim(htmlspecialchars($_POST['parent_name'] ?? 'Sarah Jenkins'));
    $studentName  = trim(htmlspecialchars($_POST['student_name'] ?? 'Leo Jenkins'));
    $gradeLevel   = trim(htmlspecialchars($_POST['grade_level'] ?? 'Grade 10 - STEM Track'));
    $teacherName  = trim(htmlspecialchars($_POST['teacher_name'] ?? 'Dr. Marcus Vance (Mathematics)'));
    $meetingDate  = trim(htmlspecialchars($_POST['meeting_date'] ?? date('Y-m-d', strtotime('+3 days'))));
    $timeSlot     = trim(htmlspecialchars($_POST['time_slot'] ?? '03:30 PM - 03:50 PM'));
    $format       = trim(htmlspecialchars($_POST['format'] ?? 'Virtual Video Conference'));
    $primaryFocus = trim(htmlspecialchars($_POST['primary_focus'] ?? 'Academic Progress & AP Placement'));

    // Unique Confirmation Pass
    $bookingId = 'PTC-' . strtoupper(substr(md5($parentName . $timeSlot . time()), 0, 6));
    
    // Assign Venue or Digital Meeting Link
    if (strpos($format, 'Virtual') !== false) {
        $locationDetails = 'https://meet.academiaportal.edu/ptc/' . strtolower(substr($bookingId, 4));
        $formatBadge = '#00d2d3'; // Electric Mint
        $badgeBg = 'rgba(0, 210, 211, 0.15)';
        $locationLabel = 'Virtual Room Access Link';
    } else {
        $locationDetails = 'Main Campus • West Wing, Room ' . rand(102, 215);
        $formatBadge = '#ff6b6b'; // Sunset Coral
        $badgeBg = 'rgba(255, 107, 107, 0.15)';
        $locationLabel = 'Campus Venue Location';
    }

    $activeBooking = [
        'booking_id'       => $bookingId,
        'parent_name'      => $parentName,
        'student_name'     => $studentName,
        'grade_level'      => $gradeLevel,
        'teacher_name'     => $teacherName,
        'meeting_date'     => date('l, M j, Y', strtotime($meetingDate)),
        'time_slot'        => $timeSlot,
        'format'           => $format,
        'location_label'   => $locationLabel,
        'location_details' => $locationDetails,
        'format_badge'     => $formatBadge,
        'badge_bg'         => $badgeBg,
        'primary_focus'    => $primaryFocus,
        'booked_at'        => date('H:i | M d, Y')
    ];

    // Store in Session
    array_unshift($_SESSION['conference_bookings'], $activeBooking);
    $_SESSION['conference_bookings'] = array_slice($_SESSION['conference_bookings'], 0, 5);
}

// Clear History Handler
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['conference_bookings'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent-Teacher Conference Scheduler</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="portal-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <span class="header-pill">Task 28 • Academic Communication Portal</span>
            <h1>Parent-Teacher Conference Scheduler</h1>
            <p>Coordinate appointments, assign meeting venues or virtual video links, and manage educator consultation slots.</p>
        </header>

        <!-- ACTIVE BOOKING CONFIRMATION CARD -->
        <?php if ($activeBooking): ?>
            <div class="pass-card" style="border-top-color: <?php echo $activeBooking['format_badge']; ?>;">
                <div class="card-top">
                    <div>
                        <span class="pass-id">APPOINTMENT PASS: <?php echo $activeBooking['booking_id']; ?></span>
                        <h2>Conference Confirmed</h2>
                        <p class="pass-meta"><?php echo htmlspecialchars($activeBooking['parent_name']); ?> w/ <?php echo htmlspecialchars($activeBooking['teacher_name']); ?></p>
                    </div>
                    <span class="format-badge" style="background: <?php echo $activeBooking['badge_bg']; ?>; color: <?php echo $activeBooking['format_badge']; ?>; border-color: <?php echo $activeBooking['format_badge']; ?>88;">
                        <?php echo $activeBooking['format']; ?>
                    </span>
                </div>

                <!-- DETAILS GRID -->
                <div class="details-grid">
                    <div class="d-box">
                        <span class="d-title">Student Profile</span>
                        <strong class="d-big"><?php echo htmlspecialchars($activeBooking['student_name']); ?></strong>
                        <span class="d-sub"><?php echo htmlspecialchars($activeBooking['grade_level']); ?></span>
                    </div>

                    <div class="d-box">
                        <span class="d-title">Date & Time Slot</span>
                        <strong class="d-big text-amber"><?php echo $activeBooking['time_slot']; ?></strong>
                        <span class="d-sub"><?php echo $activeBooking['meeting_date']; ?></span>
                    </div>

                    <div class="d-box span-2">
                        <span class="d-title"><?php echo $activeBooking['location_label']; ?></span>
                        <strong class="d-big text-mint"><?php echo htmlspecialchars($activeBooking['location_details']); ?></strong>
                        <span class="d-sub">Primary Topic: <?php echo htmlspecialchars($activeBooking['primary_focus']); ?></span>
                    </div>
                </div>

                <div class="card-footer">
                    <span>Calendar Sync: <strong class="text-mint">Dispatched via Email</strong></span>
                    <span>Booked: <?php echo $activeBooking['booked_at']; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- SCHEDULING FORM -->
        <form action="index.php" method="POST" class="schedule-form">
            <span class="form-title">Schedule a Consultation Slot</span>
            
            <div class="form-grid">
                <div class="field-group">
                    <label for="parent_name">Parent / Guardian Name</label>
                    <input type="text" id="parent_name" name="parent_name" value="<?php echo htmlspecialchars($_POST['parent_name'] ?? 'Sarah Jenkins'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="student_name">Student Full Name</label>
                    <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($_POST['student_name'] ?? 'Leo Jenkins'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="grade_level">Grade / Class Track</label>
                    <select id="grade_level" name="grade_level" class="select-input">
                        <option value="Grade 9 - Freshman Core">Grade 9 - Freshman Core</option>
                        <option value="Grade 10 - STEM Track" selected>Grade 10 - STEM Track</option>
                        <option value="Grade 11 - Humanities & Arts">Grade 11 - Humanities & Arts</option>
                        <option value="Grade 12 - Advanced Placement">Grade 12 - Advanced Placement</option>
                    </select>
                </div>

                <div class="field-group">
                    <label for="teacher_name">Select Educator / Faculty</label>
                    <select id="teacher_name" name="teacher_name" class="select-input">
                        <option value="Dr. Marcus Vance (Mathematics)">Dr. Marcus Vance (Mathematics)</option>
                        <option value="Prof. Elena Rostova (Physics & Robotics)">Prof. Elena Rostova (Physics & Robotics)</option>
                        <option value="Ms. Clara Oswald (English Literature)">Ms. Clara Oswald (English Literature)</option>
                        <option value="Mr. David Sterling (World History)">Mr. David Sterling (World History)</option>
                    </select>
                </div>

                <div class="field-group">
                    <label for="meeting_date">Preferred Date</label>
                    <input type="date" id="meeting_date" name="meeting_date" value="<?php echo $_POST['meeting_date'] ?? date('Y-m-d', strtotime('+3 days')); ?>" required>
                </div>

                <div class="field-group">
                    <label for="time_slot">Available Time Window</label>
                    <select id="time_slot" name="time_slot" class="select-input">
                        <option value="02:00 PM - 02:20 PM">02:00 PM - 02:20 PM</option>
                        <option value="02:30 PM - 02:50 PM">02:30 PM - 02:50 PM</option>
                        <option value="03:00 PM - 03:20 PM">03:00 PM - 03:20 PM</option>
                        <option value="03:30 PM - 03:50 PM" selected>03:30 PM - 03:50 PM</option>
                        <option value="04:00 PM - 04:20 PM">04:00 PM - 04:20 PM</option>
                    </select>
                </div>

                <div class="field-group">
                    <label for="format">Conference Format</label>
                    <select id="format" name="format" class="select-input">
                        <option value="Virtual Video Conference">Virtual Video Conference</option>
                        <option value="In-Person (Campus Classroom)">In-Person (Campus Classroom)</option>
                    </select>
                </div>

                <div class="field-group">
                    <label for="primary_focus">Primary Focus Area</label>
                    <input type="text" id="primary_focus" name="primary_focus" value="<?php echo htmlspecialchars($_POST['primary_focus'] ?? 'Academic Progress & AP Placement'); ?>" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">Confirm Conference Booking</button>
        </form>

        <!-- BOOKING HISTORY -->
        <?php if (!empty($_SESSION['conference_bookings'])): ?>
            <div class="history-panel">
                <div class="history-head">
                    <span>Recent Scheduled Sessions (<?php echo count($_SESSION['conference_bookings']); ?>)</span>
                    <a href="index.php?action=clear" class="clear-btn">Clear Sessions</a>
                </div>
                <div class="history-grid">
                    <?php foreach ($_SESSION['conference_bookings'] as $booking): ?>
                        <div class="history-card">
                            <div class="h-top">
                                <strong><?php echo htmlspecialchars($booking['student_name']); ?></strong>
                                <span class="h-badge" style="color: <?php echo $booking['format_badge']; ?>; border-color: <?php echo $booking['format_badge']; ?>66;">
                                    <?php echo $booking['time_slot']; ?>
                                </span>
                            </div>
                            <span class="h-sub"><?php echo htmlspecialchars($booking['teacher_name']); ?> • <?php echo $booking['meeting_date']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>