<?php
session_start();
include_once './includes/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: products.php");
    exit();
}

// Fetch single product
$stmt = mysqli_prepare($conn, "
    SELECT p.product_id AS id, p.name, p.price, c.category_name AS category, p.badge, p.image_url AS image, p.description, p.stock_quantity 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.product_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);

if (!$product) {
    header("Location: products.php");
    exit();
}

// Handle review submission
$review_error = '';
$review_success = isset($_GET['success']) ? 'Your review has been submitted successfully!' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $review_error = 'You must be logged in to leave a review.';
    } else {
        $rating = intval($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');
        $user_id = $_SESSION['user_id'];
        
        if ($rating < 1 || $rating > 5) {
            $review_error = 'Please select a valid rating between 1 and 5.';
        } elseif ($comment === '') {
            $review_error = 'Please write a comment for your review.';
        } else {
            // Check if user already reviewed this product to avoid duplicate entries
            $check_stmt = mysqli_prepare($conn, "SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ?");
            mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $id);
            mysqli_stmt_execute($check_stmt);
            $check_res = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_res) > 0) {
                // Update existing review
                $stmt_upd = mysqli_prepare($conn, "UPDATE reviews SET rating = ?, comment = ?, created_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
                mysqli_stmt_bind_param($stmt_upd, "isii", $rating, $comment, $user_id, $id);
                $executed = mysqli_stmt_execute($stmt_upd);
            } else {
                // Insert new review
                $stmt_ins = mysqli_prepare($conn, "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_ins, "iiis", $user_id, $id, $rating, $comment);
                $executed = mysqli_stmt_execute($stmt_ins);
            }
            
            if ($executed) {
                header("Location: product.php?id=" . $id . "&success=1");
                exit();
            } else {
                $review_error = 'Something went wrong. Please try again.';
            }
        }
    }
}

