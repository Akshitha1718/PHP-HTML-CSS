<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinical Patient Triage & Intake System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="triage-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- DIGITAL TRIAGE PASS VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Patient Registered</span>
                <h2>Digital Triage Pass</h2>
                <p>Intake Ref: #MED-<?php echo rand(100000, 999999); ?> | <?php echo date("H:i T"); ?></p>
            </div>

            <?php
            $patientName = htmlspecialchars(trim($_POST['patientName'] ?? 'Patient'));
            $age         = intval($_POST['age'] ?? 0);
            $bloodGroup  = htmlspecialchars(trim($_POST['bloodGroup'] ?? 'Unknown'));
            $symptom     = htmlspecialchars(trim($_POST['symptom'] ?? 'General Checkup'));
            $urgency     = htmlspecialchars(trim($_POST['urgency'] ?? 'routine'));

            // Triage Evaluation Logic
            if ($urgency === 'emergency' || $age >= 75) {
                $priorityCode = "PRIORITY 1 - IMMEDIATE";
                $dept = "Emergency Trauma Unit (ETU)";
                $waitTime = "Immediate (< 5 mins)";
                $statusColor = "status-p1";
            } elseif ($urgency === 'urgent') {
                $priorityCode = "PRIORITY 2 - ELEVATED";
                $dept = "Urgent Care & Diagnostics";
                $waitTime = "15 - 30 Minutes";
                $statusColor = "status-p2";
            } else {
                $priorityCode = "PRIORITY 3 - STANDARD";
                $dept = "General Outpatient Clinic";
                $waitTime = "45 - 60 Minutes";
                $statusColor = "status-p3";
            }
            ?>

            <div class="patient-banner">
                <div><span>Patient:</span> <strong><?php echo $patientName; ?></strong></div>
                <div><span>Age:</span> <strong><?php echo $age; ?> yrs</strong></div>
                <div><span>Blood Group:</span> <strong><?php echo $bloodGroup; ?></strong></div>
            </div>

            <div class="triage-details">
                <div class="detail-row">
                    <span class="detail-label">Chief Complaint / Symptom</span>
                    <strong class="detail-val"><?php echo $symptom; ?></strong>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Assigned Department</span>
                    <strong class="detail-val"><?php echo $dept; ?></strong>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Estimated Care Window</span>
                    <strong class="detail-val"><?php echo $waitTime; ?></strong>
                </div>
            </div>

            <div class="priority-badge <?php echo $statusColor; ?>">
                <strong>Clinical Status:</strong> <?php echo $priorityCode; ?>
            </div>

            <a href="index.php" class="back-btn">&larr; Register Another Patient</a>

        <?php else: ?>
            <!-- PATIENT INTAKE FORM VIEW -->
            <div class="card-header">
                <span class="badge">Patient Triage v8.0</span>
                <h2>Clinical Intake Portal</h2>
                <p>Input patient details and symptom indicators for priority triage</p>
            </div>

            <form action="index.php" method="POST" class="triage-form">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="patientName">Full Name</label>
                        <input type="text" id="patientName" name="patientName" placeholder="e.g. Eleanor Vance" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" min="0" max="120" placeholder="e.g. 34" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="bloodGroup">Blood Group</label>
                        <select id="bloodGroup" name="bloodGroup" required>
                            <option value="" disabled selected>Select Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="urgency">Initial Assessment</label>
                        <select id="urgency" name="urgency" required>
                            <option value="routine">Routine Checkup</option>
                            <option value="urgent">Urgent Symptoms</option>
                            <option value="emergency">Critical Emergency</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="symptom">Primary Symptom / Notes</label>
                    <input type="text" id="symptom" name="symptom" placeholder="e.g. Acute abdominal pain, high fever" required>
                </div>

                <button type="submit" class="submit-btn">Process Triage & Issue Pass</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>