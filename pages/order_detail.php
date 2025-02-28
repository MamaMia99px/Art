<?php
// Check if order ID is provided
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header('Location: index.php?page=orders');
    exit;
}

// Check if user is logged in and the order belongs to them
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = "index.php?page=order_detail&id=$order_id";
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch order details
$order_query = "SELECT o.*, p.transaction_id, p.card_last_four 
               FROM orders o 
               LEFT JOIN payments p ON o.id = p.order_id 
               WHERE o.id = $order_id AND o.user_id = $user_id";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) === 0) {
    // Order not found or doesn't belong to user
    header('Location: index.php?page=orders');
    exit;
}

$order = mysqli_fetch_assoc($order_result);

// Fetch order items
$items_query = "SELECT oi.*, p.title, p.image 
               FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
$order_items = [];

while ($item = mysqli_fetch_assoc($items_result)) {
    $order_items[] = $item;
}

// Format order date
$order_date = new DateTime($order['order_date']);
$formatted_date = $order_date->format('F j, Y');

// Get order status history
$status_history_query = "SELECT * FROM order_status_history WHERE order_id = $order_id ORDER BY created_at ASC";
$status_history_result = mysqli_query($conn, $status_history_query);
$status_history = [];

if (mysqli_num_rows($status_history_result) > 0) {
    while ($status = mysqli_fetch_assoc($status_history_result)) {
        $status_history[] = $status;
    }
} else {
    // If no history found, create a default one based on current status
    $status_history = [
        [
            'status' => 'processing',
            'created_at' => $order['order_date'],
            'notes' => 'Order received and is being processed.'
        ]
    ];
    
    if ($order['order_status'] === 'shipped') {
        $shipped_date = clone $order_date;
        $shipped_date->modify('+1 day');
        $status_history[] = [
            'status' => 'shipped',
            'created_at' => $shipped_date->format('Y-m-d H:i:s'),
            'notes' => 'Order has been shipped.'
        ];
    } else if ($order['order_status'] === 'delivered') {
        $shipped_date = clone $order_date;
        $shipped_date->modify('+1 day');
        $status_history[] = [
            'status' => 'shipped',
            'created_at' => $shipped_date->format('Y-m-d H:i:s'),
            'notes' => 'Order has been shipped.'
        ];
        
        $delivered_date = clone $order_date;
        $delivered_date->modify('+5 days');
        $status_history[] = [
            'status' => 'delivered',
            'created_at' => $delivered_date->format('Y-m-d H:i:s'),
            'notes' => 'Order has been delivered.'
        ];
    } else if ($order['order_status'] === 'cancelled') {
        $cancelled_date = clone $order_date;
        $cancelled_date->modify('+1 day');
        $status_history[] = [
            'status' => 'cancelled',
            'created_at' => $cancelled_date->format('Y-m-d H:i:s'),
            'notes' => 'Order has been cancelled.'
        ];
    }
}

// Handle cancel order request
if (isset($_POST['cancel_order']) && $order['order_status'] === 'processing') {
    $cancel_reason = mysqli_real_escape_string($conn, $_POST['cancel_reason'] ?? 'Customer requested cancellation');
    
    // Update order status
    $update_query = "UPDATE orders SET order_status = 'cancelled' WHERE id = $order_id AND user_id = $user_id";
    mysqli_query($conn, $update_query);
    
    // Add to status history
    $current_date = date('Y-m-d H:i:s');
    $history_query = "INSERT INTO order_status_history (order_id, status, notes, created_at) VALUES ($order_id, 'cancelled', '$cancel_reason', '$current_date')";
    mysqli_query($conn, $history_query);
    
    // Redirect to refresh page
    header("Location: index.php?page=order_detail&id=$order_id&cancelled=1");
    exit;
}
?>

<div class="container py-5">
    <?php if (isset($_GET['cancelled']) && $_GET['cancelled'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> Your order has been cancelled.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Order #<?php echo $order_id; ?></h1>
        <a href="index.php?page=orders" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Order Status -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order Status</h5>
                        <span class="badge bg-primary"><?php echo ucfirst($order['order_status']); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="position-relative pb-4">
                        <?php foreach ($status_history as $index => $status): ?>
                        <?php 
                            $status_date = new DateTime($status['created_at']);
                            $status_formatted_date = $status_date->format('M j, Y - g:i A');
                            
                            $is_last = $index === count($status_history) - 1;
                        ?>
                        <div class="d-flex">
                            <div class="me-3 position-relative">
                                <div class="bg-primary rounded-circle" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                                <?php if (!$is_last): ?>
                                <div class="position-absolute bg-primary" style="width: 2px; height: calc(100% - 12px); left: 5px; top: 18px;"></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="mb-0 me-2"><?php echo ucfirst($status['status']); ?></h6>
                                    <small class="text-muted"><?php echo $status_formatted_date; ?></small>
                                </div>
                                <p class="text-muted mb-0"><?php echo $status['notes']; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($order['order_status'] === 'processing'): ?>
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                            <i class="fas fa-times me-2"></i>Cancel Order
                        </button>
                    </div>
                    <?php endif; ?>
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
                                    <th scope="col" class="text-center">Price</th>
                                    <th scope="col" class="text-end pe-4">Subtotal</th>
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
                                    <td class="text-center align-middle">₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-end pe-4 align-middle">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Information -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Order Date</h6>
                        <p class="mb-0"><?php echo $formatted_date; ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Shipping Address</h6>
                        <p class="mb-0">
                            <?php echo $order['shipping_name']; ?><br>
                            <?php echo $order['shipping_address']; ?><br>
                            <?php echo $order['shipping_city']; ?>, <?php echo $order['shipping_province']; ?> <?php echo $order['shipping_postal_code']; ?><br>
                            Phone: <?php echo $order['shipping_phone']; ?><br>
                            Email: <?php echo $order['shipping_email']; ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Payment Method</h6>
                        <?php if ($order['payment_method'] === 'credit_card'): ?>
                        <p class="mb-0">
                            Credit Card (ending in <?php echo $order['card_last_four']; ?>)<br>
                            Transaction ID: <?php echo $order['transaction_id']; ?><br>
                            Status: <span class="badge bg-success">Paid</span>
                        </p>
                        <?php else: ?>
                        <p class="mb-0">
                            Cash on Delivery<br>
                            Status: <span class="badge bg-warning text-dark">Pending</span>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="card">
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
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<?php if ($order['order_status'] === 'processing'): ?>
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Reason for Cancellation</label>
                        <select class="form-select" id="cancel_reason" name="cancel_reason" required>
                            <option value="">Select a reason</option>
                            <option value="Changed my mind">Changed my mind</option>
                            <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                            <option value="Ordered by mistake">Ordered by mistake</option>
                            <option value="Shipping takes too long">Shipping takes too long</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="cancel_order" class="btn btn-danger">Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>