// Fetch all reviews for this product
$stmt_rev = mysqli_prepare($conn, "
    SELECT r.rating, r.comment, r.created_at, u.username 
    FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.user_id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
mysqli_stmt_bind_param($stmt_rev, "i", $id);
mysqli_stmt_execute($stmt_rev);
$res_rev = mysqli_stmt_get_result($stmt_rev);

$reviews = [];
$total_rating = 0;
while ($row = mysqli_fetch_assoc($res_rev)) {
    $reviews[] = $row;
    $total_rating += $row['rating'];
}
$reviews_count = count($reviews);
$avg_rating = $reviews_count > 0 ? round($total_rating / $reviews_count, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View detail specs, read reviews, and order <?= htmlspecialchars($product['name']) ?> on Plantea plant shop.">
    <title><?= htmlspecialchars($product['name']) ?> – Plantea</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/product.css">
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include './includes/navbar.php'; ?>

<main class="product-detail-section">
    <div class="container">
        
        <?php if ($review_success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($review_success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($review_error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($review_error) ?>
            </div>
        <?php endif; ?>

        <!-- Product Presentation -->
        <div class="detail-grid">
            
            <!-- Gallery Panel -->
            <div class="product-gallery">
                <?php if ($product['badge']): ?>
                    <span class="gallery-badge"><?= htmlspecialchars($product['badge']) ?></span>
                <?php endif; ?>
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>

            <!-- Configuration & Specs -->
            <div class="product-info">
                <span class="info-category"><?= htmlspecialchars($product['category']) ?></span>
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <!-- Ratings Summary -->
                <div class="rating-summary">
                    <div class="stars">
                        <?php 
                        $full_stars = floor($avg_rating);
                        $has_half = ($avg_rating - $full_stars) >= 0.5;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $full_stars) {
                                echo '<i class="fa-solid fa-star"></i>';
                            } elseif ($i == $full_stars + 1 && $has_half) {
                                echo '<i class="fa-solid fa-star-half-stroke"></i>';
                            } else {
                                echo '<i class="fa-regular fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <span class="rating-text">
                        <strong><?= $avg_rating ?></strong> / 5.0 (<?= $reviews_count ?> customer review<?= $reviews_count !== 1 ? 's' : '' ?>)
                    </span>
                </div>

                <div class="info-price">$<?= number_format($product['price'], 2) ?></div>
                
                <p class="info-desc">
                    <?= htmlspecialchars($product['description'] ?: 'A beautiful addition to any plant lover\'s home. These organic plants are carefully nurtured, low maintenance, and perfect for purifying the air inside your living space.') ?>
                </p>

                <!-- Plant Specs Table -->
                <div class="meta-list">
                    <div class="meta-item">
                        <span class="meta-label">Stock Status:</span>
                        <span class="meta-val">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="stock-tag stock-in">In Stock (<?= $product['stock_quantity'] ?> left)</span>
                            <?php else: ?>
                                <span class="stock-tag stock-out">Out Of Stock</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Botanical Name:</span>
                        <span class="meta-val"><em>Plantea Genus</em></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Care Difficulty:</span>
                        <span class="meta-val">Easy to Medium</span>
                    </div>
                </div>

                <!-- Add to Cart Form -->
                <?php if ($product['stock_quantity'] > 0): ?>
                    <form method="POST" action="cart.php" class="cart-form">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
                        <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                        <input type="hidden" name="product_image" value="<?= htmlspecialchars($product['image']) ?>">
                        <input type="hidden" name="add_to_cart" value="1">
                        
                        <div class="qty-input-wrap">
                            <button type="button" onclick="changeQty(-1)">-</button>
                            <input type="number" id="qty-input" name="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                            <button type="button" onclick="changeQty(1)">+</button>
                        </div>

                        <button type="submit" class="add-cart-btn">
                            <i class="fa-solid fa-cart-plus"></i> Add to Shopping Cart
                        </button>
                    </form>
                <?php else: ?>
                    <button class="add-cart-btn" disabled>
                        Out of Stock
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews & Ratings Panel -->
        <div class="reviews-section">
            <h2>Customer Reviews & Feedback</h2>
            
            <div class="reviews-container">
                
                <!-- Left Column: Review Feed -->
                <div class="reviews-list">
                    <?php if (empty($reviews)): ?>
                        <div class="no-reviews">
                            <i class="fa-solid fa-comments fa-2x muted-icon"></i>
                            No reviews have been left for this plant yet. Be the first to share your experience!
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <span class="reviewer-name"><?= htmlspecialchars($rev['username']) ?></span>
                                    <span class="review-date"><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                                </div>
                                <div class="review-stars">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rev['rating']) {
                                            echo '<i class="fa-solid fa-star"></i>';
                                        } else {
                                            echo '<i class="fa-regular fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <p class="review-comment"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Submission Block -->
                <div class="review-form-box">
                    <h3>Leave a Review</h3>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="product.php?id=<?= $product['id'] ?>" method="POST">
                            <input type="hidden" name="submit_review" value="1">
                            
                            <label>Your Rating:</label>
                            <div class="rating-select">
                                <input type="radio" id="star5" name="rating" value="5" checked><label for="star5" class="fa-solid fa-star"></label>
                                <input type="radio" id="star4" name="rating" value="4"><label for="star4" class="fa-solid fa-star"></label>
                                <input type="radio" id="star3" name="rating" value="3"><label for="star3" class="fa-solid fa-star"></label>
                                <input type="radio" id="star2" name="rating" value="2"><label for="star2" class="fa-solid fa-star"></label>
                                <input type="radio" id="star1" name="rating" value="1"><label for="star1" class="fa-solid fa-star"></label>
                            </div>

                            <label for="comment">Your Review:</label>
                            <textarea id="comment" name="comment" placeholder="Write about your experience with this plant..." required></textarea>

                            <button type="submit" class="submit-review-btn">Submit My Review</button>
                        </form>
                    <?php else: ?>
                        <div class="login-prompt">
                            <i class="fa-solid fa-lock fa-2x muted-icon"></i>
                            Please <a href="login.php">login to your account</a> to leave a review and rate this product.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
    function changeQty(amount) {
        var input = document.getElementById('qty-input');
        var val = parseInt(input.value) + amount;
        var max = parseInt(input.getAttribute('max'));
        if (val < 1) val = 1;
        if (val > max) val = max;
        input.value = val;
    }
</script>

<?php include './includes/footer.php'; ?>

</body>
</html>
