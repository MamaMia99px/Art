<?php
/**
 * Buy Now Functions
 * 
 * Functions for handling the Buy Now feature
 */

/**
 * Process Buy Now order
 * 
 * @param int $product_id Product ID
 * @param int $quantity Quantity
 * @return int|bool Order ID on success, false on failure
 */
function processBuyNow($product_id, $quantity = 1) {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get product details
    $product_query = "SELECT p.*, a.name as artist_name 
                     FROM products p 
                     JOIN artists a ON p.artist_id = a.id 
                     WHERE p.id = $product_id";
    $product_result = mysqli_query($conn, $product_query);
    
    if (mysqli_num_rows($product_result) === 0) {
        return false;
    }
    
    $product = mysqli_fetch_assoc($product_result);
    
    // Clear current cart and add only this item
    unset($_SESSION['cart']);
    $_SESSION['cart'] = [];
    
    $_SESSION['cart'][] = [
        'id' => $product_id,
        'title' => $product['title'],
        'price' => $product['price'],
        'image' => $product['image'],
        'artist' => $product['artist_name'],
        'quantity' => $quantity
    ];
    
    // Set buy now flag
    $_SESSION['buy_now'] = true;
    
    return true;
}

/**
 * Create order from Buy Now or Cart
 * 
 * @param int $user_id User ID
 * @param int $shipping_address_id Shipping address ID
 * @param string $payment_method Payment method (cod, gcash)
 * @param array $payment_details Payment details for non-COD methods
 * @return int|bool Order ID on success, false on failure
 */
function createOrder($user_id, $shipping_address_id, $payment_method, $payment_details = []) {
    global $conn;
    
    // Check if cart is empty
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return false;
    }
    
    // Calculate order totals
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $shipping = 250; // Fixed shipping fee
    $tax = round($subtotal * 0.12); // 12% tax
    $total = $subtotal + $shipping + $tax;
    
    // Generate order number
    $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Set initial order status based on payment method
    $order_status = ($payment_method === 'cod') ? 'pending' : 'processing';
    $payment_status = ($payment_method === 'cod') ? 'pending' : 'completed';
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert order
        $order_query = "INSERT INTO orders (user_id, order_number, order_date, shipping_address_id, payment_method, 
                        subtotal, shipping_fee, tax, total_amount, status, payment_status) 
                        VALUES ($user_id, '$order_number', NOW(), $shipping_address_id, '$payment_method', 
                        $subtotal, $shipping, $tax, $total, '$order_status', '$payment_status')";
        
        if (!mysqli_query($conn, $order_query)) {
            throw new Exception("Failed to create order: " . mysqli_error($conn));
        }
        
        $order_id = mysqli_insert_id($conn);
        
        // Insert order items
        foreach ($_SESSION['cart'] as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $item_total = $price * $quantity;
            
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                            VALUES ($order_id, $product_id, $quantity, $price, $item_total)";
            
            if (!mysqli_query($conn, $item_query)) {
                throw new Exception("Failed to add order item: " . mysqli_error($conn));
            }
            
            // Update product inventory (if applicable)
            $update_inventory = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id AND stock >= $quantity";
            mysqli_query($conn, $update_inventory);
        }
        
        // Add initial status to history
        $status_note = ($payment_method === 'cod') ? 'Order received, awaiting payment on delivery.' : 'Order received and payment completed.';
        $history_query = "INSERT INTO order_status_history (order_id, status, notes, created_at) 
                        VALUES ($order_id, '$order_status', '$status_note', NOW())";
        
        if (!mysqli_query($conn, $history_query)) {
            throw new Exception("Failed to add status history: " . mysqli_error($conn));
        }
        
        // If GCash payment, save payment details
        if ($payment_method === 'gcash' && !empty($payment_details)) {
            $reference = mysqli_real_escape_string($conn, $payment_details['reference'] ?? '');
            $account = mysqli_real_escape_string($conn, $payment_details['account'] ?? '');
            
            $payment_query = "INSERT INTO payments (order_id, payment_method, reference_number, account_number, amount, status, created_at) 
                            VALUES ($order_id, 'gcash', '$reference', '$account', $total, 'completed', NOW())";
            
            if (!mysqli_query($conn, $payment_query)) {
                throw new Exception("Failed to save payment details: " . mysqli_error($conn));
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Clear cart and buy now flag
        unset($_SESSION['cart']);
        unset($_SESSION['buy_now']);
        
        return $order_id;
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Get user's default shipping address
 * 
 * @param int $user_id User ID
 * @return array|bool Address data on success, false if no address found
 */
function getUserDefaultAddress($user_id) {
    global $conn;
    
    $query = "SELECT * FROM user_addresses WHERE user_id = $user_id AND is_default = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    // If no default address, get the most recent one
    $query = "SELECT * FROM user_addresses WHERE user_id = $user_id ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

/**
 * Update order status
 * 
 * @param int $order_id Order ID
 * @param string $status New status
 * @param string $notes Notes about the status change
 * @return bool True on success, false on failure
 */
function updateOrderStatus($order_id, $status, $notes = '') {
    global $conn;
    
    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        return false;
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update order status
        $update_query = "UPDATE orders SET status = '$status' WHERE id = $order_id";
        
        if (!mysqli_query($conn, $update_query)) {
            throw new Exception("Failed to update order status: " . mysqli_error($conn));
        }
        
        // Add to status history
        $notes = mysqli_real_escape_string($conn, $notes);
        $history_query = "INSERT INTO order_status_history (order_id, status, notes, created_at) 
                        VALUES ($order_id, '$status', '$notes', NOW())";
        
        if (!mysqli_query($conn, $history_query)) {
            throw new Exception("Failed to add status history: " . mysqli_error($conn));
        }
        
        // If status is cancelled, update inventory (add products back to stock)
        if ($status === 'cancelled') {
            $items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id";
            $items_result = mysqli_query($conn, $items_query);
            
            while ($item = mysqli_fetch_assoc($items_result)) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                
                $update_inventory = "UPDATE products SET stock = stock + $quantity WHERE id = $product_id";
                mysqli_query($conn, $update_inventory);
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        error_log($e->getMessage());
        return false;
    }
}
