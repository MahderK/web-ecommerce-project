<?php
// Example future products array
// Later this will come from MySQL

$products = [
    [
        "name" => "Monstera Deliciosa",
        "price" => 24,
        "image" => "assets/images/plant1.jpg"
    ],
    [
        "name" => "Snake Plant",
        "price" => 18,
        "image" => "assets/images/plant2.jpg"
    ],
    [
        "name" => "Fiddle Leaf Fig",
        "price" => 22,
        "image" => "assets/images/plant3.jpg"
    ],
    [
        "name" => "Aloe Vera",
        "price" => 14,
        "image" => "assets/images/plant4.jpg"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantea</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ================= HEADER ================= -->

<header class="header">

    <div class="container navbar">

        <div class="logo">
            <a href="index.php">Plantea.</a>
        </div>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php">Shop</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
        </nav>

        <div class="nav-icons">
            <a href="#">🔍</a>
            <a href="login.php">👤</a>
            <a href="cart.php">🛒</a>
        </div>

    </div>

</header>

<!-- ================= HERO ================= -->

<section class="hero">

    <div class="container hero-content">

        <div class="hero-text">

            <h1>
                Bring The Nature Close To You
            </h1>

            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit.
            </p>

            <a href="products.php" class="btn">
                Shop Now
            </a>

        </div>

        <div class="hero-image">
            <img src="assets/images/hero-plant.png" alt="">
        </div>

    </div>

</section>

<!-- ================= FEATURES ================= -->

<section class="features">

    <div class="container feature-grid">

        <div class="feature-card">
            <h3>Free Delivery</h3>
            <p>Lorem ipsum dolor sit amet.</p>
        </div>

        <div class="feature-card">
            <h3>Safe Payment</h3>
            <p>Lorem ipsum dolor sit amet.</p>
        </div>

        <div class="feature-card">
            <h3>Friendly Services</h3>
            <p>Lorem ipsum dolor sit amet.</p>
        </div>

    </div>

</section>

<!-- ================= BANNERS ================= -->

<section class="banners">

    <div class="container banner-grid">

        <div class="banner">

            <div>
                <small>Big Sale On Indoor</small>
                <h2>Indoor Plants</h2>

                <a href="products.php">
                    Shop Now →
                </a>
            </div>

            <img src="assets/images/banner1.png" alt="">

        </div>

        <div class="banner">

            <div>
                <small>Top Collection</small>
                <h2>Herbal Plants</h2>

                <a href="products.php">
                    Shop Now →
                </a>
            </div>

            <img src="assets/images/banner2.png" alt="">

        </div>

    </div>

</section>

<!-- ================= PRODUCTS ================= -->

<section class="products">

    <div class="container">

        <div class="section-title">
            <h2>Our Products</h2>
        </div>

        <div class="product-grid">

            <?php foreach($products as $product) : ?>

                <div class="product-card">

                    <div class="product-image">
                        <img src="<?= $product['image']; ?>" alt="">
                    </div>

                    <h3>
                        <?= $product['name']; ?>
                    </h3>

                    <p class="price">
                        $<?= $product['price']; ?>
                    </p>

                    <a href="product.php?id=1" class="btn-small">
                        View Product
                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<!-- ================= CTA ================= -->

<section class="cta">

    <div class="container">

        <div class="cta-box">

            <div class="cta-text">

                <h2>
                    Grow Plant For A Better Life
                </h2>

                <p>
                    Lorem ipsum dolor sit amet consectetur adipisicing elit.
                </p>

                <a href="#" class="btn">
                    Learn More
                </a>

            </div>

            <div class="cta-images">

                <div class="circle-image">
                    <img src="assets/images/plant-circle1.png" alt="">
                </div>

                <div class="circle-image">
                    <img src="assets/images/plant-circle2.png" alt="">
                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================= CARE ================= -->

<section class="care">

    <div class="container">

        <div class="section-title">
            <h2>Take Care Of Your Plants</h2>
        </div>

        <div class="care-grid">

            <div class="care-card">
                <h3>Humidity Control</h3>
                <p>Lorem ipsum dolor sit amet.</p>
            </div>

            <div class="care-card">
                <h3>Plant Anticipation</h3>
                <p>Lorem ipsum dolor sit amet.</p>
            </div>

            <div class="care-card">
                <h3>Pruning Weeds</h3>
                <p>Lorem ipsum dolor sit amet.</p>
            </div>

            <div class="care-card">
                <h3>Feeding Plants</h3>
                <p>Lorem ipsum dolor sit amet.</p>
            </div>

        </div>

    </div>

</section>

<!-- ================= BLOG ================= -->

<section class="blog">

    <div class="container">

        <div class="blog-box">

            <div class="blog-image">
                <img src="assets/images/blog.jpg" alt="">
            </div>

            <div class="blog-content">

                <h2>
                    Learn How To Grow Your Plants Better
                </h2>

                <p>
                    Lorem ipsum dolor sit amet consectetur adipisicing elit.
                </p>

                <a href="#" class="btn">
                    Read More
                </a>

            </div>

        </div>

    </div>

</section>

<!-- ================= NEWSLETTER ================= -->

<section class="newsletter">

    <div class="container">

        <h2>
            Join Our Newsletter
        </h2>

        <form action="" method="POST">

            <input 
                type="email" 
                name="email"
                placeholder="Enter your email"
            >

            <button type="submit">
                Subscribe
            </button>

        </form>

    </div>

</section>

<!-- ================= FOOTER ================= -->

<footer class="footer">

    <div class="container footer-grid">

        <div>
            <h3>Plantea.</h3>

            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit.
            </p>
        </div>

        <div>

            <h3>Pages</h3>

            <a href="index.php">Home</a>
            <a href="products.php">Shop</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>

        </div>

        <div>

            <h3>Contact</h3>

            <p>support@plantea.com</p>
            <p>+123 456 7890</p>

        </div>

    </div>

    <div class="copyright">
        © <?= date("Y"); ?> Plantea. All Rights Reserved.
    </div>

</footer>

</body>
</html>