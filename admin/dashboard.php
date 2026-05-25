<?php include_once '../user-side/includes/db.php'; ?>
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
                <span>Admin User</span>
                <i class="fa-solid fa-circle-user fa-2x" style="color: #0b4d2c;"></i>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <div class="card-info">
                    <h3>1,250</h3>
                    <span>Total Orders</span>
                </div>
                <div class="card-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>Birr 45K</h3>
                    <span>Total Revenue</span>
                </div>
                <div class="card-icon">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3><?= count(get_products()) ?></h3>
                    <span>Products</span>
                </div>
                <div class="card-icon">
                    <i class="fa-solid fa-leaf"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>56</h3>
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
                    <tr>
                        <td>#ORD-001</td>
                        <td>Abebe Bekele</td>
                        <td>Oct 24, 2026</td>
                        <td>Birr 120.00</td>
                        <td><span class="status pending">Pending</span></td>
                        <td class="action-links">
                            <a href="#">View</a>
                        </td>
                    </tr>
                    <tr>
                        <td>#ORD-002</td>
                        <td>Aster Aweke</td>
                        <td>Oct 23, 2026</td>
                        <td>Birr 45.00</td>
                        <td><span class="status processing">Processing</span></td>
                        <td class="action-links">
                            <a href="#">View</a>
                        </td>
                    </tr>
                    <tr>
                        <td>#ORD-003</td>
                        <td>Ethiopian Dude</td>
                        <td>Oct 22, 2026</td>
                        <td>Birr 210.00</td>
                        <td><span class="status completed">Completed</span></td>
                        <td class="action-links">
                            <a href="#">View</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
