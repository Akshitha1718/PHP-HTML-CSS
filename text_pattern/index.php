<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Pattern & Character Analyzer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="analyzer-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- METRICS DASHBOARD VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Analysis Complete</span>
                <h2>Text Metrics Dashboard</h2>
                <p>String Ref: #STR-<?php echo strtoupper(substr(md5(uniqid()), 0, 6)); ?> | <?php echo date("M d, Y"); ?></p>
            </div>

            <?php
            $rawText = trim($_POST['inputText'] ?? '');
            
            // String Metric Calculations
            $totalChars    = strlen($rawText);
            $totalWords    = str_word_count($rawText);
            
            $vowels        = preg_match_all('/[aeiouAEIOU]/', $rawText);
            $consonants    = preg_match_all('/[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]/', $rawText);
            $digits        = preg_match_all('/[0-9]/', $rawText);
            $spaces        = preg_match_all('/\s/', $rawText);
            $specialChars  = $totalChars - ($vowels + $consonants + $digits + $spaces);

            // Reversals and Transformations
            $reversedText  = strrev($rawText);
            $uppercaseText = strtoupper($rawText);
            ?>

            <div class="text-preview-box">
                <span>Evaluated Passage:</span>
                <p>"<?php echo htmlspecialchars($rawText); ?>"</p>
            </div>

            <div class="metrics-grid">
                <div class="metric-box">
                    <span>Vowels</span>
                    <h3 class="highlight-vowel"><?php echo $vowels; ?></h3>
                </div>
                <div class="metric-box">
                    <span>Consonants</span>
                    <h3 class="highlight-consonant"><?php echo $consonants; ?></h3>
                </div>
                <div class="metric-box">
                    <span>Digits</span>
                    <h3 class="highlight-digit"><?php echo $digits; ?></h3>
                </div>
                <div class="metric-box">
                    <span>Specials</span>
                    <h3 class="highlight-special"><?php echo $specialChars; ?></h3>
                </div>
            </div>

            <div class="analysis-details">
                <div class="detail-row">
                    <span>Total Character Count:</span>
                    <strong><?php echo $totalChars; ?> Chars</strong>
                </div>
                <div class="detail-row">
                    <span>Total Word Count:</span>
                    <strong><?php echo $totalWords; ?> Words</strong>
                </div>
                <div class="detail-row">
                    <span>Whitespace Count:</span>
                    <strong><?php echo $spaces; ?> Spaces</strong>
                </div>
                <div class="detail-row">
                    <span>Reversed String:</span>
                    <strong class="text-truncate"><?php echo htmlspecialchars($reversedText); ?></strong>
                </div>
            </div>

            <a href="index.php" class="back-btn">&larr; Analyze Another Text Passage</a>

        <?php else: ?>
            <!-- INPUT FORM VIEW -->
            <div class="card-header">
                <span class="badge">Text Diagnostics v9.0</span>
                <h2>Text Pattern Analyzer</h2>
                <p>Analyze character distribution, word counts, and structural metrics</p>
            </div>

            <form action="index.php" method="POST" class="analyzer-form">
                <div class="form-group">
                    <label for="inputText">Enter Title or Text Passage</label>
                    <textarea id="inputText" name="inputText" rows="5" placeholder="e.g. Hello World! Web Development 2026 #PHP_Rocks" required></textarea>
                </div>

                <button type="submit" class="submit-btn">Run String Diagnostics & Analysis</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>