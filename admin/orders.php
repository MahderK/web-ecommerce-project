<?php
include_once '../user-side/includes/auth.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Plantea Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://kit.fontawesome.com/80f4af3029.js" crossorigin="anonymous"></script>
</head>
<body>

    <?php include './includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2>Manage Orders</h2>
            <div class="admin-profile">
                <span>Admin User</span>
                <i class="fa-solid fa-circle-user fa-2x" style="color: #0b4d2c;"></i>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3>All Orders</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#ORD-001</td>
                        <td>Abebe Bekele</td>
                        <td>3 Items</td>
                        <td>Birr 120.00</td>
                        <td>
                            <select name="status">
                                <option value="pending" selected>Pending</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </td>
                        <td>Oct 24, 2026</td>
                        <td class="action-links">
                            <a href="#">Edit</a>
                            <a href="#" class="delete">Delete</a>
                        </td>
                    </tr>
                    <tr>
                        <td>#ORD-002</td>
                        <td>Aster Aweke</td>
                        <td>1 Item</td>
                        <td>Birr 45.00</td>
                        <td>
                            <select name="status">
                                <option value="pending">Pending</option>
                                <option value="processing" selected>Processing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </td>
                        <td>Oct 23, 2026</td>
                        <td class="action-links">
                            <a href="#">Edit</a>
                            <a href="#" class="delete">Delete</a>
                        </td>
                    </tr>
                    <tr>
                        <td>#ORD-003</td>
                        <td>Ethiopian Dude</td>
                        <td>5 Items</td>
                        <td>Birr 210.00</td>
                        <td>
                            <select name="status">
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="completed" selected>Completed</option>
                            </select>
                        </td>
                        <td>Oct 22, 2026</td>
                        <td class="action-links">
                            <a href="#">Edit</a>
                            <a href="#" class="delete">Delete</a>
                        </td>
                    </tr>
                    <tr>
                        <td>#ORD-004</td>
                        <td>Ethiopian Chick</td>
                        <td>2 Items</td>
                        <td>Birr 85.00</td>
                        <td>
                            <select name="status">
                                <option value="pending" selected>Pending</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </td>
                        <td>Oct 21, 2026</td>
                        <td class="action-links">
                            <a href="#">Edit</a>
                            <a href="#" class="delete">Delete</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
