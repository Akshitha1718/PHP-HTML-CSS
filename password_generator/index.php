<?php
session_start();

if (!isset($_SESSION['generated_passwords'])) {
    $_SESSION['generated_passwords'] = [];
}

$generatedPassword = '';
$entropyBits       = 0;
$strengthLabel     = '';
$strengthClass     = '';
$crackTimeStr      = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $length      = max(6, min(64, intval($_POST['length'] ?? 16)));
    $useUpper    = isset($_POST['useUpper']);
    $useLower    = isset($_POST['useLower']);
    $useNums     = isset($_POST['useNums']);
    $useSymbols  = isset($_POST['useSymbols']);
    $excludeAmb  = isset($_POST['excludeAmb']);

    // Character Pool Construction
    $upperSet   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowerSet   = 'abcdefghijklmnopqrstuvwxyz';
    $numSet     = '0123456789';
    $symbolSet  = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    $ambiguous  = ['O', '0', 'l', '1', 'I', '|', 'S', '5', 'B', '8', 'Z', '2'];

    $pool = '';
    $guaranteedChars = [];

    if ($useUpper) {
        $pool .= $upperSet;
        $guaranteedChars[] = $upperSet[random_int(0, strlen($upperSet) - 1)];
    }
    if ($useLower) {
        $pool .= $lowerSet;
        $guaranteedChars[] = $lowerSet[random_int(0, strlen($lowerSet) - 1)];
    }
    if ($useNums) {
        $pool .= $numSet;
        $guaranteedChars[] = $numSet[random_int(0, strlen($numSet) - 1)];
    }
    if ($useSymbols) {
        $pool .= $symbolSet;
        $guaranteedChars[] = $symbolSet[random_int(0, strlen($symbolSet) - 1)];
    }

    // Default fallback if nothing selected
    if (empty($pool)) {
        $pool = $lowerSet . $numSet;
        $useLower = true;
        $useNums  = true;
    }

    // Apply Ambiguous Filtering
    if ($excludeAmb) {
        $pool = str_replace($ambiguous, '', $pool);
        $guaranteedChars = array_map(function($ch) use ($ambiguous, $pool) {
            return in_array($ch, $ambiguous, true) ? $pool[random_int(0, strlen($pool) - 1)] : $ch;
        }, $guaranteedChars);
    }

    $poolSize = strlen($pool);

    // Cryptographically Secure Password Generation
    $passArray = [];
    foreach ($guaranteedChars as $char) {
        $passArray[] = $char;
    }

    while (count($passArray) < $length) {
        $randomIndex = random_int(0, $poolSize - 1);
        $passArray[] = $pool[$randomIndex];
    }

    // Cryptographic shuffle
    for ($i = count($passArray) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        $temp = $passArray[$i];
        $passArray[$i] = $passArray[$j];
        $passArray[$j] = $temp;
    }

    $generatedPassword = implode('', $passArray);

    // Calculate Shannon Entropy: E = length * log2(poolSize)
    $entropyBits = ($poolSize > 0) ? round($length * (log($poolSize, 2)), 1) : 0;

    // Strength Classification & Crack Time Estimation
    if ($entropyBits < 40) {
        $strengthLabel = 'Weak';
        $strengthClass = 'strength-weak';
        $crackTimeStr  = 'Seconds to Minutes';
    } elseif ($entropyBits < 65) {
        $strengthLabel = 'Moderate';
        $strengthClass = 'strength-medium';
        $crackTimeStr  = 'A Few Days';
    } elseif ($entropyBits < 90) {
        $strengthLabel = 'Strong';
        $strengthClass = 'strength-strong';
        $crackTimeStr  = 'Decades';
    } else {
        $strengthLabel = 'Ultra-Secure';
        $strengthClass = 'strength-ultra';
        $crackTimeStr  = 'Trillions of Years';
    }

    // Push to session history (max 5 stored)
    array_unshift($_SESSION['generated_passwords'], [
        'password' => $generatedPassword,
        'entropy'  => $entropyBits,
        'time'     => date('H:i:s')
    ]);
    $_SESSION['generated_passwords'] = array_slice($_SESSION['generated_passwords'], 0, 5);
}

