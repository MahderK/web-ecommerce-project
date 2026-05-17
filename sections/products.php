<?php
$products = [
  [
    "name" => "Monstera Deliciosa",
    "price" => 24.00,
    "image" => "https://images.unsplash.com/photo-1501004318641-b39e6451bec6"
  ],
  [
    "name" => "Snake Plant",
    "price" => 18.00,
    "image" => "https://images.unsplash.com/photo-1512428813834-c702c7702b78"
  ],
  [
    "name" => "Fiddle Leaf Fig",
    "price" => 22.00,
    "image" => "https://images.unsplash.com/photo-1466692476868-aef1dfb1e735"
  ],
  [
    "name" => "Aloe Vera",
    "price" => 14.00,
    "image" => "https://images.unsplash.com/photo-1416879595882-3373a0480b5b"
  ]
];
?>

<section class="products" id="shop">
  <div class="container">

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

  </div>
</section>