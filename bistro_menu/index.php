<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gourmet Bistro Digital Menu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="bistro-card">
        <?php
        // Gourmet Menu Items Database
        $menuCatalog = [
            'app1' => ['name' => 'Truffle & Wild Mushroom Arancini', 'category' => 'Appetizers', 'price' => 18.50, 'desc' => 'Crispy risotto spheres with smoked provolone & garlic aioli'],
            'app2' => ['name' => 'Yellowfin Tuna Tartare', 'category' => 'Appetizers', 'price' => 22.00, 'desc' => 'Avocado mousse, citrus ponzu, sesame crisp'],
            'main1' => ['name' => 'Dry-Aged Prime Ribeye (12oz)', 'category' => 'Main Courses', 'price' => 54.00, 'desc' => 'Truffle butter, roasted bone marrow, duck-fat potatoes'],
            'main2' => ['name' => 'Saffron Lobster Risotto', 'category' => 'Main Courses', 'price' => 42.50, 'desc' => 'Maine lobster tail, carnaroli rice, charred cherry tomatoes'],
            'des1' => ['name' => 'Valrhona Dark Chocolate Soufflé', 'category' => 'Desserts', 'price' => 16.00, 'desc' => 'Grand Marnier anglaise & Madagascar vanilla gelato'],
            'des2' => ['name' => 'Pistachio Matcha Tiramisu', 'category' => 'Desserts', 'price' => 14.50, 'desc' => 'Espresso-soaked ladyfingers, mascarpone cream'],
            'bev1' => ['name' => 'Smoked Old Fashioned', 'category' => 'Beverages', 'price' => 19.00, 'desc' => 'Bourbon, raw honey syrup, Angostura, hickory smoke'],
            'bev2' => ['name' => 'Sparkling Elderflower Botanical', 'category' => 'Beverages', 'price' => 9.50, 'desc' => 'Craft mocktail with mint, cucumber, and tonic']
        ];
        ?>

        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <!-- DIGITAL ORDER RECEIPT VIEW -->
            <div class="card-header">
                <span class="badge badge-success">Order Confirmed</span>
                <h2>L'Étoile Gourmet Bistro</h2>
                <p>Table Ref: #<?php echo strtoupper(htmlspecialchars($_POST['tableNum'] ?? 'T-01')); ?> | Order #<?php echo rand(1000, 9999); ?></p>
            </div>

            <?php
            $guestName   = htmlspecialchars(trim($_POST['guestName'] ?? 'Valued Guest'));
            $tableNum    = htmlspecialchars(trim($_POST['tableNum'] ?? '01'));
            $diningType  = htmlspecialchars(trim($_POST['diningType'] ?? 'Dine-In'));
            $quantities  = $_POST['qty'] ?? [];

            $orderItems = [];
            $subtotal   = 0.0;

            foreach ($quantities as $itemId => $qty) {
                $qtyInt = intval($qty);
                if ($qtyInt > 0 && isset($menuCatalog[$itemId])) {
                    $item = $menuCatalog[$itemId];
                    $lineTotal = $item['price'] * $qtyInt;
                    $subtotal += $lineTotal;
                    $orderItems[] = [
                        'name'     => $item['name'],
                        'category' => $item['category'],
                        'price'    => $item['price'],
                        'qty'      => $qtyInt,
                        'total'    => $lineTotal
                    ];
                }
            }

            $serviceCharge = $subtotal * 0.10; // 10% Service Gratuity
            $salesTax      = $subtotal * 0.085; // 8.5% Dining Tax
            $grandTotal    = $subtotal + $serviceCharge + $salesTax;
            ?>

            <div class="guest-banner">
                <div><span>Guest:</span> <strong><?php echo $guestName; ?></strong></div>
                <div><span>Type:</span> <strong><?php echo $diningType; ?> (Table <?php echo $tableNum; ?>)</strong></div>
            </div>

            <?php if (!empty($orderItems)): ?>
                <div class="receipt-table-wrapper">
                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $line): ?>
                                <tr>
                                    <td>
                                        <div class="item-name-cell"><?php echo $line['name']; ?></div>
                                        <div class="item-cat-tag"><?php echo $line['category']; ?></div>
                                    </td>
                                    <td><?php echo $line['qty']; ?></td>
                                    <td>$<?php echo number_format($line['price'], 2); ?></td>
                                    <td class="text-right">$<?php echo number_format($line['total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="total-summary">
                    <div class="summary-row">
                        <span>Items Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Service Charge (10%):</span>
                        <span>+$<?php echo number_format($serviceCharge, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Dining Sales Tax (8.5%):</span>
                        <span>+$<?php echo number_format($salesTax, 2); ?></span>
                    </div>
                    <div class="summary-row grand-total">
                        <span>Grand Total Due:</span>
                        <span>$<?php echo number_format($grandTotal, 2); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-notice">
                    <p>No culinary items were selected. Please choose items from the menu.</p>
                </div>
            <?php endif; ?>

            <a href="index.php" class="back-btn">&larr; Return to Gourmet Menu</a>

        <?php else: ?>
            <!-- MENU ORDER SELECTION FORM -->
            <div class="card-header">
                <span class="badge">Artisanal Dining</span>
                <h2>L'Étoile Bistro Menu</h2>
                <p>Select your course preferences and quantities to send your order to the kitchen</p>
            </div>

            <form action="index.php" method="POST" class="menu-form">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="guestName">Guest Name</label>
                        <input type="text" id="guestName" name="guestName" placeholder="e.g. Lord Julian Vance" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="tableNum">Table No.</label>
                        <input type="text" id="tableNum" name="tableNum" placeholder="e.g. 12" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="diningType">Service</label>
                        <select id="diningType" name="diningType">
                            <option value="Dine-In">Dine-In</option>
                            <option value="Takeaway">Takeaway</option>
                        </select>
                    </div>
                </div>

                <div class="catalog-sections">
                    <?php
                    $currentCategory = '';
                    foreach ($menuCatalog as $id => $item):
                        if ($currentCategory !== $item['category']):
                            $currentCategory = $item['category'];
                            echo "<h3 class='category-header'>{$currentCategory}</h3>";
                        endif;
                    ?>
                        <div class="menu-item-card">
                            <div class="item-info">
                                <h4><?php echo $item['name']; ?> <span class="price-tag">$<?php echo number_format($item['price'], 2); ?></span></h4>
                                <p><?php echo $item['desc']; ?></p>
                            </div>
                            <div class="item-qty-input">
                                <label for="qty_<?php echo $id; ?>">Qty</label>
                                <input type="number" id="qty_<?php echo $id; ?>" name="qty[<?php echo $id; ?>]" min="0" max="10" value="0">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="submit-btn">Place Order & Generate Invoice</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>