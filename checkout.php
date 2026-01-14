<?php
session_start();
include('connect.php'); // Database connection အတွက်

// Check if cart is empty
if(!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    echo "<script>alert('Your cart is empty!'); window.location.href='product.php';</script>";
    exit();
}

// Calculate total
$order_total = 0;
if(isset($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $item) {
        $order_total += $item['item_price'] * $item['quantity'];
    }
}

// Process checkout form submission
if(isset($_POST['place_order'])) {
    $customer_name = mysqli_real_escape_string($con, $_POST['customer_name']);
    $customer_email = mysqli_real_escape_string($con, $_POST['customer_email']);
    $customer_phone = mysqli_real_escape_string($con, $_POST['customer_phone']);
    $customer_address = mysqli_real_escape_string($con, $_POST['customer_address']);
    $payment_method = mysqli_real_escape_string($con, $_POST['payment_method']);
    
    // Generate order number
    $order_number = 'ORD' . date('Ymd') . rand(1000, 9999);
    
    // Insert into orders table
    $insert_order = "INSERT INTO orders (order_number, customer_name, customer_email, 
                     customer_phone, customer_address, payment_method, order_total, order_date) 
                     VALUES ('$order_number', '$customer_name', '$customer_email', 
                     '$customer_phone', '$customer_address', '$payment_method', 
                     '$order_total', NOW())";
    
    if(mysqli_query($con, $insert_order)) {
        $order_id = mysqli_insert_id($con);
        
        // Insert order items
        foreach($_SESSION['cart'] as $item_id => $item) {
            $item_name = $item['item_name'];
            $item_price = $item['item_price'];
            $quantity = $item['quantity'];
            $item_total = $item_price * $quantity;
            
            $insert_item = "INSERT INTO order_items (order_id, item_id, item_name, 
                           item_price, quantity, item_total) 
                           VALUES ('$order_id', '$item_id', '$item_name', 
                           '$item_price', '$quantity', '$item_total')";
            mysqli_query($con, $insert_item);
        }
        
        // Clear cart and redirect to success page
        unset($_SESSION['cart']);
        
        echo "<script>
                alert('Order placed successfully! Order Number: $order_number');
                window.location.href='order.php?order_id=$order_id';
              </script>";
        exit();
    } else {
        echo "<script>alert('Error placing order. Please try again.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Your Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same CSS as cart.php for consistency */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 90%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        
        h2 {
            color: #444;
            margin-top: 30px;
        }
        
        .checkout-section {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        
        .checkout-left {
            flex: 3;
        }
        
        .checkout-right {
            flex: 2;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        .order-summary {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-total {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-secondary {
            background-color: #008CBA;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #45a049;
        }
        
        .payment-methods {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .payment-option {
            flex: 1;
            text-align: center;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .payment-option.selected {
            border-color: #4CAF50;
            background-color: #f0fff0;
        }
        
        .payment-option i {
            font-size: 24px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-check-circle"></i> Checkout</h1>
        
        <div class="checkout-section">
            <!-- Customer Information Form -->
            <div class="checkout-left">
                <h2>Customer Information</h2>
                <form method="POST" action="checkout.php">
                    <div class="form-group">
                        <label for="customer_name">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_email">Email Address *</label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_phone">Phone Number *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_address">Shipping Address *</label>
                        <textarea id="customer_address" name="customer_address" required></textarea>
                    </div>
                    
                    <h2>Payment Method</h2>
                    <div class="payment-methods">
                        <div class="payment-option selected" onclick="selectPayment('cod')">
                            <i class="fas fa-money-bill-wave"></i>
                            <div>Cash on Delivery</div>
                            <input type="radio" name="payment_method" value="Cash on Delivery" checked hidden>
                        </div>
                        
                        <div class="payment-option" onclick="selectPayment('card')">
                            <i class="fas fa-credit-card"></i>
                            <div>Credit Card</div>
                            <input type="radio" name="payment_method" value="Credit Card" hidden>
                        </div>
                        
                        <div class="payment-option" onclick="selectPayment('paypal')">
                            <i class="fab fa-paypal"></i>
                            <div>PayPal</div>
                            <input type="radio" name="payment_method" value="PayPal" hidden>
                        </div>
                    </div>
                    
                    <button type="submit" name="place_order" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Place Order
                    </button>
                </form>
                
                <a href="cart.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Cart
                </a>
            </div>
            
            <!-- Order Summary -->
            <div class="checkout-right">
                <h2>Order Summary</h2>
                <div class="order-summary">
                    <?php if(isset($_SESSION['cart'])): ?>
                        <?php foreach($_SESSION['cart'] as $item_id => $item): ?>
                        <div class="order-item">
                            <div>
                                <strong><?php echo $item['item_name']; ?></strong><br>
                                <small>Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['item_price'], 2); ?></small>
                            </div>
                            <div>$<?php echo number_format($item['item_price'] * $item['quantity'], 2); ?></div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="order-total">
                            <div>Total Amount:</div>
                            <div>$<?php echo number_format($order_total, 2); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function selectPayment(method) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Check the corresponding radio button
            event.currentTarget.querySelector('input[type="radio"]').checked = true;
        }
    </script>
</body>
</html>
