<?php
session_start();
include('connect.php'); // Database connection အတွက်

// Add to cart လုပ်တဲ့ အခါ
if(isset($_GET['add_to_cart']) && isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    
    // Database ကနေ ကုန်ပစ္စည်းအချက်အလက်များ ယူမယ်
    $sql = "SELECT * FROM items WHERE item_id='$item_id'";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    
    if($row) {
        // Cart session array ကို စတင်မယ်
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }
        
        // ကုန်ပစ္စည်းကို cart ထဲထည့်မယ်
        if(isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$item_id] = array(
                'item_name' => $row['item_name'],
                'item_price' => $row['item_price'],
                'item_image' => $row['item_image'],
                'quantity' => 1
            );
        }
        
        // Product page ကို ပြန်ညွှန်းမယ် (သို့မဟုတ်) alert ပေး
        echo "<script>alert('Product added to cart!'); window.location.href='product.php';</script>";
    }
}

// Remove from cart လုပ်တဲ့ အခါ
if(isset($_GET['remove_item'])) {
    $item_id = $_GET['remove_item'];
    if(isset($_SESSION['cart'][$item_id])) {
        unset($_SESSION['cart'][$item_id]);
        echo "<script>alert('Item removed from cart!'); window.location.href='cart.php';</script>";
    }
}

// Update quantity လုပ်တဲ့ အခါ
if(isset($_POST['update_cart'])) {
    foreach($_POST['quantity'] as $item_id => $quantity) {
        if(isset($_SESSION['cart'][$item_id]) && $quantity > 0) {
            $_SESSION['cart'][$item_id]['quantity'] = $quantity;
        } elseif(isset($_SESSION['cart'][$item_id]) && $quantity <= 0) {
            unset($_SESSION['cart'][$item_id]);
        }
    }
    echo "<script>alert('Cart updated!'); window.location.href='cart.php';</script>";
}

// Clear cart လုပ်တဲ့ အခါ
if(isset($_GET['clear_cart'])) {
    unset($_SESSION['cart']);
    echo "<script>alert('Cart cleared!'); window.location.href='cart.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Your Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Your existing CSS styles here */
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
        }
        
        th {
            background-color: #4CAF50;
            color: white;
        }
        
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .quantity-input {
            width: 60px;
            padding: 5px;
            text-align: center;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-danger {
            background-color: #f44336;
            color: white;
        }
        
        .btn-secondary {
            background-color: #008CBA;
            color: white;
        }
        
        .cart-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .total-section {
            text-align: right;
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
        }
        
        .continue-shopping {
            color: #4CAF50;
            text-decoration: none;
        }
        
        .continue-shopping:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
        
        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
        <form method="POST" action="cart.php">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $cart_total = 0;
                    foreach($_SESSION['cart'] as $item_id => $item): 
                        $item_total = $item['item_price'] * $item['quantity'];
                        $cart_total += $item_total;
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo $item['item_image']; ?>" class="product-img" alt="<?php echo $item['item_name']; ?>">
                            <?php echo $item['item_name']; ?>
                        </td>
                        <td>$<?php echo number_format($item['item_price'], 2); ?></td>
                        <td>
                            <input type="number" name="quantity[<?php echo $item_id; ?>]" 
                                   value="<?php echo $item['quantity']; ?>" 
                                   min="1" class="quantity-input">
                        </td>
                        <td>$<?php echo number_format($item_total, 2); ?></td>
                        <td>
                            <a href="cart.php?remove_item=<?php echo $item_id; ?>" 
                               class="btn btn-danger">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total-section">
                <h3>Cart Total: $<?php echo number_format($cart_total, 2); ?></h3>
            </div>
            
            <div class="cart-actions">
                <a href="product.php" class="continue-shopping">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
                
                <div>
                    <button type="submit" name="update_cart" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Update Cart
                    </button>
                    
                    <a href="cart.php?clear_cart=1" class="btn btn-danger" 
                       onclick="return confirm('Are you sure to clear all items?')">
                        <i class="fas fa-trash-alt"></i> Clear Cart
                    </a>
                    
                    <a href="checkout.php" class="btn btn-secondary">
                        <i class="fas fa-check-circle"></i> Proceed to Checkout
                    </a>
                </div>
            </div>
        </form>
        <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart fa-4x" style="color:#ccc;"></i>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <a href="product.php" class="btn btn-primary">Browse Products</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
