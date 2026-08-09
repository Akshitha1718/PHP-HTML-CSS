<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Student Dossier Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dossier-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- DOSSIER PROFILE VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Dossier Verified</span>
                <h2>Student Academic Profile</h2>
                <p>ID: #<?php echo strtoupper(htmlspecialchars($_POST['studentId'] ?? 'STD-0000')); ?> | Verified Record</p>
            </div>

            <?php
            $fullName  = htmlspecialchars(trim($_POST['fullName'] ?? 'Student Name'));
            $studentId = htmlspecialchars(trim($_POST['studentId'] ?? 'STD-0000'));
            $major     = htmlspecialchars(trim($_POST['major'] ?? 'Computer Science'));
            $yearLevel = htmlspecialchars(trim($_POST['yearLevel'] ?? 'Senior'));
            $gpa       = floatval($_POST['gpa'] ?? 0.0);
            $bio       = htmlspecialchars(trim($_POST['bio'] ?? ''));
            $rawSkills = htmlspecialchars(trim($_POST['skills'] ?? ''));

            // Skill parsing
            $skillsArray = array_filter(array_map('trim', explode(',', $rawSkills)));

            // GPA Honors Classification
            if ($gpa >= 3.8) {
                $honors = "Summa Cum Laude";
                $honorClass = "honor-gold";
            } elseif ($gpa >= 3.5) {
                $honors = "Magna Cum Laude";
                $honorClass = "honor-silver";
            } elseif ($gpa >= 3.0) {
                $honors = "Dean's List";
                $honorClass = "honor-bronze";
            } else {
                $honors = "Good Academic Standing";
                $honorClass = "honor-standard";
            }
            ?>

            <div class="profile-header-box">
                <div class="avatar-circle">
                    <?php 
                        $words = explode(' ', $fullName);
                        $initials = strtoupper(substr($words[0] ?? 'S', 0, 1) . substr($words[1] ?? 'N', 0, 1));
                        echo $initials;
                    ?>
                </div>
                <div class="profile-title">
                    <h3><?php echo $fullName; ?></h3>
                    <p><?php echo $major; ?> &bull; <?php echo $yearLevel; ?></p>
                </div>
            </div>

            <div class="metrics-grid">
                <div class="metric-box">
                    <span>Cumulative GPA</span>
                    <h3 class="highlight-gpa"><?php echo number_format($gpa, 2); ?></h3>
                </div>
                <div class="metric-box">
                    <span>Honor Status</span>
                    <span class="honor-tag <?php echo $honorClass; ?>"><?php echo $honors; ?></span>
                </div>
            </div>

            <?php if (!empty($bio)): ?>
                <div class="bio-box">
                    <span>Biography / Academic Statement</span>
                    <p>"<?php echo $bio; ?>"</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($skillsArray)): ?>
                <div class="skills-container">
                    <span class="skills-label">Key Competencies & Skills</span>
                    <div class="tags-wrapper">
                        <?php foreach ($skillsArray as $skill): ?>
                            <span class="skill-tag"><?php echo $skill; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="dossier-meta">
                <div class="meta-row">
                    <span>Student Registration ID:</span>
                    <strong><?php echo $studentId; ?></strong>
                </div>
                <div class="meta-row">
                    <span>Academic Year:</span>
                    <strong><?php echo $yearLevel; ?> Year</strong>
                </div>
            </div>

            <a href="index.php" class="back-btn">&larr; Generate Another Dossier</a>

        <?php else: ?>
            <!-- DOSSIER INPUT FORM -->
            <div class="card-header">
                <span class="badge">Academic Portal v12.0</span>
                <h2>Student Dossier Builder</h2>
                <p>Register student credentials to compile an executive academic profile</p>
            </div>

            <form action="index.php" method="POST" class="dossier-form">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" placeholder="e.g. Maya Lin" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="studentId">Student ID</label>
                        <input type="text" id="studentId" name="studentId" placeholder="e.g. CS-9021" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="major">Department / Major</label>
                        <select id="major" name="major" required>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Data Science & AI">Data Science & AI</option>
                            <option value="Electrical Engineering">Electrical Engineering</option>
                            <option value="Business Analytics">Business Analytics</option>
                            <option value="Mechanical Engineering">Mechanical Engineering</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="yearLevel">Academic Year</label>
                        <select id="yearLevel" name="yearLevel" required>
                            <option value="Freshman">Freshman</option>
                            <option value="Sophomore">Sophomore</option>
                            <option value="Junior">Junior</option>
                            <option value="Senior" selected>Senior</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gpa">Cumulative GPA (0.00 - 4.00)</label>
                    <input type="number" id="gpa" name="gpa" min="0.0" max="4.0" step="0.01" placeholder="3.85" required>
                </div>

                <div class="form-group">
                    <label for="skills">Key Skills (Comma-separated)</label>
                    <input type="text" id="skills" name="skills" placeholder="e.g. Python, SQL, Machine Learning, UI Design" required>
                </div>

                <div class="form-group">
                    <label for="bio">Biography / Research Statement</label>
                    <textarea id="bio" name="bio" rows="3" placeholder="Brief statement about your academic focus or research interests..."></textarea>
                </div>

                <button type="submit" class="submit-btn">Compile & Generate Dossier</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>