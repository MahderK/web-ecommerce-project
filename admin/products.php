<?php
include './includes/auth_check.php';

$products_file = '../data/products.json';
$products = [];
$message = '';

// Load products from JSON
if (file_exists($products_file)) {
    $json_data = file_get_contents($products_file);
    $data = json_decode($json_data, true);
    $products = $data['products'] ?? [];
}

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $new_product = [
        'id' => count($products) + 1,
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => (float)($_POST['price'] ?? 0),
        'image' => $_POST['image'] ?? '',
        'category' => $_POST['category'] ?? '',
        'stock' => (int)($_POST['stock'] ?? 0),
        'created_at' => date('Y-m-d')
    ];
    $products[] = $new_product;
    file_put_contents($products_file, json_encode(['products' => $products], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $message = 'Product added successfully!';
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $products = array_filter($products, function($p) use ($delete_id) {
        return $p['id'] != $delete_id;
    });
    $products = array_values($products);
    file_put_contents($products_file, json_encode(['products' => $products], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $message = 'Product deleted successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Plantea Admin</title>
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: #f5f5f5;
        }
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: #0b4d2c;
            color: white;
            padding: 20px;
            position: fixed;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar h3 {
            margin-bottom: 30px;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li {
            margin: 15px 0;
        }
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .btn-add {
            background: #0b4d2c;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-add:hover {
            background: #083b22;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }
        .modal-content h2 {
            margin-bottom: 20px;
            color: #0b4d2c;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-submit {
            flex: 1;
            background: #0b4d2c;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-cancel {
            flex: 1;
            background: #ddd;
            color: #333;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .products-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .products-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .products-table th,
        .products-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .products-table th {
            font-weight: 600;
            color: #0b4d2c;
            background: #f9f9f9;
        }
        .btn-edit,
        .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit {
            background: #2196F3;
            color: white;
        }
        .btn-delete {
            background: #f44336;
            color: white;
        }
        .message {
            padding: 15px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Admin Panel</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="products.php" style="background: rgba(255, 255, 255, 0.2);"><i class="fa-solid fa-box"></i> Products</a></li>
                <li><a href="orders.php"><i class="fa-solid fa-receipt"></i> Orders</a></li>
                <li><a href="logout.php" style="color: #ff6b6b; margin-top: 30px;"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Product Management</h1>
                <button class="btn-add" onclick="openAddForm()">+ Add Product</button>
            </div>

            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #999;">No products yet. Add your first product!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <h2>Add New Product</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock Quantity *</label>
                    <input type="number" id="stock" name="stock" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <input type="text" id="category" name="category" required>
                </div>
                
                <div class="form-group">
                    <label for="image">Image Filename</label>
                    <input type="text" id="image" name="image" placeholder="e.g., monstera.jpg">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Add Product</button>
                    <button type="button" class="btn-cancel" onclick="closeAddForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddForm() {
            document.getElementById('productModal').classList.add('active');
        }
        
        function closeAddForm() {
            document.getElementById('productModal').classList.remove('active');
        }
    </script>
</body>
</html>