// Clear History Action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['generated_passwords'] = [];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Algorithmic Password Generator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="pass-container">
        <div class="pass-card">
            <div class="card-header">
                <span class="badge">Cryptographic Engine</span>
                <h2>Algorithmic Password Generator</h2>
                <p>Generate high-entropy credentials backed by CSPRNG randomness</p>
            </div>

            <!-- GENERATED OUTPUT DISPLAY -->
            <?php if (!empty($generatedPassword)): ?>
                <div class="output-box">
                    <span class="output-label">Generated Secret Key</span>
                    <div class="pass-display-row">
                        <input type="text" id="passwordOutput" value="<?php echo htmlspecialchars($generatedPassword); ?>" readonly>
                        <button type="button" class="btn-copy" onclick="copyPassword()">Copy</button>
                    </div>

                    <div class="metrics-bar">
                        <div class="metric-item">
                            <span>Entropy</span>
                            <strong><?php echo $entropyBits; ?> <small>bits</small></strong>
                        </div>
                        <div class="metric-item">
                            <span>Strength</span>
                            <strong class="<?php echo $strengthClass; ?>"><?php echo $strengthLabel; ?></strong>
                        </div>
                        <div class="metric-item">
                            <span>Est. Crack Time</span>
                            <strong><?php echo $crackTimeStr; ?></strong>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- GENERATOR CONFIGURATION FORM -->
            <form action="index.php" method="POST" class="generator-form">
                <div class="form-group">
                    <div class="label-row">
                        <label for="length">Password Length</label>
                        <span id="lengthVal" class="range-val"><?php echo $_POST['length'] ?? 18; ?> Chars</span>
                    </div>
                    <input type="range" id="length" name="length" min="8" max="48" value="<?php echo $_POST['length'] ?? 18; ?>" oninput="document.getElementById('lengthVal').innerText = this.value + ' Chars'">
                </div>

                <div class="form-group">
                    <label>Character Set Composition</label>
                    <div class="checkbox-grid">
                        <label class="check-card">
                            <input type="checkbox" name="useUpper" value="1" <?php echo (!isset($_POST['length']) || isset($_POST['useUpper'])) ? 'checked' : ''; ?>>
                            <span>Uppercase (A-Z)</span>
                        </label>
                        <label class="check-card">
                            <input type="checkbox" name="useLower" value="1" <?php echo (!isset($_POST['length']) || isset($_POST['useLower'])) ? 'checked' : ''; ?>>
                            <span>Lowercase (a-z)</span>
                        </label>
                        <label class="check-card">
                            <input type="checkbox" name="useNums" value="1" <?php echo (!isset($_POST['length']) || isset($_POST['useNums'])) ? 'checked' : ''; ?>>
                            <span>Numbers (0-9)</span>
                        </label>
                        <label class="check-card">
                            <input type="checkbox" name="useSymbols" value="1" <?php echo (!isset($_POST['length']) || isset($_POST['useSymbols'])) ? 'checked' : ''; ?>>
                            <span>Symbols (!@#$)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="check-card full-width">
                        <input type="checkbox" name="excludeAmb" value="1" <?php echo isset($_POST['excludeAmb']) ? 'checked' : ''; ?>>
                        <span>Exclude Ambiguous Characters (e.g. 0, O, I, 1, l, S, 5)</span>
                    </label>
                </div>

                <button type="submit" class="btn-generate">Generate Cryptographic Key</button>
            </form>

            <!-- SESSION HISTORY -->
            <?php if (!empty($_SESSION['generated_passwords'])): ?>
                <div class="history-section">
                    <div class="history-header">
                        <span>Recent Vault Keys</span>
                        <a href="index.php?action=clear" class="clear-link">Clear</a>
                    </div>
                    <ul class="history-list">
                        <?php foreach ($_SESSION['generated_passwords'] as $item): ?>
                            <li>
                                <code><?php echo htmlspecialchars($item['password']); ?></code>
                                <span class="hist-meta"><?php echo $item['entropy']; ?> bits | <?php echo $item['time']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function copyPassword() {
            const input = document.getElementById('passwordOutput');
            input.select();
            document.execCommand('copy');
            alert('Password copied to clipboard!');
        }
    </script>
</body>
</html>