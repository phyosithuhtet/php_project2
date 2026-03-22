<?php
session_start();
require_once 'components/connect.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];

// Fetch orders for the current user
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Online Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'components/header.php'; ?>

    <div class="container">
        <h1>My Orders</h1>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                You haven't placed any orders yet. 
                <a href="view_products.php" class="alert-link">Start shopping now!</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <strong class="order-number">Order #<?php echo $order['id']; ?></strong>
                                <span class="order-date">
                                    <i class="date-icon"></i> 
                                    <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?>
                                </span>
                            </div>
                            <div class="order-status status-<?php echo $order['order_status']; ?>">
                                <?php 
                                    $status_text = '';
                                    switch($order['order_status']) {
                                        case 'pending': $status_text = 'Pending'; break;
                                        case 'processing': $status_text = 'Processing'; break;
                                        case 'shipped': $status_text = 'Shipped'; break;
                                        case 'delivered': $status_text = 'Delivered'; break;
                                        case 'cancelled': $status_text = 'Cancelled'; break;
                                        default: $status_text = ucfirst($order['order_status']);
                                    }
                                    echo $status_text;
                                ?>
                            </div>
                        </div>
                        <div class="order-body">
                            <div class="order-summary">
                                <div class="summary-item">
                                    <span class="summary-label">Total Amount:</span>
                                    <span class="summary-value"><?php echo number_format($order['total_amount']); ?> Ks</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Payment Method:</span>
                                    <span class="summary-value">
                                        <?php 
                                            $payment_text = '';
                                            switch($order['payment_method']) {
                                                case 'cod': $payment_text = 'Cash on Delivery'; break;
                                                case 'bank_transfer': $payment_text = 'Bank Transfer'; break;
                                                case 'wave_money': $payment_text = 'Wave Money'; break;
                                                default: $payment_text = ucfirst(str_replace('_', ' ', $order['payment_method']));
                                            }
                                            echo $payment_text;
                                        ?>
                                    </span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Shipping Address:</span>
                                    <span class="summary-value"><?php echo htmlspecialchars(substr($order['shipping_address'], 0, 60)); ?>...</span>
                                </div>
                            </div>
                            <div class="order-actions">
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html>
