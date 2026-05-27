<?php
include_once '../user-side/includes/auth.php';
require_admin();
include_once '../user-side/includes/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $badge = trim($_POST['badge'] ?? '');
        $image = trim($_POST['image'] ?? '');

        if ($name === '' || $price <= 0 || $category === '') {
            $error = 'Please fill in all required fields (Name, Category, and a valid Price).';
        } else {
            add_product($name, $price, $category, $badge, $image);
            $message = 'Product added successfully!';
        }
    } elseif (isset($_POST['delete_product'])) {
        $id = intval($_POST['id'] ?? 0);
        if (delete_product($id)) {
            $message = 'Product deleted successfully!';
        } else {
            $error = 'Product not found or could not be deleted.';
        }
    }
}

$products = get_products();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Plantea Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>

    <?php include './includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2>Manage Products</h2>
            <div class="admin-profile">
                <span>Admin User</span>
                <i class="fa-solid fa-circle-user fa-2x" style="color: #0b4d2c;"></i>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Add Product Form -->
        <div class="form-container">
            <h3><i class="fa-solid fa-plus"></i> Add New Product</h3>
            <form action="products.php" method="POST">
                <input type="hidden" name="add_product" value="1">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" required placeholder="e.g. Swiss Cheese Plant">
                    </div>
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" required placeholder="e.g. 24.00">
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="" disabled selected>Select Category</option>
                            <option value="Indoor">Indoor</option>
                            <option value="Outdoor">Outdoor</option>
                            <option value="Succulents">Succulents</option>
                            <option value="Tropical">Tropical</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="badge">Badge</label>
                        <select id="badge" name="badge">
                            <option value="">No Badge</option>
                            <option value="Best Seller">Best Seller</option>
                            <option value="New">New</option>
                            <option value="Popular">Popular</option>
                            <option value="Sale">Sale</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="image">Image URL</label>
                    <input type="url" id="image" name="image" placeholder="Leave empty for default image or paste a photo URL">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Add Product</button>
                </div>
            </form>
        </div>

        <!-- Products List -->
        <div class="table-container">
            <div class="table-header">
                <h3>All Products (<?= count($products) ?>)</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Badge</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #888;">No products found in the catalog.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($p['id']) ?></td>
                                <td>
                                    <img class="product-thumb" src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                                </td>
                                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                                <td><?= htmlspecialchars($p['category']) ?></td>
                                <td>$<?= number_format($p['price'], 2) ?></td>
                                <td>
                                    <?php if (!empty($p['badge'])): ?>
                                        <span class="admin-badge admin-badge-<?= strtolower(str_replace(' ', '-', $p['badge'])) ?>">
                                            <?= htmlspecialchars($p['badge']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #bbb; font-size: 12px;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-links">
                                    <form action="products.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="delete_product" value="1">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                                        <button type="submit" class="delete-btn-link">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
