<?php
session_start();
require_once 'components/connect.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id === 0) {
    header("Location: orders.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order = null;
$order_items = [];

// Fetch the order, verifying it belongs to the current user
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Order not found or doesn't belong to user
    header("Location: orders.php");
    exit();
}

$order = $result->fetch_assoc();

// Fetch items for this order
$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

if ($items_result->num_rows > 0) {
    while ($item = $items_result->fetch_assoc()) {
        $order_items[] = $item;
    }
}
$items_stmt->close();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order['id']; ?> - Online Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'components/header.php'; ?>

    <div class="container">
        <h1>Order Details</h1>

        <div class="order-summary">
            <div class="order-summary-header">
                <h2>Order #<?php echo htmlspecialchars($order['id']); ?></h2>
                <div class="order-status status-<?php echo htmlspecialchars($order['order_status']); ?>">
                    <?php echo ucfirst(htmlspecialchars($order['order_status'])); ?>
                </div>
            </div>

            <div class="order-summary-details">
                <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($order['payment_method']))); ?></p>
                <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
        </div>

        <h3>Order Items</h3>
        <div class="cart-table">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>1</td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="order-actions">
            <a href="orders.php" class="btn btn-secondary">&larr; Back to My Orders</a>
            <a href="view_products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html>
