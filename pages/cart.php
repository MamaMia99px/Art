<?php
// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    // Remove item from cart
    if ($action === 'remove' && $item_id > 0) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $item_id) {
                unset($_SESSION['cart'][$key]);
                // Reindex array
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
        // Redirect to prevent refresh issues
        header('Location: index.php?page=cart');
        exit;
    }
    
    // Update quantity
    if ($action === 'update' && $item_id > 0 && isset($_GET['quantity'])) {
        $quantity = intval($_GET['quantity']);
        if ($quantity > 0) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $item_id) {
                    $_SESSION['cart'][$key]['quantity'] = $quantity;
                    break;
                }
            }
        }
        // Return JSON response for AJAX requests
        if (isset($_GET['ajax'])) {
            // Calculate new totals
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shipping = 250;
            $tax = round($subtotal * 0.12); // 12% tax
            $total = $subtotal + $shipping + $tax;
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'item_subtotal' => $item['price'] * $quantity,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total
            ]);
            exit;
        }
        // Redirect for non-AJAX requests
        header('Location: index.php?page=cart');
        exit;
    }
}

// Calculate totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 250;
$tax = round($subtotal * 0.12); // 12% tax
$total = $subtotal + $shipping + $tax;

// Handle coupon application
$coupon_message = '';
if (isset($_POST['apply_coupon']) && !empty($_POST['coupon_code'])) {
    $coupon_code = $_POST['coupon_code'];
    
    // Check if coupon exists in database
    $query = "SELECT * FROM coupons WHERE code = '$coupon_code' AND active = 1 AND expiry_date >= CURDATE()";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $coupon = mysqli_fetch_assoc($result);
        $discount = $coupon['discount_amount'];
        $total -= $discount;
        $coupon_message = '<div class="alert alert-success">Coupon applied successfully! You saved ₱' . number_format($discount, 2) . '</div>';
    } else {
        $coupon_message = '<div class="alert alert-danger">Invalid or expired coupon code.</div>';
    }
}

// Handle checkout
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    // Redirect to checkout page
    header('Location: index.php?page=checkout');
    exit;
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Shopping Cart</h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
        </a>
    </div>
    
    <?php if (!empty($_SESSION['cart'])): ?>
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-body">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item d-flex py-3 border-bottom">
                        <!-- Product Image -->
                        <div class="flex-shrink-0 me-3">
                            <div style="width: 100px; height: 100px;" class="border rounded overflow-hidden">
                                <img src="<?php echo $item['image']; ?>" class="img-fluid h-100 w-100 object-fit-cover" alt="<?php echo $item['title']; ?>">
                            </div>
                        </div>
                        
                        <!-- Product Details -->
                        <div class="flex-grow-1">
                            <h5 class="mb-1"><?php echo $item['title']; ?></h5>
                            <p class="text-muted small mb-2">by <?php echo $item['artist']; ?></p>
                            <p class="fw-medium mb-3">₱<?php echo number_format($item['price'], 2); ?></p>
                            
                            <div class="d-flex align-items-center">
                                <!-- Quantity Controls -->
                                <div class="input-group" style="width: 120px;">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" 
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity']; ?> - 1)" 
                                            <?php echo ($item['quantity'] <= 1) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="text" class="form-control form-control-sm text-center" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           id="quantity-<?php echo $item['id']; ?>" 
                                           onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" 
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity']; ?> + 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <!-- Subtotal -->
                                <div class="ms-auto">
                                    <span class="fw-bold" id="subtotal-<?php echo $item['id']; ?>">
                                        ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                    </span>
                                </div>
                                
                                <!-- Remove Button -->
                                <a href="index.php?page=cart&action=remove&id=<?php echo $item['id']; ?>" class="btn btn-link text-danger ms-3">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-medium" id="cart-subtotal">₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <span class="fw-medium">₱<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax</span>
                        <span class="fw-medium" id="cart-tax">₱<?php echo number_format($tax, 2); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5" id="cart-total">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <!-- Coupon Code -->
                    <form method="POST" action="" class="mb-3">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="coupon_code" placeholder="Coupon code">
                            <button type="submit" name="apply_coupon" class="btn btn-outline-secondary">Apply</button>
                        </div>
                        <?php echo $coupon_message; ?>
                    </form>
                    
                    <!-- Checkout Button -->
                    <a href="index.php?page=checkout" class="btn btn-primary w-100 py-2">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Empty Cart -->
    <div class="text-center py-5 bg-white rounded shadow-sm">
        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
        <h2 class="h4 mb-3">Your cart is empty</h2>
        <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left me-2"></i>Start Shopping
        </a>
    </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(itemId, quantity) {
    // Ensure quantity is at least 1
    quantity = Math.max(1, parseInt(quantity));
    
    // Update input field
    document.getElementById('quantity-' + itemId).value = quantity;
    
    // Send AJAX request to update cart
    fetch(`index.php?page=cart&action=update&id=${itemId}&quantity=${quantity}&ajax=1`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update item subtotal
                document.getElementById('subtotal-' + itemId).textContent = '₱' + formatNumber(data.item_subtotal);
                
                // Update cart totals
                document.getElementById('cart-subtotal').textContent = '₱' + formatNumber(data.subtotal);
                document.getElementById('cart-tax').textContent = '₱' + formatNumber(data.tax);
                document.getElementById('cart-total').textContent = '₱' + formatNumber(data.total);
            }
        })
        .catch(error => console.error('Error updating cart:', error));
}

function formatNumber(number) {
    return new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(number);
}
</script>