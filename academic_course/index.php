<?php
$courses = [
    [
        "badge" => "B.Tech Specialization",
        "title" => "Artificial Intelligence & ML",
        "instructor" => "Dr. Sarah Jenkins",
        "desc" => "Master neural networks, computer vision, deep learning, and predictive modeling algorithms.",
        "duration" => "8 Semesters",
        "theme" => "cyan-theme"
    ],
    [
        "badge" => "Professional Stream",
        "title" => "Cybersecurity & Defense",
        "instructor" => "Prof. Marcus Vance",
        "desc" => "Learn ethical hacking, network forensics, cryptography, and cloud security architecture.",
        "duration" => "8 Semesters",
        "theme" => "emerald-theme"
    ],
    [
        "badge" => "Advanced Degree",
        "title" => "Data Science & Analytics",
        "instructor" => "Dr. Elena Rostova",
        "desc" => "Harness big data pipelines, statistical inference, visualization, and cloud engineering.",
        "duration" => "4 Semesters",
        "theme" => "violet-theme"
    ],
    [
        "badge" => "Core Engineering",
        "title" => "Full-Stack Web Engineering",
        "instructor" => "Prof. David Chen",
        "desc" => "Build scalable web architectures, modern JS frameworks, REST APIs, and database engines.",
        "duration" => "6 Semesters",
        "theme" => "amber-theme"
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Course Showcase Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="portal-wrapper">
        <header class="portal-header">
            <span class="portal-tag">Department Hub</span>
            <h1>Academic Course Showcase</h1>
            <p>Explore cutting-edge undergraduate and postgraduate streams</p>
        </header>

        <section class="course-grid">
            <?php foreach ($courses as $course): ?>
                <div class="course-card <?php echo $course['theme']; ?>">
                    <div class="card-badge"><?php echo $course['badge']; ?></div>
                    <h3><?php echo $course['title']; ?></h3>
                    <p class="instructor">Lead Instructor: <span><?php echo $course['instructor']; ?></span></p>
                    <p class="desc"><?php echo $course['desc']; ?></p>
                    <div class="card-footer">
                        <span class="duration"><?php echo $course['duration']; ?></span>
                        <a href="#" class="card-btn">View Syllabus &rarr;</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <footer class="portal-footer">
            <p>Need guidance? Contact Department Admissions at <strong>admissions@university.edu</strong></p>
        </footer>
    </div>
</body>
</html>