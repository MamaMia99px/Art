<?php
// Check if order ID is provided
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';

// If neither order ID nor order number is provided, show the tracking form
$show_form = ($order_id <= 0 && empty($order_number));

// Initialize variables
$order = null;
$order_items = [];
$status_history = [];
$error_message = '';

// Process form submission
if (isset($_POST['track_order'])) {
    $order_number = trim($_POST['order_number']);
    $email = trim($_POST['email']);
    
    if (empty($order_number)) {
        $error_message = 'Please enter an order number';
    } elseif (empty($email)) {
        $error_message = 'Please enter your email address';
    } else {
        // Sanitize inputs
        $order_number = mysqli_real_escape_string($conn, $order_number);
        $email = mysqli_real_escape_string($conn, $email);
        
        // Find the order
        $order_query = "SELECT o.* FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.order_number = '$order_number' AND u.email = '$email'";
        $order_result = mysqli_query($conn, $order_query);
        
        if (mysqli_num_rows($order_result) > 0) {
            $order = mysqli_fetch_assoc($order_result);
            $order_id = $order['id'];
            $show_form = false;
            
            // Redirect to the tracking page with the order ID
            header("Location: index.php?page=track_order&order_id=$order_id");
            exit;
        } else {
            $error_message = 'No order found with the provided details';
        }
    }
}

// If order ID is provided, fetch order details
if ($order_id > 0) {
    // Fetch order details
    $order_query = "SELECT o.*, a.full_name, a.phone, a.address_line1, a.address_line2, a.city, a.province, a.postal_code, 
                   u.email as user_email, u.full_name as user_name,
                   p.reference_number, p.account_number 
                   FROM orders o 
                   JOIN user_addresses a ON o.shipping_address_id = a.id 
                   JOIN users u ON o.user_id = u.id 
                   LEFT JOIN payments p ON o.id = p.order_id 
                   WHERE o.id = $order_id";
    $order_result = mysqli_query($conn, $order_query);
    
    if (mysqli_num_rows($order_result) > 0) {
        $order = mysqli_fetch_assoc($order_result);
        $show_form = false;
        
        // Fetch order items
        $items_query = "SELECT oi.*, p.title, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = $order_id";
        $items_result = mysqli_query($conn, $items_query);
        
        while ($item = mysqli_fetch_assoc($items_result)) {
            $order_items[] = $item;
        }
        
        // Get order status history
        $history_query = "SELECT * FROM order_status_history WHERE order_id = $order_id ORDER BY created_at ASC";
        $history_result = mysqli_query($conn, $history_query);
        
        while ($history = mysqli_fetch_assoc($history_result)) {
            $status_history[] = $history;
        }
    } else {
        $error_message = 'Order not found';
        $show_form = true;
    }
} elseif (!empty($order_number)) {
    // If order number is provided, fetch order details
    $order_number = mysqli_real_escape_string($conn, $order_number);
    $order_query = "SELECT o.id FROM orders o WHERE o.order_number = '$order_number'";
    $order_result = mysqli_query($conn, $order_query);
    
    if (mysqli_num_rows($order_result) > 0) {
        $order_data = mysqli_fetch_assoc($order_result);
        $order_id = $order_data['id'];
        
        // Redirect to the tracking page with the order ID
        header("Location: index.php?page=track_order&order_id=$order_id");
        exit;
    } else {
        $error_message = 'Order not found';
        $show_form = true;
    }
}

// Format order date if order exists
if ($order) {
    $order_date = new DateTime($order['order_date']);
    $formatted_date = $order_date->format('F j, Y');
    
    // Calculate estimated delivery date
    $delivery_date = clone $order_date;
    $delivery_date->modify('+5 days');
    $delivery_end_date = clone $order_date;
    $delivery_end_date->modify('+7 days');
    $delivery_range = $delivery_date->format('F j') . ' - ' . $delivery_end_date->format('F j, Y');
}
?>

