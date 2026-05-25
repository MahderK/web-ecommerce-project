<?php
session_start();
include './includes/db.php';

$categories = ['All', 'Indoor', 'Outdoor', 'Succulents', 'Tropical'];

$products = get_products();

$sort   = $_GET['sort']     ?? 'default';
$search = strtolower(trim($_GET['search'] ?? ''));
$cat    = $_GET['category'] ?? 'All';

// Filter
$filtered = array_filter($products, function($p) use ($search, $cat) {
  $matchSearch = $search === '' || str_contains(strtolower($p['name']), $search);
  $matchCat    = $cat === 'All' || $p['category'] === $cat;
  return $matchSearch && $matchCat;
});

// Sort
if ($sort === 'price-asc')  usort($filtered, fn($a,$b) => $a['price'] <=> $b['price']);
if ($sort === 'price-desc') usort($filtered, fn($a,$b) => $b['price'] <=> $a['price']);
if ($sort === 'name')       usort($filtered, fn($a,$b) => strcmp($a['name'], $b['name']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Browse the full Plantea plant collection. Filter by category, search by name, and find your perfect plant today.">
  <title>Shop – Plantea</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/shop.css">
  <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include './includes/navbar.php'; ?>

<!-- Shop Hero Banner -->
<section class="shop-hero">
  <div class="container">
    <p class="shop-eyebrow">Our Collection</p>
    <h1>Find Your <span>Perfect Plant</span></h1>
    <p class="shop-sub">From air-purifying indoor plants to lush tropical greens — we've got something for every space.</p>
  </div>
</section>

<!-- Toolbar -->
<section class="shop-body">
  <div class="container">
    <form class="shop-toolbar" method="GET" action="products.php" id="shop-form">
      <!-- Search -->
      <div class="search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" placeholder="Search plants…" value="<?= htmlspecialchars($search) ?>" id="search-input">
      </div>

      <!-- Category Pills -->
      <div class="category-pills">
        <?php foreach ($categories as $c): ?>
          <button type="submit" name="category" value="<?= $c ?>"
                  class="pill <?= $cat === $c ? 'active' : '' ?>">
            <?= $c ?>
          </button>
        <?php endforeach; ?>
        <!-- preserve sort & search when switching categories -->
        <input type="hidden" name="sort"   value="<?= htmlspecialchars($sort) ?>">
      </div>

      <!-- Sort -->
      <div class="sort-wrap">
        <label for="sort-select"><i class="fa-solid fa-arrow-up-wide-short"></i></label>
        <select name="sort" id="sort-select" onchange="this.form.submit()">
          <option value="default"    <?= $sort==='default'    ? 'selected':'' ?>>Default</option>
          <option value="name"       <?= $sort==='name'       ? 'selected':'' ?>>Name A–Z</option>
          <option value="price-asc"  <?= $sort==='price-asc'  ? 'selected':'' ?>>Price: Low → High</option>
          <option value="price-desc" <?= $sort==='price-desc' ? 'selected':'' ?>>Price: High → Low</option>
        </select>
        <!-- preserve category & search when sorting -->
        <input type="hidden" name="category" value="<?= htmlspecialchars($cat) ?>">
      </div>
    </form>

    <!-- Results count -->
    <p class="results-count">
      Showing <strong><?= count($filtered) ?></strong> plant<?= count($filtered) !== 1 ? 's' : '' ?>
      <?= $cat !== 'All' ? "in <strong>$cat</strong>" : '' ?>
      <?= $search !== '' ? 'for &ldquo;<strong>' . htmlspecialchars($search) . '</strong>&rdquo;' : '' ?>
    </p>

    <!-- Product Grid -->
    <?php if (count($filtered) === 0): ?>
      <div class="no-results">
        <i class="fa-solid fa-leaf"></i>
        <h3>No plants found</h3>
        <p>Try a different search or category.</p>
        <a href="products.php" class="btn">Clear Filters</a>
      </div>
    <?php else: ?>
    <div class="shop-grid">
      <?php foreach ($filtered as $p): ?>
      <div class="shop-card" data-category="<?= $p['category'] ?>">
        <?php if ($p['badge']): ?>
          <span class="badge badge-<?= strtolower(str_replace(' ','-',$p['badge'])) ?>"><?= $p['badge'] ?></span>
        <?php endif; ?>

        <div class="shop-card-img">
          <img src="<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
          <div class="card-overlay">
            <a href="product.php?id=<?= $p['id'] ?>" class="overlay-btn"><i class="fa-solid fa-eye"></i> Quick View</a>
          </div>
        </div>

        <div class="shop-card-body">
          <span class="card-category"><?= $p['category'] ?></span>
          <h3><?= htmlspecialchars($p['name']) ?></h3>
          <div class="card-footer">
            <span class="card-price">$<?= number_format($p['price'], 2) ?></span>
            <form method="POST" action="cart.php">
              <input type="hidden" name="product_id"   value="<?= $p['id'] ?>">
              <input type="hidden" name="product_name"  value="<?= htmlspecialchars($p['name']) ?>">
              <input type="hidden" name="product_price" value="<?= $p['price'] ?>">
              <input type="hidden" name="product_image" value="<?= $p['image'] ?>">
              <input type="hidden" name="quantity"      value="1">
              <button type="submit" class="add-to-cart-btn" name="add_to_cart">
                <i class="fa-solid fa-cart-plus"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php include './includes/footer.php'; ?>

</body>
</html>