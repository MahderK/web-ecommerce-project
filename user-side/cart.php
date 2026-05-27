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

        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shipping = count($_SESSION['cart']) > 0 ? 5.00 : 0;
            echo json_encode([
                'success' => true,
                'cart_count' => count($_SESSION['cart']),
                'subtotal' => number_format($subtotal, 2),
                'shipping' => number_format($shipping, 2),
                'total' => number_format($subtotal + $shipping, 2)
            ]);
            exit();
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

        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            $subtotal = 0;
            $item_total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
                if ($item['product_id'] == $pid) {
                    $item_total = $item['price'] * $item['quantity'];
                }
            }
            $shipping = count($_SESSION['cart']) > 0 ? 5.00 : 0;
            echo json_encode([
                'success' => true,
                'cart_count' => count($_SESSION['cart']),
                'item_total' => number_format($item_total, 2),
                'subtotal' => number_format($subtotal, 2),
                'shipping' => number_format($shipping, 2),
                'total' => number_format($subtotal + $shipping, 2)
            ]);
            exit();
        }

        header("Location: cart.php");
        exit();
    }

    // Remove from cart
    if (isset($_POST['remove_item'])) {
        $pid = intval($_POST['product_id']);
        $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($pid) {
            return $item['product_id'] != $pid;
        }));

        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shipping = count($_SESSION['cart']) > 0 ? 5.00 : 0;
            echo json_encode([
                'success' => true,
                'cart_count' => count($_SESSION['cart']),
                'subtotal' => number_format($subtotal, 2),
                'shipping' => number_format($shipping, 2),
                'total' => number_format($subtotal + $shipping, 2)
            ]);
            exit();
        }

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
    <style>
        /* Cart item transitions for smooth AJAX updates */
        .cart-item {
            transition: all 0.4s ease;
            opacity: 1;
            transform: scale(1);
        }
        .cart-item.fading-out {
            opacity: 0;
            transform: scale(0.9);
            padding-top: 0;
            padding-bottom: 0;
            margin-top: 0;
            margin-bottom: 0;
            height: 0;
            overflow: hidden;
            border: none;
        }
        .item-subtotal {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        .item-subtotal-val {
            font-weight: 600;
            color: #0b4d2c;
        }
    </style>
</head>
<body>
    <?php include './includes/navbar.php'; ?>

    <section class="cart-page">
        <div class="container">
            <h2>Your Shopping Cart</h2>
            
            <!-- Empty Cart State -->
            <div class="empty-cart" id="empty-cart-state" style="<?= $cart_count === 0 ? '' : 'display: none;' ?>">
                <i class="fa-solid fa-cart-shopping fa-3x"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any plants yet. Browse our collection and find the perfect green companion!</p>
                <a href="products.php" class="btn">Browse Plants</a>
            </div>

            <!-- Dynamic Cart Container -->
            <div class="cart-container" id="cart-content-container" style="<?= $cart_count === 0 ? 'display: none;' : '' ?>">
                <!-- Cart Items -->
                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="cart-item" data-product-id="<?= $item['product_id'] ?>">
                            <div class="item-img">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="item-details">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="item-price"><?= number_format($item['price'], 2) ?> Birr each</div>
                                <div class="item-subtotal">Total: <span class="item-subtotal-val" data-price="<?= $item['price'] ?>"><?= number_format($item['price'] * $item['quantity'], 2) ?></span> Birr</div>
                            </div>
                            <div class="quantity-control">
                                <form method="POST" action="cart.php" class="qty-form">
                                    <input type="hidden" name="update_qty" value="1">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                    <button type="button" class="qty-btn" onclick="updateQty(this, -1)">−</button>
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-field" onchange="submitQtyChange(this.form)">
                                    <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                                </form>
                                <form method="POST" action="cart.php" class="remove-form">
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
                        <span id="summary-items-count">Subtotal (<?= $cart_count ?> item<?= $cart_count > 1 ? 's' : '' ?>)</span>
                        <span id="summary-subtotal"><?= number_format($subtotal, 2) ?> Birr</span>
                    </div>
                    <div class="summary-line">
                        <span>Shipping</span>
                        <span id="summary-shipping"><?= number_format($shipping, 2) ?> Birr</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span id="summary-total"><?= number_format($total, 2) ?> Birr</span>
                    </div>
                    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </section>

    <script>
        function updateQty(btn, amount) {
            var form = btn.closest('form');
            var input = form.querySelector('input[name="quantity"]');
            var val = parseInt(input.value) + amount;
            if (val < 1) val = 1;
            input.value = val;
            submitQtyChange(form);
        }

        function submitQtyChange(form) {
            var formData = new FormData(form);
            formData.append('ajax', '1');

            fetch('cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update specific item subtotal text
                    var productId = form.querySelector('input[name="product_id"]').value;
                    var itemCard = document.querySelector('.cart-item[data-product-id="' + productId + '"]');
                    if (itemCard) {
                        var subtotalSpan = itemCard.querySelector('.item-subtotal-val');
                        if (subtotalSpan) {
                            subtotalSpan.textContent = data.item_total;
                        }
                    }
                    // Update global summary totals
                    updateSummaryDOM(data);
                }
            })
            .catch(error => {
                // Fallback to normal submit if ajax fails
                form.submit();
            });
        }

        // Intercept remove item forms to handle AJAX delete with animation
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.classList.contains('remove-form')) {
                e.preventDefault();
                var form = e.target;
                var productId = form.querySelector('input[name="product_id"]').value;
                var itemCard = document.querySelector('.cart-item[data-product-id="' + productId + '"]');
                
                var formData = new FormData(form);
                formData.append('ajax', '1');

                fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (itemCard) {
                            // Smooth fade-out animation
                            itemCard.classList.add('fading-out');
                            setTimeout(function() {
                                itemCard.remove();
                                updateSummaryDOM(data);

                                // If empty, fade in the empty state
                                if (data.cart_count === 0) {
                                    document.getElementById('cart-content-container').style.display = 'none';
                                    document.getElementById('empty-cart-state').style.display = 'block';
                                }
                            }, 400);
                        }
                    }
                })
                .catch(error => {
                    form.submit();
                });
            }
        });

        function updateSummaryDOM(data) {
            document.getElementById('summary-items-count').innerHTML = 'Subtotal (' + data.cart_count + ' item' + (data.cart_count !== 1 ? 's' : '') + ')';
            document.getElementById('summary-subtotal').textContent = data.subtotal + ' Birr';
            document.getElementById('summary-shipping').textContent = data.shipping + ' Birr';
            document.getElementById('summary-total').textContent = data.total + ' Birr';
            
            // Also update the navbar cart badge count dynamically if it exists
            var navBadge = document.querySelector('.nav-links .cart-badge, .icons .cart-badge');
            if (navBadge) {
                navBadge.textContent = data.cart_count;
                if (data.cart_count === 0) {
                    navBadge.style.display = 'none';
                } else {
                    navBadge.style.display = 'inline-block';
                }
            }
        }
    </script>

    <?php include './includes/footer.php'; ?>
</body>
</html>