<div class="container py-5">
    <h1 class="h2 mb-4">Track Your Order</h1>
    
    <?php if ($show_form): ?>
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-body p-4">
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <p class="text-muted mb-4">Enter your order number and email address to track your order status.</p>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="order_number" class="form-label">Order Number</label>
                            <input type="text" class="form-control" id="order_number" name="order_number" placeholder="e.g. ORD-20230615-1234" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter the email used for the order" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="track_order" class="btn btn-primary">Track Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($order): ?>
    <div class="row">
        <div class="col-lg-8">
            <!-- Order Information -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order #<?php echo $order['order_number']; ?></h5>
                        <span class="badge <?php echo getStatusBadgeClass($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="text-muted mb-2">Order Date</h6>
                            <p class="mb-0"><?php echo $formatted_date; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Estimated Delivery</h6>
                            <p class="mb-0"><?php echo $delivery_range; ?></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="text-muted mb-2">Shipping Address</h6>
                            <p class="mb-0">
                                <?php echo $order['full_name']; ?><br>
                                <?php echo $order['address_line1']; ?>
                                <?php if (!empty($order['address_line2'])): ?>, <?php echo $order['address_line2']; ?><?php endif; ?><br>
                                <?php echo $order['city']; ?>, <?php echo $order['province']; ?> <?php echo $order['postal_code']; ?><br>
                                Phone: <?php echo $order['phone']; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Payment Method</h6>
                            <?php if ($order['payment_method'] === 'gcash'): ?>
                            <p class="mb-0">
                                <strong>GCash</strong><br>
                                <?php if (!empty($order['account_number'])): ?>
                                Account: <?php echo $order['account_number']; ?><br>
                                <?php endif; ?>
                                <?php if (!empty($order['reference_number'])): ?>
                                Reference: <?php echo $order['reference_number']; ?><br>
                                <?php endif; ?>
                                Status: <span class="badge bg-success">Paid</span>
                            </p>
                            <?php else: ?>
                            <p class="mb-0">
                                <strong>Cash on Delivery</strong><br>
                                Amount to pay: ₱<?php echo number_format($order['total_amount'], 2); ?><br>
                                Status: <span class="badge bg-warning text-dark"><?php echo $order['payment_status'] === 'pending' ? 'Pay upon delivery' : 'Paid'; ?></span>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Tracking Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <!-- Visual Tracking Progress -->
                    <div class="mb-4">
                        <?php 
                        $status_steps = ['pending', 'processing', 'shipped', 'delivered'];
                        $current_status = $order['status'];
                        $current_step = array_search($current_status, $status_steps);
                        if ($current_step === false) $current_step = -1; // For cancelled or other statuses
                        ?>
                        
                        <div class="position-relative mb-4">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($current_step >= 0) ? (($current_step + 1) / count($status_steps) * 100) : 0; ?>%" aria-valuenow="<?php echo ($current_step >= 0) ? (($current_step + 1) / count($status_steps) * 100) : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between position-relative" style="margin-top: -12px;">
                                <?php foreach ($status_steps as $index => $step): ?>
                                <div class="text-center" style="width: 100px; margin-left: <?php echo $index === 0 ? '0' : 'auto'; ?>; margin-right: <?php echo $index === count($status_steps) - 1 ? '0' : 'auto'; ?>">
                                    <div class="<?php echo ($current_step >= $index) ? 'bg-primary' : 'bg-secondary'; ?> rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 24px; height: 24px;">
                                        <i class="fas fa-check text-white small"></i>
                                    </div>
                                    <div class="mt-2">
                                        <span class="small <?php echo ($current_step >= $index) ? 'fw-bold' : 'text-muted'; ?>"><?php echo ucfirst($step); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detailed Timeline -->
                    <div class="position-relative">
                        <?php if (!empty($status_history)): ?>
                            <?php foreach ($status_history as $index => $status): ?>
                                <?php 
                                    $status_date = new DateTime($status['created_at']);
                                    $status_formatted_date = $status_date->format('M j, Y - g:i A');
                                    $is_last = $index === count($status_history) - 1;
                                ?>
                                <div class="d-flex mb-3">
                                    <div class="me-3 position-relative">
                                        <div class="bg-primary rounded-circle" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                                        <?php if (!$is_last): ?>
                                        <div class="position-absolute bg-primary" style="width: 2px; height: calc(100% + 12px); left: 5px; top: 12px;"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <h6 class="mb-0 me-2"><?php echo ucfirst($status['status']); ?></h6>
                                            <small class="text-muted"><?php echo $status_formatted_date; ?></small>
                                        </div>
                                        <p class="text-muted mb-0"><?php echo $status['notes']; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="d-flex mb-3">
                                <div class="me-3">
                                    <div class="bg-primary rounded-circle" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0 me-2"><?php echo ucfirst($order['status']); ?></h6>
                                        <small class="text-muted"><?php echo $formatted_date; ?></small>
                                    </div>
                                    <p class="text-muted mb-0">Your order has been received.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Next expected status for COD orders -->
                        <?php if ($order['payment_method'] === 'cod' && $order['status'] === 'pending'): ?>
                        <div class="d-flex mb-3">
                            <div class="me-3 position-relative">
                                <div class="bg-secondary rounded-circle" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                            </div>
                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="mb-0 me-2 text-muted">Processing</h6>
                                    <small class="text-muted">Expected soon</small>
                                </div>
                                <p class="text-muted mb-0">Your order will be processed after payment is received.</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col" class="ps-4">Product</th>
                                    <th scope="col" class="text-center">Quantity</th>
                                    <th scope="col" class="text-end pe-4">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div style="width: 60px; height: 60px;" class="me-3 border rounded overflow-hidden">
                                                <img src="<?php echo $item['image']; ?>" class="img-fluid h-100 w-100 object-fit-cover" alt="<?php echo $item['title']; ?>">
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo $item['title']; ?></h6>
                                                <a href="index.php?page=product&id=<?php echo $item['product_id']; ?>" class="text-decoration-none small">View Product</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end pe-4 align-middle">₱<?php echo number_format($item['price'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>₱<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <span>₱<?php echo number_format($order['shipping_fee'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax</span>
                        <span>₱<?php echo number_format($order['tax'], 2); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Information -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <?php if ($order['status'] === 'shipped' || $order['status'] === 'delivered'): ?>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Tracking Number</h6>
                        <p class="mb-0 fw-medium">TRK<?php echo str_pad(rand(1000, 9999), 8, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Estimated Delivery</h6>
                        <p class="mb-0"><?php echo $delivery_range; ?></p>
                    </div>
                    
                    <div>
                        <h6 class="text-muted mb-2">Shipping Method</h6>
                        <p class="mb-0">Standard Shipping</p>
                    </div>
                </div>
            </div>
            
            <!-- Need Help Section -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">If you have any questions or concerns about your order, please contact our customer support.</p>
                    <div class="d-grid">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-headset me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>