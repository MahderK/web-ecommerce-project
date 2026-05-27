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
        $stock = intval($_POST['stock_quantity'] ?? 50);
        $description = trim($_POST['description'] ?? 'A beautiful green companion.');

        if ($name === '' || $price <= 0 || $category === '') {
            $error = 'Please fill in all required fields (Name, Category, and a valid Price).';
        } else {
            // Get category ID or insert it
            $stmt = mysqli_prepare($conn, "SELECT category_id FROM categories WHERE category_name = ?");
            mysqli_stmt_bind_param($stmt, "s", $category);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($res);
            if ($row) {
                $category_id = $row['category_id'];
            } else {
                $stmt_ins = mysqli_prepare($conn, "INSERT INTO categories (category_name) VALUES (?)");
                mysqli_stmt_bind_param($stmt_ins, "s", $category);
                mysqli_stmt_execute($stmt_ins);
                $category_id = mysqli_insert_id($conn);
            }

            $image_val = $image ?: 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=400&q=80';
            $stmt_prod = mysqli_prepare($conn, "INSERT INTO products (name, price, category_id, badge, image_url, stock_quantity, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_prod, "sdissis", $name, $price, $category_id, $badge, $image_val, $stock, $description);
            mysqli_stmt_execute($stmt_prod);
            $message = 'Product added successfully!';
        }
    } elseif (isset($_POST['edit_product'])) {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $badge = trim($_POST['badge'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $stock = intval($_POST['stock_quantity'] ?? 50);
        $description = trim($_POST['description'] ?? 'A beautiful green companion.');

        if ($name === '' || $price <= 0 || $category === '') {
            $error = 'Please fill in all required fields (Name, Category, and a valid Price).';
        } else {
            // Get category ID or insert it
            $stmt = mysqli_prepare($conn, "SELECT category_id FROM categories WHERE category_name = ?");
            mysqli_stmt_bind_param($stmt, "s", $category);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($res);
            if ($row) {
                $category_id = $row['category_id'];
            } else {
                $stmt_ins = mysqli_prepare($conn, "INSERT INTO categories (category_name) VALUES (?)");
                mysqli_stmt_bind_param($stmt_ins, "s", $category);
                mysqli_stmt_execute($stmt_ins);
                $category_id = mysqli_insert_id($conn);
            }

            $image_val = $image ?: 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=400&q=80';
            $stmt_upd = mysqli_prepare($conn, "
                UPDATE products 
                SET name = ?, price = ?, category_id = ?, badge = ?, image_url = ?, stock_quantity = ?, description = ? 
                WHERE product_id = ?
            ");
            mysqli_stmt_bind_param($stmt_upd, "sdissisi", $name, $price, $category_id, $badge, $image_val, $stock, $description, $id);
            if (mysqli_stmt_execute($stmt_upd)) {
                $message = 'Product updated successfully!';
            } else {
                $error = 'Failed to update product.';
            }
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

// Fetch single product if editing
$edit_product = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt_fetch = mysqli_prepare($conn, "
        SELECT p.product_id AS id, p.name, p.price, c.category_name AS category, p.badge, p.image_url AS image, p.description, p.stock_quantity 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = ?
    ");
    mysqli_stmt_bind_param($stmt_fetch, "i", $edit_id);
    mysqli_stmt_execute($stmt_fetch);
    $res_fetch = mysqli_stmt_get_result($stmt_fetch);
    $edit_product = mysqli_fetch_assoc($res_fetch);
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
                <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin User') ?></span>
                <i class="fa-solid fa-circle-user fa-2x" style="color: #0b4d2c;"></i>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success" style="color: #2e7d32; background-color: #e8f5e9; border: 1px solid #c8e6c9; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="color: #c62828; background-color: #ffebee; border: 1px solid #ffcdd2; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Add / Edit Product Form -->
        <div class="form-container">
            <?php if ($edit_product): ?>
                <h3><i class="fa-solid fa-pen-to-square"></i> Edit Product: <?= htmlspecialchars($edit_product['name']) ?></h3>
                <form action="products.php?edit_id=<?= $edit_product['id'] ?>" method="POST">
                    <input type="hidden" name="edit_product" value="1">
                    <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
            <?php else: ?>
                <h3><i class="fa-solid fa-plus"></i> Add New Product</h3>
                <form action="products.php" method="POST">
                    <input type="hidden" name="add_product" value="1">
            <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" required placeholder="e.g. Swiss Cheese Plant" value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" required placeholder="e.g. 24.00" value="<?= htmlspecialchars($edit_product['price'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="" disabled <?= !$edit_product ? 'selected' : '' ?>>Select Category</option>
                            <option value="Indoor" <?= (($edit_product['category'] ?? '') === 'Indoor') ? 'selected' : '' ?>>Indoor</option>
                            <option value="Outdoor" <?= (($edit_product['category'] ?? '') === 'Outdoor') ? 'selected' : '' ?>>Outdoor</option>
                            <option value="Succulents" <?= (($edit_product['category'] ?? '') === 'Succulents') ? 'selected' : '' ?>>Succulents</option>
                            <option value="Tropical" <?= (($edit_product['category'] ?? '') === 'Tropical') ? 'selected' : '' ?>>Tropical</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="badge">Badge</label>
                        <select id="badge" name="badge">
                            <option value="" <?= (($edit_product['badge'] ?? '') === '') ? 'selected' : '' ?>>No Badge</option>
                            <option value="Best Seller" <?= (($edit_product['badge'] ?? '') === 'Best Seller') ? 'selected' : '' ?>>Best Seller</option>
                            <option value="New" <?= (($edit_product['badge'] ?? '') === 'New') ? 'selected' : '' ?>>New</option>
                            <option value="Popular" <?= (($edit_product['badge'] ?? '') === 'Popular') ? 'selected' : '' ?>>Popular</option>
                            <option value="Sale" <?= (($edit_product['badge'] ?? '') === 'Sale') ? 'selected' : '' ?>>Sale</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid" style="grid-template-columns: 1fr 1fr; margin-top: 15px;">
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="0" placeholder="e.g. 50" value="<?= htmlspecialchars($edit_product['stock_quantity'] ?? '50') ?>">
                    </div>
                    <div class="form-group">
                        <label for="image">Image URL</label>
                        <input type="url" id="image" name="image" placeholder="Leave empty for default image or paste a photo URL" value="<?= htmlspecialchars($edit_product['image'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px; margin-top: 15px;">
                    <label for="description">Product Description</label>
                    <textarea id="description" name="description" rows="3" style="width:100%; border: 1px solid #ddd; border-radius: 6px; padding: 10px; font-family: inherit; font-size:14px; outline:none; resize:vertical;" placeholder="Write description details (botanical name, care info, etc.)..."><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <?php if ($edit_product): ?>
                        <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Save Changes</button>
                        <a href="products.php" class="btn" style="background-color: #6c757d; margin-left: 10px; text-decoration: none; display: inline-block; padding: 10px 20px; text-align: center;">Cancel</a>
                    <?php else: ?>
                        <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Add Product</button>
                    <?php endif; ?>
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
                                <td class="action-links" style="display: flex; gap: 15px; align-items: center;">
                                    <a href="products.php?edit_id=<?= htmlspecialchars($p['id']) ?>" style="color: #0b4d2c; font-weight: 600; text-decoration: none;">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    
                                    <form action="products.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="delete_product" value="1">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                                        <button type="submit" class="delete-btn-link" style="background: none; border: none; color: #e63946; font-weight: 600; cursor: pointer; font-family: inherit;">
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
