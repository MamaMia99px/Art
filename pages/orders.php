<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'index.php?page=orders';
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get order status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "SELECT * FROM orders WHERE user_id = $user_id";

if (!empty($status_filter)) {
    $status_filter = mysqli_real_escape_string($conn, $status_filter);
    $query .= " AND order_status = '$status_filter'";
}

$query .= " ORDER BY order_date DESC";

// Execute query
$result = mysqli_query($conn, $query);
$orders = [];

if (mysqli_num_rows($result) > 0) {
    while ($order = mysqli_fetch_assoc($result)) {
        $orders[] = $order;
    }
}

// Get all possible order statuses for filter
$statuses_query = "SELECT DISTINCT order_status FROM orders WHERE user_id = $user_id";
$statuses_result = mysqli_query($conn, $statuses_query);
$statuses = [];

if (mysqli_num_rows($statuses_result) > 0) {
    while ($status = mysqli_fetch_assoc($statuses_result)) {
        $statuses[] = $status['order_status'];
    }
} else {
    // Default statuses if none found
    $statuses = ['processing', 'shipped', 'delivered', 'cancelled'];
}
?>

<div class="container py-5">
    <h1 class="h2 mb-4">My Orders</h1>
    
    <!-- Status Filter -->
    <div class="mb-4">
        <div class="btn-group">
            <a href="index.php?page=orders" class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-outline-primary'; ?>">All Orders</a>
            <?php foreach ($statuses as $status): ?>
            <a href="index.php?page=orders&status=<?php echo $status; ?>" class="btn <?php echo $status_filter === $status ? 'btn-primary' : 'btn-outline-primary'; ?>">
                <?php echo ucfirst($status); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php if (empty($orders)): ?>
    <div class="text-center py-5 bg-white rounded shadow-sm">
        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
        <h2 class="h4 mb-3">No orders found</h2>
        <p class="text-muted mb-4">
            <?php echo empty($status_filter) ? 'You haven\'t placed any orders yet.' : 'You don\'t have any ' . $status_filter . ' orders.'; ?>
        </p>
        <a href="index.php" class="btn btn-primary">Start Shopping</a>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="ps-4">Order #</th>
                            <th scope="col">Date</th>
                            <th scope="col">Items</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <?php 
                            // Get order items count
                            $items_query = "SELECT COUNT(*) as count, SUM(quantity) as total_quantity FROM order_items WHERE order_id = {$order['id']}";
                            $items_result = mysqli_query($conn, $items_query);
                            $items_data = mysqli_fetch_assoc($items_result);
                            $item_count = $items_data['count'];
                            $total_quantity = $items_data['total_quantity'];
                            
                            // Format date
                            $order_date = new DateTime($order['order_date']);
                            $formatted_date = $order_date->format('M j, Y');
                        ?>
                        <tr>
                            <td class="ps-4">#<?php echo $order['id']; ?></td>
                            <td><?php echo $formatted_date; ?></td>
                            <td><?php echo $total_quantity; ?> item<?php echo $total_quantity !== 1 ? 's' : ''; ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <?php 
                                    $status_class = '';
                                    switch ($order['order_status']) {
                                        case 'processing':
                                            $status_class = 'bg-info text-dark';
                                            break;
                                        case 'shipped':
                                            $status_class = 'bg-primary';
                                            break;
                                        case 'delivered':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'bg-danger';
                                            break;
                                        default:
                                            $status_class = 'bg-secondary';
                                    }
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order['order_status']); ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="index.php?page=order_detail&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>