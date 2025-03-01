<?php
// Check if order ID is provided
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

// Check if user is logged in and the order belongs to them
if (!isset($_SESSION['user_id'])) {
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
    header('Location: index.php');
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
$order_date = new DateTime($order['created_at']);
$formatted_date = $order_date->format('F j, Y');

// Calculate estimated delivery date (5-7 days from order date)
$delivery_date = clone $order_date;
$delivery_date->modify('+5 days');
$delivery_end_date = clone $order_date;
$delivery_end_date->modify('+7 days');
$delivery_range = $delivery_date->format('F j') . ' - ' . $delivery_end_date->format('F j, Y');
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <div class="mb-4">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
        </div>
        <h1 class="h2 mb-2">Thank You for Your Order!</h1>
        <p class="text-muted">Your order has been placed successfully.</p>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Order Information -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order #<?php echo $order['order_number']; ?></h5>
                        <span class="badge bg-primary"><?php echo ucfirst($order['status']); ?></span>
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
                                GCash<br>
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
                                Cash on Delivery<br>
                                Status: <span class="badge bg-warning text-dark">Pay upon delivery</span>
                            </p>
                            <?php endif; ?>
                        </div>
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
            
            <!-- Order Summary -->
            <div class="card mb-4">
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
                        <span class="fw-bold">₱<?php echo number_format($order['total'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- What's Next Section -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">What's Next?</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="mb-3">
                                <i class="fas fa-clipboard-check fa-3x text-primary"></i>
                            </div>
                            <h6>Order Processing</h6>
                            <p class="text-muted small">We're preparing your order for shipment.</p>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="mb-3">
                                <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                            </div>
                            <h6>Shipping</h6>
                            <p class="text-muted small">Your order will be shipped within 1-2 business days.</p>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <i class="fas fa-box-open fa-3x text-primary"></i>
                            </div>
                            <h6>Delivery</h6>
                            <p class="text-muted small">Estimated delivery within 5-7 business days.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="index.php?page=orders" class="btn btn-primary me-2">View All Orders</a>
                <a href="index.php" class="btn btn-outline-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>