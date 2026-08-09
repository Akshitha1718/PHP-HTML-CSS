<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elective Course Enrollment System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="enroll-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- ENROLLMENT TICKET VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Registration Confirmed</span>
                <h2>Course Pass</h2>
                <p>Pass ID: #ELC-<?php echo strtoupper(substr(md5(uniqid()), 0, 8)); ?></p>
            </div>

            <?php
            $studentName = htmlspecialchars(trim($_POST['studentName'] ?? 'Student'));
            $studentId   = htmlspecialchars(trim($_POST['studentId'] ?? 'N/A'));
            $track       = htmlspecialchars(trim($_POST['track'] ?? 'General'));
            $primary     = htmlspecialchars(trim($_POST['primaryElective'] ?? 'None'));
            $secondary   = htmlspecialchars(trim($_POST['secondaryElective'] ?? 'None'));
            $gpa         = floatval($_POST['gpa'] ?? 0.0);

            // Course mapping and credits
            $credits = [
                "Advanced Cloud Architecture" => 4,
                "Neural Networks & Deep Learning" => 4,
                "Quantum Computing Fundamentals" => 3,
                "UI/UX Design Systems" => 3,
                "Blockchain & Smart Contracts" => 3,
                "Ethical Hacking & Forensics" => 4
            ];

            $primCredits = $credits[$primary] ?? 3;
            $secCredits  = $credits[$secondary] ?? 3;
            $totalCredits = $primCredits + $secCredits;

            $isEligible = ($gpa >= 3.0);
            $approvalStatus = $isEligible ? "Approved & Seat Reserved" : "Conditional Approval (Pending Advisor Review)";
            $badgeClass = $isEligible ? "status-approved" : "status-pending";
            ?>

            <div class="ticket-header">
                <div><span>Student Name:</span> <strong><?php echo $studentName; ?></strong></div>
                <div><span>Student ID:</span> <strong><?php echo $studentId; ?></strong></div>
            </div>

            <div class="ticket-body">
                <div class="course-item">
                    <span class="course-type">Primary Elective (4 Cr)</span>
                    <strong class="course-title"><?php echo $primary; ?></strong>
                </div>
                <div class="course-item">
                    <span class="course-type">Secondary Elective (3 Cr)</span>
                    <strong class="course-title"><?php echo $secondary; ?></strong>
                </div>
            </div>

            <div class="ticket-summary">
                <div class="sum-box">
                    <span>Academic Track</span>
                    <strong><?php echo $track; ?></strong>
                </div>
                <div class="sum-box">
                    <span>Prior GPA</span>
                    <strong><?php echo number_format($gpa, 2); ?></strong>
                </div>
                <div class="sum-box">
                    <span>Total Credits</span>
                    <strong><?php echo $totalCredits; ?> Credits</strong>
                </div>
            </div>

            <div class="status-banner <?php echo $badgeClass; ?>">
                <strong>Status:</strong> <?php echo $approvalStatus; ?>
            </div>

            <a href="index.php" class="back-btn">&larr; Register Another Student</a>

        <?php else: ?>
            <!-- SELECTION FORM VIEW -->
            <div class="card-header">
                <span class="badge">Semester V Electives</span>
                <h2>Course Enrollment</h2>
                <p>Choose primary & secondary specialization modules</p>
            </div>

            <form action="index.php" method="POST" class="enroll-form">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="studentName">Full Name</label>
                        <input type="text" id="studentName" name="studentName" placeholder="e.g. Maya Lin" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="studentId">Student ID</label>
                        <input type="text" id="studentId" name="studentId" placeholder="24ST891" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="track">Specialization Stream</label>
                        <select id="track" name="track" required>
                            <option value="" disabled selected>Select Stream</option>
                            <option value="Artificial Intelligence">Artificial Intelligence</option>
                            <option value="Cloud Engineering">Cloud Engineering</option>
                            <option value="Cyber Security">Cyber Security</option>
                            <option value="Human-Computer Interaction">Human-Computer Interaction</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="gpa">Current CGPA</label>
                        <input type="number" id="gpa" name="gpa" min="0.0" max="4.0" step="0.01" placeholder="e.g. 3.65" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="primaryElective">Primary Elective Module</label>
                    <select id="primaryElective" name="primaryElective" required>
                        <option value="" disabled selected>Select Primary Elective (4 Credits)</option>
                        <option value="Advanced Cloud Architecture">Advanced Cloud Architecture</option>
                        <option value="Neural Networks & Deep Learning">Neural Networks & Deep Learning</option>
                        <option value="Ethical Hacking & Forensics">Ethical Hacking & Forensics</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="secondaryElective">Secondary Elective Module</label>
                    <select id="secondaryElective" name="secondaryElective" required>
                        <option value="" disabled selected>Select Secondary Elective (3 Credits)</option>
                        <option value="Quantum Computing Fundamentals">Quantum Computing Fundamentals</option>
                        <option value="UI/UX Design Systems">UI/UX Design Systems</option>
                        <option value="Blockchain & Smart Contracts">Blockchain & Smart Contracts</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Lock Choices & Issue Pass</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>