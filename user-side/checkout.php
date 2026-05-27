<?php
include_once './includes/auth.php';
require_login('login.php');
include_once './includes/db.php';

// If cart is empty, redirect back to cart
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: cart.php");
    exit();
}

$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 5.00;
$total = $subtotal + $shipping;

$order_placed = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'cash');

    if ($fullname === '' || $email === '' || $address === '' || $city === '' || $zip === '') {
        $error = 'Please fill in all checkout fields.';
    } else {
        // Begin order placement transaction
        mysqli_begin_transaction($conn);
        
        try {
            // 1. Insert into orders table
            $user_id = $_SESSION['user_id'];
            $status = 'Pending';
            $stmt_ord = mysqli_prepare($conn, "INSERT INTO orders (user_id, total_amount, order_status) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_ord, "ids", $user_id, $total, $status);
            mysqli_stmt_execute($stmt_ord);
            $order_id = mysqli_insert_id($conn);

            // 2. Insert order items & reduce product stock
            foreach ($_SESSION['cart'] as $item) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $item_subtotal = $item['price'] * $qty;

                // Validate stock availability
                $stock_stmt = mysqli_prepare($conn, "SELECT stock_quantity FROM products WHERE product_id = ?");
                mysqli_stmt_bind_param($stock_stmt, "i", $pid);
                mysqli_stmt_execute($stock_stmt);
                $stock_res = mysqli_stmt_get_result($stock_stmt);
                $prod_data = mysqli_fetch_assoc($stock_res);

                if (!$prod_data || $prod_data['stock_quantity'] < $qty) {
                    throw new Exception("Not enough stock for: " . $item['name']);
                }

                // Insert order item
                $stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_item, "iiid", $order_id, $pid, $qty, $item_subtotal);
                mysqli_stmt_execute($stmt_item);

                // Reduce stock
                $new_stock = $prod_data['stock_quantity'] - $qty;
                $stmt_stock = mysqli_prepare($conn, "UPDATE products SET stock_quantity = ? WHERE product_id = ?");
                mysqli_stmt_bind_param($stmt_stock, "ii", $new_stock, $pid);
                mysqli_stmt_execute($stmt_stock);
            }

            // 3. Insert into payments table
            $pay_status = ($payment_method === 'cash') ? 'Pending' : 'Paid';
            $paid_at = ($pay_status === 'Paid') ? date('Y-m-d H:i:s') : null;
            
            $stmt_pay = mysqli_prepare($conn, "INSERT INTO payments (order_id, payment_method, amount, payment_status, paid_at) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_pay, "isdss", $order_id, $payment_method, $total, $pay_status, $paid_at);
            mysqli_stmt_execute($stmt_pay);

            // Commit transaction
            mysqli_commit($conn);
            
            // Empty session cart
            $_SESSION['cart'] = [];
            $order_placed = true;
        } catch (Exception $e) {
            // Rollback on any failure
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Plantea</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include './includes/navbar.php'; ?>

    <section class="checkout-page">
        <div class="container">
            
            <?php if ($order_placed): ?>
                <!-- Success State -->
                <div class="order-success">
                    <i class="fa-solid fa-circle-check fa-4x"></i>
                    <h2>Thank You for Your Order!</h2>
                    <p>Your order has been successfully placed. We're getting your plants ready to ship. You can track the status of your order inside your user profile.</p>
                    <a href="products.php" class="btn">Continue Shopping</a>
                </div>
            <?php else: ?>
                <h2>Checkout</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger" style="margin-bottom: 30px;">
                        <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="checkout-grid">
                    <!-- Checkout Form -->
                    <div class="checkout-form-box">
                        <form method="POST" action="checkout.php">
                            <input type="hidden" name="place_order" value="1">
                            
                            <h3>Shipping Information</h3>
                            <div class="form-group">
                                <label for="fullname">Full Name</label>
                                <input type="text" id="fullname" name="fullname" placeholder="John Doe" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="john@example.com" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Delivery Address</label>
                                <input type="text" id="address" name="address" placeholder="123 Green Lane" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" placeholder="Addis Ababa" required>
                                </div>
                                <div class="form-group">
                                    <label for="zip">ZIP / Postal Code</label>
                                    <input type="text" id="zip" name="zip" placeholder="1000" required>
                                </div>
                            </div>

                            <h3>Payment Method</h3>
                            <div class="payment-options">
                                <div class="payment-option">
                                    <input type="radio" id="pay_cash" name="payment_method" value="cash" checked>
                                    <label for="pay_cash">Cash on Delivery (COD)</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="pay_telebirr" name="payment_method" value="telebirr">
                                    <label for="pay_telebirr">telebirr</label>
                                </div>
                            </div>

                            <div id="telebirr-details-section" style="display: none; margin-top: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background: #fafafa;">
                                <h4 style="margin-top: 0; margin-bottom: 15px; color: #0b4d2c; font-size: 15px; font-weight: 600;">telebirr Payment</h4>
                                <div class="form-group">
                                    <label for="telebirr_phone">telebirr Registered Mobile Number</label>
                                    <input type="text" id="telebirr_phone" placeholder="09xxxxxxxx" style="background: #fff;">
                                </div>
                            </div>

                            <button type="submit" class="place-order-btn">Place My Order</button>
                        </form>
                    </div>

                    <!-- Side Order Summary -->
                    <div class="checkout-order-summary">
                        <h3>Your Order</h3>
                        
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="order-item-line">
                                <div>
                                    <span class="order-item-name"><?= htmlspecialchars($item['name']) ?></span>
                                    <span class="order-item-qty">x<?= $item['quantity'] ?></span>
                                </div>
                                <span class="order-item-price"><?= number_format($item['price'] * $item['quantity'], 2) ?> Birr</span>
                            </div>
                        <?php endforeach; ?>

                        <div class="summary-line" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                            <span>Subtotal</span>
                            <span><?= number_format($subtotal, 2) ?> Birr</span>
                        </div>
                        <div class="summary-line">
                            <span>Shipping</span>
                            <span><?= number_format($shipping, 2) ?> Birr</span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span><?= number_format($total, 2) ?> Birr</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include './includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var payCash = document.getElementById('pay_cash');
        var payTelebirr = document.getElementById('pay_telebirr');
        var telebirrSection = document.getElementById('telebirr-details-section');

        function toggleTelebirrSection() {
            if (payTelebirr.checked) {
                telebirrSection.style.display = 'block';
                telebirrSection.querySelectorAll('input').forEach(i => i.required = true);
            } else {
                telebirrSection.style.display = 'none';
                telebirrSection.querySelectorAll('input').forEach(i => {
                    i.required = false;
                    i.value = '';
                });
            }
        }

        payCash.addEventListener('change', toggleTelebirrSection);
        payTelebirr.addEventListener('change', toggleTelebirrSection);
    });
    </script>
</body>
</html>
