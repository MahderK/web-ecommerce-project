<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
  <div class="container navbar">
    <div class="logo">Plantea.</div>

    <nav class="nav-links">
      <a href="index.php#index">Home</a>
      <a href="products.php#products">Shop</a>
      <a href="index.php#contact">Contact</a>
    </nav>

    <div class="icons">
      <span><i class="fa-solid fa-magnifying-glass"></i></span>
      <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="../admin/dashboard.php" title="Admin Dashboard" style="color: inherit;">
            <span><i class="fa-solid fa-user-gear"></i></span>
          </a>
        <?php else: ?>
          <span style="font-size: 14px; font-weight: 500; margin-right: 5px; color: #cbd5cb;">Hi, <?= htmlspecialchars(explode(' ', $_SESSION['username'])[0]) ?></span>
        <?php endif; ?>
        <a href="logout.php" title="Logout" style="color: inherit;">
          <span><i class="fa-solid fa-right-from-bracket"></i></span>
        </a>
      <?php else: ?>
        <a href="login.php" title="Login" style="color: inherit;">
          <span><i class="fa-regular fa-user"></i></span>
        </a>
      <?php endif; ?>
      <a href="cart.php" style="color: inherit;">
        <span><i class="fa-solid fa-cart-shopping"></i></span>
      </a>
    </div>
  </div>
</header>