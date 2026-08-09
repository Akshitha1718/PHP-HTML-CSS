<?php
session_start();

if (!isset($_SESSION['payroll_history'])) {
    $_SESSION['payroll_history'] = [];
}

$payStub = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empName      = trim(htmlspecialchars($_POST['emp_name'] ?? 'Elena Rostova'));
    $empId        = trim(htmlspecialchars($_POST['emp_id'] ?? 'EMP-2026-9042'));
    $department   = trim(htmlspecialchars($_POST['department'] ?? 'Software Engineering'));
    $designation  = trim(htmlspecialchars($_POST['designation'] ?? 'Lead Systems Architect'));
    
    // Earnings Inputs
    $baseSalary   = max(0, floatval($_POST['base_salary'] ?? 8500));
    $overtimeHrs  = max(0, floatval($_POST['overtime_hours'] ?? 12));
    $overtimeRate = max(0, floatval($_POST['overtime_rate'] ?? 65));
    $housingAllowance   = max(0, floatval($_POST['housing_allowance'] ?? 1200));
    $transportAllowance = max(0, floatval($_POST['transport_allowance'] ?? 450));
    $bonus              = max(0, floatval($_POST['bonus'] ?? 1500));

    // Deduction Parameters
    $taxBracketPct   = max(0, min(50, floatval($_POST['tax_bracket_pct'] ?? 22))); // Income tax %
    $pensionPct      = max(0, min(25, floatval($_POST['pension_pct'] ?? 6)));      // 401k / Pension %
    $healthInsurance = max(0, floatval($_POST['health_insurance'] ?? 320));        // Flat health premium
    $customDeductions= max(0, floatval($_POST['custom_deductions'] ?? 150));       // Misc withholdings

    // Computations
    $overtimePay    = $overtimeHrs * $overtimeRate;
    $totalAllowances= $housingAllowance + $transportAllowance + $bonus;
    $grossEarnings  = $baseSalary + $overtimePay + $totalAllowances;

    // Pension is pre-tax deduction
    $pensionContribution = $baseSalary * ($pensionPct / 100);
    $taxableIncome       = max(0, $grossEarnings - $pensionContribution);
    
    $incomeTax       = $taxableIncome * ($taxBracketPct / 100);
    $totalDeductions = $pensionContribution + $incomeTax + $healthInsurance + $customDeductions;
    $netCompensation = max(0, $grossEarnings - $totalDeductions);

    $effectiveDeductionPct = ($grossEarnings > 0) ? ($totalDeductions / $grossEarnings) * 100 : 0;
    $takeHomeRatio         = ($grossEarnings > 0) ? ($netCompensation / $grossEarnings) * 100 : 0;

    $payStub = [
        'stub_id'              => 'PAY-' . strtoupper(substr(md5(time() . $empId), 0, 8)),
        'emp_name'             => $empName,
        'emp_id'               => $empId,
        'department'           => $department,
        'designation'          => $designation,
        'pay_period'           => date('F Y'),
        'base_salary'          => $baseSalary,
        'overtime_pay'         => $overtimePay,
        'overtime_hrs'         => $overtimeHrs,
        'housing_allowance'    => $housingAllowance,
        'transport_allowance'  => $transportAllowance,
        'bonus'                => $bonus,
        'gross_earnings'       => $grossEarnings,
        'pension_contribution' => $pensionContribution,
        'income_tax'           => $incomeTax,
        'tax_bracket_pct'      => $taxBracketPct,
        'health_insurance'     => $healthInsurance,
        'custom_deductions'    => $customDeductions,
        'total_deductions'     => $totalDeductions,
        'net_compensation'     => $netCompensation,
        'take_home_ratio'      => round($takeHomeRatio, 1),
        'effective_deduction_pct' => round($effectiveDeductionPct, 1),
        'generated_at'         => date('H:i | M d, Y')
    ];

    array_unshift($_SESSION['payroll_history'], $payStub);
    $_SESSION['payroll_history'] = array_slice($_SESSION['payroll_history'], 0, 5);
}

