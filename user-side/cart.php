<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Plantea</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include './includes/navbar.php'; ?>

    <section class="cart-page">
        <div class="container">
            <h2>Your Shopping Cart</h2>
            
            <div class="cart-container">
                <!-- Cart Items -->
                <div class="cart-items">
                    <!-- Item 1 -->
                    <div class="cart-item">
                        <div class="item-img">
                            <img src="https://images.unsplash.com/photo-1501004318641-b39e6451bec6?q=80&w=1000&auto=format&fit=crop" alt="Monstera Deliciosa">
                        </div>
                        <div class="item-details">
                            <h3>Monstera Deliciosa</h3>
                            <p>Category: Indoor Plants</p>
                            <div class="item-price">Birr 24.00</div>
                        </div>
                        <div class="quantity-control">
                            <input type="number" value="1" min="1">
                            <button class="remove-btn"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                    
                    <!-- Item 2 -->
                    <div class="cart-item">
                        <div class="item-img">
                            <img src="https://images.unsplash.com/photo-1512428813834-c702c7702b78?q=80&w=1000&auto=format&fit=crop" alt="Snake Plant">
                        </div>
                        <div class="item-details">
                            <h3>Snake Plant</h3>
                            <p>Category: Indoor Plants</p>
                            <div class="item-price">Birr 18.00</div>
                        </div>
                        <div class="quantity-control">
                            <input type="number" value="2" min="1">
                            <button class="remove-btn"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>Birr 60.00</span>
                    </div>
                    <div class="summary-line">
                        <span>Shipping</span>
                        <span>Birr 5.00</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>Birr 65.00</span>
                    </div>
                    <button class="checkout-btn">Proceed to Checkout</button>
                </div>
            </div>
        </div>
    </section>

    <?php include './includes/footer.php'; ?>
</body>
</html>