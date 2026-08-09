<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Inventory & Stock Valuation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="inventory-card">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- AUDIT REPORT VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Audit Completed</span>
                <h2>Stock Valuation Sheet</h2>
                <p>SKU Ref: #<?php echo strtoupper(htmlspecialchars($_POST['sku'] ?? 'SKU-000')); ?> | <?php echo date("d M Y"); ?></p>
            </div>

            <?php
            $sku         = htmlspecialchars(trim($_POST['sku'] ?? 'SKU-000'));
            $productName = htmlspecialchars(trim($_POST['productName'] ?? 'Item'));
            $category    = htmlspecialchars(trim($_POST['category'] ?? 'General'));
            $unitCost    = floatval($_POST['unitCost'] ?? 0);
            $quantity    = intval($_POST['quantity'] ?? 0);
            $reorderLvl  = intval($_POST['reorderLvl'] ?? 10);

            // Inventory Asset Logic
            $grossValue   = $unitCost * $quantity;
            $holdingTax   = $grossValue * 0.08; // 8% holding overhead
            $netAssetVal  = $grossValue + $holdingTax;

            $needsReorder = ($quantity <= $reorderLvl);
            $statusText   = $needsReorder ? "CRITICAL: REORDER REQUIRED" : "STOCK LEVEL OPTIMAL";
            $statusClass  = $needsReorder ? "status-alert" : "status-optimal";
            ?>

            <div class="item-summary">
                <div><span>Product:</span> <strong><?php echo $productName; ?></strong></div>
                <div><span>Category:</span> <strong><?php echo $category; ?></strong></div>
            </div>

            <div class="valuation-grid">
                <div class="val-box">
                    <span>Gross Value</span>
                    <h4>$<?php echo number_format($grossValue, 2); ?></h4>
                </div>
                <div class="val-box">
                    <span>Holding Tax (8%)</span>
                    <h4>$<?php echo number_format($holdingTax, 2); ?></h4>
                </div>
                <div class="val-box">
                    <span>Net Asset Valuation</span>
                    <h4 class="highlight-val">$<?php echo number_format($netAssetVal, 2); ?></h4>
                </div>
            </div>

            <div class="inventory-meta">
                <div class="meta-row">
                    <span>Unit Cost Price:</span>
                    <strong>$<?php echo number_format($unitCost, 2); ?></strong>
                </div>
                <div class="meta-row">
                    <span>Current Units in Stock:</span>
                    <strong><?php echo $quantity; ?> Units</strong>
                </div>
                <div class="meta-row">
                    <span>Reorder Threshold:</span>
                    <strong><?php echo $reorderLvl; ?> Units</strong>
                </div>
            </div>

            <div class="status-banner <?php echo $statusClass; ?>">
                <strong>System Notice:</strong> <?php echo $statusText; ?>
            </div>

            <a href="index.php" class="back-btn">&larr; Audit Another Inventory Item</a>

        <?php else: ?>
            <!-- STOCK INGESTION FORM -->
            <div class="card-header">
                <span class="badge">Inventory Ledger v10.0</span>
                <h2>Stock Valuator</h2>
                <p>Register stock metrics to compute asset valuation and reorder flags</p>
            </div>

            <form action="index.php" method="POST" class="inventory-form">
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="sku">SKU Code</label>
                        <input type="text" id="sku" name="sku" placeholder="e.g. PRD-9042" required>
                    </div>
                    <div class="form-group flex-2">
                        <label for="productName">Product Name</label>
                        <input type="text" id="productName" name="productName" placeholder="e.g. Ergonomic Office Chair" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="Electronics">Electronics</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Raw Materials">Raw Materials</option>
                            <option value="Logistics & Supplies">Logistics & Supplies</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="unitCost">Unit Cost ($)</label>
                        <input type="number" id="unitCost" name="unitCost" min="0.01" step="0.01" placeholder="149.99" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="quantity">Current Stock Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="0" placeholder="45" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="reorderLvl">Reorder Threshold Level</label>
                        <input type="number" id="reorderLvl" name="reorderLvl" min="1" placeholder="15" required>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Run Audit & Calculate Valuation</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>