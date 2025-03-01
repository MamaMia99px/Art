<?php
// Check if order ID is provided
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    header('Location: index.php?page=orders');
    exit;
}

// Check if user is logged in and the order belongs to them
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = "index.php?page=order_detail&order_id=$order_id";
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch order details
$order_query = "SELECT o.*, a.full_name, a.phone, a.address_line1, a.address_line2, a.city, a.province, a.postal_code, 
               p.reference_number, p.account_number 
               FROM orders o 
               JOIN user_addresses a ON o.shipping_address_id = a.id 
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
$history_query = "SELECT * FROM order_status_history WHERE order_id = $order_id ORDER BY created_at ASC";
$history_result = mysqli_query($conn, $history_query);
$status_history = [];

while ($history = mysqli_fetch_assoc($history_result)) {
    $status_history[] = $history;
}

// Handle cancel order request
if (isset($_POST['cancel_order']) && ($order['status'] === 'pending' || $order['status'] === 'processing')) {
    $cancel_reason = mysqli_real_escape_string($conn, $_POST['cancel_reason'] ?? 'Customer requested cancellation');
    
    // Update order status
    $update_query = "UPDATE orders SET status = 'cancelled' WHERE id = $order_id AND user_id = $user_id";
    mysqli_query($conn, $update_query);
    
    // Add to status history
    $current_date = date('Y-m-d H:i:s');
    $history_query = "INSERT INTO order_status_history (order_id, status, notes, created_at) VALUES ($order_id, 'cancelled', '$cancel_reason', '$current_date')";
    mysqli_query($conn, $history_query);
    
    // Redirect to refresh page
    header("Location: index.php?page=order_detail&order_id=$order_id&cancelled=1");
    exit;
}

// Calculate estimated delivery date
$delivery_date = clone $order_date;
$delivery_date->modify('+5 days');
$delivery_end_date = clone $order_date;
$delivery_end_date->modify('+7 days');
$delivery_range = $delivery_date->format('F j') . ' - ' . $delivery_end_date->format('F j, Y');
?>

<div class="container py-5">
    <?php if (isset($_GET['cancelled']) && $_GET['cancelled'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> Your order has been cancelled.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Order Details</h1>
        <a href="index.php?page=orders" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
    
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
                    
                    <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
                    <div class="mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                            <i class="fas fa-times me-2"></i>Cancel Order
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Status Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body">
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
</div>

<!-- Cancel Order Modal -->
<?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
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