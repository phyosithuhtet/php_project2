<?php
// Cart functions
function getCartCount($conn) {
    if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        return count($_SESSION['cart']);
    }
    return 0;
}

function getCartTotal($conn) {
    $total = 0;
    if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $product_id) {
            $sql = "SELECT price FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $total += $row['price'];
            }
            $stmt->close();
        }
    }
    return $total;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
