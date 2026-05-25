<?php
session_start();
include './includes/db.php';

$categories = ['All', 'Indoor', 'Outdoor', 'Succulents', 'Tropical'];

$products = [
  ['id' => 1, 'name' => 'Monstera Deliciosa',  'price' => 24.00, 'category' => 'Indoor',    'badge' => 'Best Seller', 'image' => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?w=400&q=80'],
  ['id' => 2, 'name' => 'Snake Plant',          'price' => 18.00, 'category' => 'Indoor',    'badge' => '',            'image' => 'https://images.unsplash.com/photo-1512428813834-c702c7702b78?w=400&q=80'],
  ['id' => 3, 'name' => 'Fiddle Leaf Fig',      'price' => 22.00, 'category' => 'Indoor',    'badge' => 'New',         'image' => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=400&q=80'],
  ['id' => 4, 'name' => 'Aloe Vera',            'price' => 14.00, 'category' => 'Succulents','badge' => '',            'image' => 'https://images.unsplash.com/photo-1616677102255-f6f5d4e7749d?w=400&q=80'],
  ['id' => 5, 'name' => 'Bird of Paradise',     'price' => 35.00, 'category' => 'Tropical',  'badge' => 'Popular',     'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80'],
  ['id' => 6, 'name' => 'Pothos',               'price' => 12.00, 'category' => 'Indoor',    'badge' => '',            'image' => 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=400&q=80'],
  ['id' => 7, 'name' => 'Cactus Mix',           'price' => 10.00, 'category' => 'Succulents','badge' => 'Sale',        'image' => 'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=400&q=80'],
  ['id' => 8, 'name' => 'Peace Lily',           'price' => 20.00, 'category' => 'Indoor',    'badge' => '',            'image' => 'https://images.unsplash.com/photo-1591958911259-bee2173bdccc?w=400&q=80'],
  ['id' => 9, 'name' => 'Bamboo Palm',          'price' => 28.00, 'category' => 'Outdoor',   'badge' => '',            'image' => 'https://images.unsplash.com/photo-1584467735871-8e85353a8413?w=400&q=80'],
  ['id' => 10,'name' => 'Lavender',             'price' => 16.00, 'category' => 'Outdoor',   'badge' => 'New',         'image' => 'https://images.unsplash.com/photo-1444930694458-01babf71870c?w=400&q=80'],
  ['id' => 11,'name' => 'Rubber Plant',         'price' => 26.00, 'category' => 'Tropical',  'badge' => '',            'image' => 'https://images.unsplash.com/photo-1620803366004-119b57f54cd6?w=400&q=80'],
  ['id' => 12,'name' => 'Echeveria Succulent',  'price' => 9.00,  'category' => 'Succulents','badge' => 'Sale',        'image' => 'https://images.unsplash.com/photo-1555173274-ae64e5a4f51d?w=400&q=80'],
];

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