<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retail Point-of-Sale Billing Terminal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="pos-container">
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- RECEIPT VIEW -->
            <div class="pos-header">
                <span class="badge badge-gold">Transaction Success</span>
                <h2>Retail Invoice</h2>
                <p>Receipt #POS-<?php echo rand(10000, 99999); ?> | <?php echo date("d M Y, h:i A"); ?></p>
            </div>

            <?php
            $customerName = htmlspecialchars(trim($_POST['customerName'] ?? 'Guest'));
            $item1_name   = htmlspecialchars(trim($_POST['item1_name'] ?? 'Item 1'));
            $item1_price  = floatval($_POST['item1_price'] ?? 0);
            $item1_qty    = intval($_POST['item1_qty'] ?? 1);

            $item2_name   = htmlspecialchars(trim($_POST['item2_name'] ?? 'Item 2'));
            $item2_price  = floatval($_POST['item2_price'] ?? 0);
            $item2_qty    = intval($_POST['item2_qty'] ?? 1);

            $discountRate = floatval($_POST['discount'] ?? 0);
            $taxRate      = 8.5; // 8.5% fixed sales tax

            // Line Calculations
            $subtotal1 = $item1_price * $item1_qty;
            $subtotal2 = $item2_price * $item2_qty;
            $grossSubtotal = $subtotal1 + $subtotal2;

            $discountAmount = ($grossSubtotal * $discountRate) / 100;
            $taxableTotal   = $grossSubtotal - $discountAmount;
            $taxAmount      = ($taxableTotal * $taxRate) / 100;
            $grandTotal     = $taxableTotal + $taxAmount;
            ?>

            <div class="customer-info">
                <span>Customer: <strong><?php echo $customerName; ?></strong></span>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $item1_name; ?></td>
                        <td><?php echo $item1_qty; ?></td>
                        <td>$<?php echo number_format($item1_price, 2); ?></td>
                        <td>$<?php echo number_format($subtotal1, 2); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $item2_name; ?></td>
                        <td><?php echo $item2_qty; ?></td>
                        <td>$<?php echo number_format($item2_price, 2); ?></td>
                        <td>$<?php echo number_format($subtotal2, 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($grossSubtotal, 2); ?></span>
                </div>
                <?php if ($discountRate > 0): ?>
                <div class="summary-row discount">
                    <span>Discount (<?php echo $discountRate; ?>%):</span>
                    <span>-$<?php echo number_format($discountAmount, 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Sales Tax (<?php echo $taxRate; ?>%):</span>
                    <span>+$<?php echo number_format($taxAmount, 2); ?></span>
                </div>
                <div class="summary-row total-row">
                    <span>Grand Total:</span>
                    <span>$<?php echo number_format($grandTotal, 2); ?></span>
                </div>
            </div>

            <a href="index.php" class="new-sale-btn">&larr; Process New Order</a>

        <?php else: ?>
            <!-- BILLING FORM VIEW -->
            <div class="pos-header">
                <span class="badge">POS Terminal v4.0</span>
                <h2>Checkout Terminal</h2>
                <p>Enter items and customer details to generate an invoice</p>
            </div>

            <form action="index.php" method="POST" class="pos-form">
                <div class="form-group">
                    <label for="customerName">Customer Name</label>
                    <input type="text" id="customerName" name="customerName" placeholder="e.g. Alex Morgan" required>
                </div>

                <div class="item-block">
                    <h4>Item 01</h4>
                    <div class="form-row">
                        <div class="form-group flex-2">
                            <label>Item Name</label>
                            <input type="text" name="item1_name" placeholder="e.g. Wireless Mouse" required>
                        </div>
                        <div class="form-group flex-1">
                            <label>Price ($)</label>
                            <input type="number" name="item1_price" min="0" step="0.01" placeholder="29.99" required>
                        </div>
                        <div class="form-group flex-1">
                            <label>Qty</label>
                            <input type="number" name="item1_qty" min="1" value="1" required>
                        </div>
                    </div>
                </div>

                <div class="item-block">
                    <h4>Item 02</h4>
                    <div class="form-row">
                        <div class="form-group flex-2">
                            <label>Item Name</label>
                            <input type="text" name="item2_name" placeholder="e.g. Mechanical Keyboard" required>
                        </div>
                        <div class="form-group flex-1">
                            <label>Price ($)</label>
                            <input type="number" name="item2_price" min="0" step="0.01" placeholder="89.99" required>
                        </div>
                        <div class="form-group flex-1">
                            <label>Qty</label>
                            <input type="number" name="item2_qty" min="1" value="1" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="discount">Discount Code / Offer (%)</label>
                    <select id="discount" name="discount">
                        <option value="0">No Discount (0%)</option>
                        <option value="5">Regular Customer (5%)</option>
                        <option value="10">Seasonal Offer (10%)</option>
                        <option value="15">VIP Member (15%)</option>
                    </select>
                </div>

                <button type="submit" class="checkout-btn">Complete & Print Invoice</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>