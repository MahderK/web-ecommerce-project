<?php 
include_once '../user-side/includes/auth.php';
require_admin();
include_once '../user-side/includes/db.php'; 

// Fetch Stats
$ordersCountQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
$totalOrders = mysqli_fetch_assoc($ordersCountQuery)['count'];

$revenueQuery = mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders");
$totalRevenue = mysqli_fetch_assoc($revenueQuery)['total'];

$productsCountQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
$totalProducts = mysqli_fetch_assoc($productsCountQuery)['count'];

$pendingOrdersQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE order_status = 'Pending'");
$pendingOrders = mysqli_fetch_assoc($pendingOrdersQuery)['count'];

// Fetch Recent Orders
$recentOrdersQuery = mysqli_query($conn, "
    SELECT o.order_id, u.username, o.created_at, o.total_amount, o.order_status 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Plantea</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>

    <?php include './includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2>Dashboard Overview</h2>
            <div class="admin-profile">
                <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin User') ?></span>
                <i class="fa-solid fa-circle-user fa-2x" style="color: #0b4d2c;"></i>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <div class="card-info">
                    <h3><?= number_format($totalOrders) ?></h3>
                    <span>Total Orders</span>
                </div>
                <div class="card-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3><?= number_format($totalRevenue, 2) ?> Birr</h3>
                    <span>Total Revenue</span>
                </div>
                <div class="card-icon">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3><?= number_format($totalProducts) ?></h3>
                    <span>Products</span>
                </div>
                <div class="card-icon">
                    <i class="fa-solid fa-leaf"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3><?= number_format($pendingOrders) ?></h3>
                    <span>Pending Orders</span>
                </div>
                <div class="card-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3>Recent Orders</h3>
                <a href="orders.php" class="btn">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recentOrdersQuery) === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #888; padding: 20px;">No orders placed yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php while ($ord = mysqli_fetch_assoc($recentOrdersQuery)): ?>
                            <tr>
                                <td>#ORD-<?= str_pad($ord['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($ord['username'] ?? 'Guest Customer') ?></td>
                                <td><?= date('M d, Y', strtotime($ord['created_at'])) ?></td>
                                <td><?= number_format($ord['total_amount'], 2) ?> Birr</td>
                                <td>
                                    <span class="status <?= strtolower($ord['order_status']) ?>">
                                        <?= htmlspecialchars($ord['order_status']) ?>
                                    </span>
                                </td>
                                <td class="action-links">
                                    <a href="orders.php?order_id=<?= $ord['order_id'] ?>">Manage</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
