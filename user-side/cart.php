<?php
session_start();
include_once './includes/db.php';

// Initialize cart session if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Add to cart
    if (isset($_POST['add_to_cart'])) {
        $pid = intval($_POST['product_id']);
        $qty = intval($_POST['quantity'] ?? 1);
        if ($qty < 1) $qty = 1;

        // Check if item already exists in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $pid) {
                $item['quantity'] += $qty;
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $pid,
                'name'       => $_POST['product_name'] ?? '',
                'price'      => floatval($_POST['product_price'] ?? 0),
                'image'      => $_POST['product_image'] ?? '',
                'quantity'   => $qty
            ];
        }
        header("Location: cart.php");
        exit();
    }

    // Update quantity
    if (isset($_POST['update_qty'])) {
        $pid = intval($_POST['product_id']);
        $qty = intval($_POST['quantity']);
        if ($qty < 1) $qty = 1;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $pid) {
                $item['quantity'] = $qty;
                break;
            }
        }
        unset($item);
        header("Location: cart.php");
        exit();
    }

    // Remove from cart
    if (isset($_POST['remove_item'])) {
        $pid = intval($_POST['product_id']);
        $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($pid) {
            return $item['product_id'] != $pid;
        }));
        header("Location: cart.php");
        exit();
    }
}

// Calculate totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = count($_SESSION['cart']) > 0 ? 5.00 : 0;
$total = $subtotal + $shipping;
$cart_count = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Plantea</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include './includes/navbar.php'; ?>

    <section class="cart-page">
        <div class="container">
            <h2>Your Shopping Cart</h2>
            
            <?php if ($cart_count === 0): ?>
                <!-- Empty Cart State -->
                <div class="empty-cart">
                    <i class="fa-solid fa-cart-shopping fa-3x"></i>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added any plants yet. Browse our collection and find the perfect green companion!</p>
                    <a href="products.php" class="btn">Browse Plants</a>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item">
                                <div class="item-img">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                </div>
                                <div class="item-details">
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                    <div class="item-price">$<?= number_format($item['price'], 2) ?></div>
                                </div>
                                <div class="quantity-control">
                                    <form method="POST" action="cart.php" class="qty-form">
                                        <input type="hidden" name="update_qty" value="1">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <button type="button" class="qty-btn" onclick="updateQty(this, -1)">−</button>
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-field" onchange="this.form.submit()">
                                        <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                                    </form>
                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="remove_item" value="1">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <button type="submit" class="remove-btn" title="Remove item"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-line">
                            <span>Subtotal (<?= $cart_count ?> item<?= $cart_count > 1 ? 's' : '' ?>)</span>
                            <span>$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Shipping</span>
                            <span>$<?= number_format($shipping, 2) ?></span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span>$<?= number_format($total, 2) ?></span>
                        </div>
                        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        function updateQty(btn, amount) {
            var form = btn.closest('form');
            var input = form.querySelector('input[name="quantity"]');
            var val = parseInt(input.value) + amount;
            if (val < 1) val = 1;
            input.value = val;
            form.submit();
        }
    </script>

    <?php include './includes/footer.php'; ?>
</body>
</html>