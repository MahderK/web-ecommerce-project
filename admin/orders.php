<?php
include_once '../user-side/includes/auth.php';
require_admin();
include_once '../user-side/includes/db.php';

$message = '';
$error = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['status']);
    
    if (in_array($new_status, ['Pending', 'Processing', 'Completed'])) {
        $stmt = mysqli_prepare($conn, "UPDATE orders SET order_status = ? WHERE order_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Order status updated successfully!";
        } else {
            $error = "Failed to update order status.";
        }
    } else {
        $error = "Invalid order status value.";
    }
}

// Handle Order Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $order_id = intval($_POST['order_id']);
    
    mysqli_begin_transaction($conn);
    try {
        // Delete payment details
        $stmt_pay = mysqli_prepare($conn, "DELETE FROM payments WHERE order_id = ?");
        mysqli_stmt_bind_param($stmt_pay, "i", $order_id);
        mysqli_stmt_execute($stmt_pay);
        
        // Delete order items
        $stmt_items = mysqli_prepare($conn, "DELETE FROM order_items WHERE order_id = ?");
        mysqli_stmt_bind_param($stmt_items, "i", $order_id);
        mysqli_stmt_execute($stmt_items);
        
        // Delete order
        $stmt_ord = mysqli_prepare($conn, "DELETE FROM orders WHERE order_id = ?");
        mysqli_stmt_bind_param($stmt_ord, "i", $order_id);
        mysqli_stmt_execute($stmt_ord);
        
        mysqli_commit($conn);
        $message = "Order deleted successfully!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Failed to delete order: " . $e->getMessage();
    }
}

// Fetch all orders
$ordersQuery = mysqli_query($conn, "
    SELECT o.order_id, u.username, o.total_amount, o.order_status, o.created_at, 
           COALESCE(SUM(oi.quantity), 0) as items_count
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id 
    LEFT JOIN order_items oi ON o.order_id = oi.order_id 
    GROUP BY o.order_id 
    ORDER BY o.created_at DESC
");
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
                <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin User') ?></span>
                <i class="fa-solid fa-circle-user fa-2x" style="color: #0b4d2c;"></i>
            </div>
        </div>

        <div class="table-container" style="margin-top: 30px;">
            <div class="table-header">
                <h3>All Orders</h3>
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
                    <?php if (mysqli_num_rows($ordersQuery) === 0): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #888; padding: 20px;">No orders recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php while ($ord = mysqli_fetch_assoc($ordersQuery)): ?>
                            <tr>
                                <td>#ORD-<?= str_pad($ord['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($ord['username'] ?? 'Guest Customer') ?></td>
                                <td><?= $ord['items_count'] ?> Item<?= $ord['items_count'] !== 1 ? 's' : '' ?></td>
                                <td>$<?= number_format($ord['total_amount'], 2) ?></td>
                                <td>
                                    <form method="POST" action="orders.php" style="display: inline-block;">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="order_id" value="<?= $ord['order_id'] ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #ddd; outline: none; background: #fff; font-size: 14px; cursor: pointer;">
                                            <option value="Pending" <?= $ord['order_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Processing" <?= $ord['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="Completed" <?= $ord['order_status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?= date('M d, Y', strtotime($ord['created_at'])) ?></td>
                                <td class="action-links">
                                    <form method="POST" action="orders.php" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                        <input type="hidden" name="delete_order" value="1">
                                        <input type="hidden" name="order_id" value="<?= $ord['order_id'] ?>">
                                        <button type="submit" class="delete" style="background: none; border: none; color: #e63946; font-weight: 600; cursor: pointer; font-size: 14px;">Delete</button>
                                    </form>
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