// Clear History Action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['payroll_history'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Net Compensation Calculator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="payroll-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <span class="header-pill">Enterprise Compensation Suite</span>
            <h1>Payroll Net Compensation Calculator</h1>
            <p>Compute gross earnings, progressive withholdings, tax liabilities, and final net remuneration.</p>
        </header>

        <!-- DIGITAL PAY STUB DISPLAY -->
        <?php if ($payStub): ?>
            <div class="paystub-card">
                <div class="stub-header">
                    <div class="company-brand">
                        <span class="brand-badge">⚡ CORP PAYROLL</span>
                        <h2>Itemized Remuneration Statement</h2>
                        <p>Pay Period: <strong><?php echo $payStub['pay_period']; ?></strong></p>
                    </div>
                    <div class="net-payout-box">
                        <span class="net-title">NET TAKE-HOME PAY</span>
                        <span class="net-amount">$<?php echo number_format($payStub['net_compensation'], 2); ?></span>
                        <span class="net-ratio">Retained: <?php echo $payStub['take_home_ratio']; ?>% of Gross</span>
                    </div>
                </div>

                <div class="emp-summary-bar">
                    <div><span>Employee Name</span><strong><?php echo htmlspecialchars($payStub['emp_name']); ?></strong></div>
                    <div><span>Employee ID</span><strong><?php echo htmlspecialchars($payStub['emp_id']); ?></strong></div>
                    <div><span>Department</span><strong><?php echo htmlspecialchars($payStub['department']); ?></strong></div>
                    <div><span>Designation</span><strong><?php echo htmlspecialchars($payStub['designation']); ?></strong></div>
                </div>

                <!-- EARNINGS VS DEDUCTIONS TABLES -->
                <div class="pay-breakdown-grid">
                    
                    <!-- EARNINGS COLUMN -->
                    <div class="breakdown-col earnings-col">
                        <div class="col-title">
                            <span>🟢 EARNINGS & ALLOWANCES</span>
                            <strong>$<?php echo number_format($payStub['gross_earnings'], 2); ?></strong>
                        </div>
                        <ul class="pay-list">
                            <li>
                                <span>Base Monthly Salary</span>
                                <strong>$<?php echo number_format($payStub['base_salary'], 2); ?></strong>
                            </li>
                            <li>
                                <span>Overtime Pay (<?php echo $payStub['overtime_hrs']; ?> hrs)</span>
                                <strong>$<?php echo number_format($payStub['overtime_pay'], 2); ?></strong>
                            </li>
                            <li>
                                <span>Housing Allowance</span>
                                <strong>$<?php echo number_format($payStub['housing_allowance'], 2); ?></strong>
                            </li>
                            <li>
                                <span>Transport Allowance</span>
                                <strong>$<?php echo number_format($payStub['transport_allowance'], 2); ?></strong>
                            </li>
                            <li>
                                <span>Performance Bonus</span>
                                <strong>$<?php echo number_format($payStub['bonus'], 2); ?></strong>
                            </li>
                        </ul>
                    </div>

                    <!-- DEDUCTIONS COLUMN -->
                    <div class="breakdown-col deductions-col">
                        <div class="col-title">
                            <span>🔴 TAXES & WITHHOLDINGS</span>
                            <strong>-$<?php echo number_format($payStub['total_deductions'], 2); ?></strong>
                        </div>
                        <ul class="pay-list">
                            <li>
                                <span>Income Tax (<?php echo $payStub['tax_bracket_pct']; ?>% bracket)</span>
                                <strong>-$<?php echo number_format($payStub['income_tax'], 2); ?></strong>
                            </li>
                            <li>
                                <span>Pension / Retirement Fund</span>
                                <strong>-$<?php echo number_format($payStub['pension_contribution'], 2); ?></strong>
                            </li>
                            <li>
                                <span>Health & Dental Insurance</span>
                                <strong>-$<?php echo number_format($payStub['health_insurance'], 2); ?></strong>
                            </li>
                            <li>
                                <span>Custom / Misc Deductions</span>
                                <strong>-$<?php echo number_format($payStub['custom_deductions'], 2); ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- DISTRIBUTION METER -->
                <div class="distribution-bar-wrapper">
                    <div class="dist-labels">
                        <span>Net Salary: <?php echo $payStub['take_home_ratio']; ?>%</span>
                        <span>Total Deductions: <?php echo $payStub['effective_deduction_pct']; ?>%</span>
                    </div>
                    <div class="dist-track">
                        <div class="dist-fill net-fill" style="width: <?php echo $payStub['take_home_ratio']; ?>%;"></div>
                        <div class="dist-fill ded-fill" style="width: <?php echo $payStub['effective_deduction_pct']; ?>%;"></div>
                    </div>
                </div>

                <div class="stub-footer">
                    <span>Voucher Serial: <code><?php echo $payStub['stub_id']; ?></code></span>
                    <span>Processed: <?php echo $payStub['generated_at']; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- PAYROLL CALCULATION FORM -->
        <form action="index.php" method="POST" class="payroll-form">
            
            <span class="form-section-head">1. Staff & Departmental Details</span>
            <div class="form-row quad">
                <div class="field">
                    <label for="emp_name">Employee Name</label>
                    <input type="text" id="emp_name" name="emp_name" value="<?php echo htmlspecialchars($_POST['emp_name'] ?? 'Elena Rostova'); ?>" required>
                </div>
                <div class="field">
                    <label for="emp_id">Employee Staff ID</label>
                    <input type="text" id="emp_id" name="emp_id" value="<?php echo htmlspecialchars($_POST['emp_id'] ?? 'EMP-2026-9042'); ?>" required>
                </div>
                <div class="field">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($_POST['department'] ?? 'Software Engineering'); ?>" required>
                </div>
                <div class="field">
                    <label for="designation">Job Title / Designation</label>
                    <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($_POST['designation'] ?? 'Lead Systems Architect'); ?>" required>
                </div>
            </div>

            <span class="form-section-head">2. Gross Earnings & Incentives ($)</span>
            <div class="form-row quad">
                <div class="field">
                    <label for="base_salary">Base Monthly Salary</label>
                    <input type="number" step="0.01" id="base_salary" name="base_salary" value="<?php echo htmlspecialchars($_POST['base_salary'] ?? 8500); ?>" required>
                </div>
                <div class="field">
                    <label for="overtime_hours">Overtime Hours</label>
                    <input type="number" step="0.5" id="overtime_hours" name="overtime_hours" value="<?php echo htmlspecialchars($_POST['overtime_hours'] ?? 12); ?>">
                </div>
                <div class="field">
                    <label for="overtime_rate">Hourly Overtime Rate</label>
                    <input type="number" step="0.01" id="overtime_rate" name="overtime_rate" value="<?php echo htmlspecialchars($_POST['overtime_rate'] ?? 65); ?>">
                </div>
                <div class="field">
                    <label for="bonus">Performance Bonus</label>
                    <input type="number" step="0.01" id="bonus" name="bonus" value="<?php echo htmlspecialchars($_POST['bonus'] ?? 1500); ?>">
                </div>
                <div class="field">
                    <label for="housing_allowance">Housing Allowance</label>
                    <input type="number" step="0.01" id="housing_allowance" name="housing_allowance" value="<?php echo htmlspecialchars($_POST['housing_allowance'] ?? 1200); ?>">
                </div>
                <div class="field">
                    <label for="transport_allowance">Transport Allowance</label>
                    <input type="number" step="0.01" id="transport_allowance" name="transport_allowance" value="<?php echo htmlspecialchars($_POST['transport_allowance'] ?? 450); ?>">
                </div>
            </div>

            <span class="form-section-head">3. Deductions & Statutory Withholdings</span>
            <div class="form-row quad">
                <div class="field">
                    <label for="tax_bracket_pct">Income Tax Rate (%)</label>
                    <input type="number" step="0.1" id="tax_bracket_pct" name="tax_bracket_pct" value="<?php echo htmlspecialchars($_POST['tax_bracket_pct'] ?? 22); ?>" required>
                </div>
                <div class="field">
                    <label for="pension_pct">Pension / 401(k) Contribution (%)</label>
                    <input type="number" step="0.1" id="pension_pct" name="pension_pct" value="<?php echo htmlspecialchars($_POST['pension_pct'] ?? 6); ?>" required>
                </div>
                <div class="field">
                    <label for="health_insurance">Health Insurance ($)</label>
                    <input type="number" step="0.01" id="health_insurance" name="health_insurance" value="<?php echo htmlspecialchars($_POST['health_insurance'] ?? 320); ?>">
                </div>
                <div class="field">
                    <label for="custom_deductions">Misc / Custom Deductions ($)</label>
                    <input type="number" step="0.01" id="custom_deductions" name="custom_deductions" value="<?php echo htmlspecialchars($_POST['custom_deductions'] ?? 150); ?>">
                </div>
            </div>

            <button type="submit" class="calculate-btn">Calculate Net Compensation & Generate Stub</button>
        </form>

        <!-- SESSION HISTORY LOG -->
        <?php if (!empty($_SESSION['payroll_history'])): ?>
            <div class="history-block">
                <div class="history-header">
                    <span>Recent Session Payroll Calculations</span>
                    <a href="index.php?action=clear" class="clear-btn">Clear Log History</a>
                </div>
                <div class="history-list">
                    <?php foreach ($_SESSION['payroll_history'] as $item): ?>
                        <div class="history-row">
                            <div>
                                <strong><?php echo htmlspecialchars($item['emp_name']); ?> (<?php echo htmlspecialchars($item['emp_id']); ?>)</strong>
                                <span>Gross: $<?php echo number_format($item['gross_earnings'], 2); ?> • Deductions: $<?php echo number_format($item['total_deductions'], 2); ?></span>
                            </div>
                            <div class="hist-net">$<?php echo number_format($item['net_compensation'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>