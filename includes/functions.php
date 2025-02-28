<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect user
function redirect($url) {
    header("Location: $url");
    exit;
}

// Function to display error message
function displayError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Function to display success message
function displaySuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Function to get user details
function getUserDetails($user_id) {
    global $conn;
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Function to get product details
function getProductDetails($product_id) {
    global $conn;
    $query = "SELECT p.*, c.name as category_name, a.name as artist_name, a.location as artist_location 
              FROM products p 
              JOIN categories c ON p.category_id = c.id 
              JOIN artists a ON p.artist_id = a.id 
              WHERE p.id = $product_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Function to get product images
function getProductImages($product_id) {
    global $conn;
    $query = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_primary DESC";
    $result = mysqli_query($conn, $query);
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row;
    }
    return $images;
}

// Function to format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Function to get order status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'processing':
            return 'bg-info text-dark';
        case 'shipped':
            return 'bg-primary';
        case 'delivered':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Function to add item to cart
function addToCart($product_id, $quantity = 1) {
    global $conn;
    
    // Get product details
    $product = getProductDetails($product_id);
    
    if (!$product) {
        return false;
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if product already in cart
    $found = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $product_id) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    
    // If product not in cart, add it
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $product_id,
            'title' => $product['title'],
            'price' => $product['price'],
            'image' => $product['image'],
            'artist' => $product['artist_name'],
            'quantity' => $quantity
        ];
    }
    
    return true;
}

// Function to update cart item quantity
function updateCartQuantity($product_id, $quantity) {
    if ($quantity < 1) {
        return false;
    }
    
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $product_id) {
            $_SESSION['cart'][$key]['quantity'] = $quantity;
            return true;
        }
    }
    
    return false;
}

// Function to remove item from cart
function removeFromCart($product_id) {
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $product_id) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
            return true;
        }
    }
    
    return false;
}

// Function to calculate cart totals
function calculateCartTotals() {
    $subtotal = 0;
    $items_count = 0;
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $items_count += $item['quantity'];
        }
    }
    
    $shipping = 250; // Fixed shipping fee
    $tax = round($subtotal * 0.12); // 12% tax
    $total = $subtotal + $shipping + $tax;
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'items_count' => $items_count
    ];
}

// Function to clear cart
function clearCart() {
    unset($_SESSION['cart']);
}

// Function to create order
function createOrder($user_id, $shipping_info, $payment_method) {
    global $conn;
    
    // Calculate totals
    $totals = calculateCartTotals();
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert order
        $shipping_name = sanitize($shipping_info['name']);
        $shipping_address = sanitize($shipping_info['address']);
        $shipping_city = sanitize($shipping_info['city']);
        $shipping_province = sanitize($shipping_info['province']);
        $shipping_postal_code = sanitize($shipping_info['postal_code']);
        $shipping_phone = sanitize($shipping_info['phone']);
        $shipping_email = sanitize($shipping_info['email']);
        
        $payment_status = ($payment_method === 'cod') ? 'pending' : 'paid';
        $order_status = 'processing';
        
        $query = "INSERT INTO orders (user_id, order_date, subtotal, shipping_fee, tax, total_amount, 
                  shipping_name, shipping_address, shipping_city, shipping_province, shipping_postal_code, 
                  shipping_phone, shipping_email, payment_method, payment_status, order_status) 
                  VALUES ($user_id, NOW(), {$totals['subtotal']}, {$totals['shipping']}, {$totals['tax']}, 
                  {$totals['total']}, '$shipping_name', '$shipping_address', '$shipping_city', 
                  '$shipping_province', '$shipping_postal_code', '$shipping_phone', '$shipping_email', 
                  '$payment_method', '$payment_status', '$order_status')";
        
        mysqli_query($conn, $query);
        $order_id = mysqli_insert_id($conn);
        
        // Insert order items
        foreach ($_SESSION['cart'] as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $subtotal = $price * $quantity;
            
            $query = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                      VALUES ($order_id, $product_id, $quantity, $price, $subtotal)";
            mysqli_query($conn, $query);
            
            // Update product stock
            $query = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id AND stock >= $quantity";
            mysqli_query($conn, $query);
        }
        
        // Add initial status to history
        $query = "INSERT INTO order_status_history (order_id, status, notes, created_at) 
                  VALUES ($order_id, 'processing', 'Order received and is being processed.', NOW())";
        mysqli_query($conn, $query);
        
        // If payment method is credit card, add payment record
        if ($payment_method === 'credit_card' && isset($shipping_info['card_last_four'])) {
            $last_four = sanitize($shipping_info['card_last_four']);
            $transaction_id = 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 10));
            
            $query = "INSERT INTO payments (order_id, payment_method, amount, status, transaction_id, card_last_four, created_at) 
                      VALUES ($order_id, 'credit_card', {$totals['total']}, 'completed', '$transaction_id', '$last_four', NOW())";
            mysqli_query($conn, $query);
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Clear cart
        clearCart();
        
        return $order_id;
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        return false;
    }
}
?>