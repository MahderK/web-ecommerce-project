<?php
include_once __DIR__ . '/../includes/db.php';
$products = array_slice(get_products(), 0, 4);
?>

<section class="products" id="shop" >
  <div class="container" >

    <div class="section-title">
      <h2>Our Products</h2>
    </div>

    <div class="product-grid">

      <?php foreach ($products as $product): ?>
        <div class="product-card">

          <div class="product-image">
            <img src="<?= $product['image']; ?>" alt="<?= $product['name']; ?>">
          </div>

          <h3><?= $product['name']; ?></h3>

          <div class="price">
            $<?= number_format($product['price'], 2); ?>
          </div>

        </div>
      <?php endforeach; ?>

    </div>

    <div class="products-cta">
      <a href="products.php" class="btn">View More Products</a>
    </div>

  </div>
</section>