<?php
// Handle cart actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        
        if ($product_id > 0) {
            // Get product details
            $product_query = "SELECT * FROM products WHERE id = $product_id";
            $product_result = mysqli_query($conn, $product_query);
            
            if (mysqli_num_rows($product_result) > 0) {
                $product = mysqli_fetch_assoc($product_result);
                
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
                    // Get artist name
                    $artist_query = "SELECT name FROM artists WHERE id = {$product['artist_id']}";
                    $artist_result = mysqli_query($conn, $artist_query);
                    $artist = mysqli_fetch_assoc($artist_result);
                    
                    $_SESSION['cart'][] = [
                        'id' => $product_id,
                        'title' => $product['title'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'artist' => $artist['name'],
                        'quantity' => $quantity
                    ];
                }
                
                // Redirect back to product page with success message
                header("Location: index.php?page=product&id=$product_id&added=1");
                exit;
            }
        }
    } elseif ($action === 'update') {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        
        if ($product_id > 0 && $quantity > 0) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $product_id) {
                    $_SESSION['cart'][$key]['quantity'] = $quantity;
                    break;
                }
            }
        }
        
        // Redirect back to cart
        header("Location: index.php?page=cart");
        exit;
    } elseif ($action === 'remove') {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if ($product_id > 0) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $product_id) {
                    unset($_SESSION['cart'][$key]);
                    // Re-index array
                    $_SESSION['cart'] = array_values($_SESSION['cart']);
                    break;
                }
            }
        }
        
        // Redirect back to cart
        header("Location: index.php?page=cart");
        exit;
    } elseif ($action === 'clear') {
        // Clear entire cart
        unset($_SESSION['cart']);
        
        // Redirect back to cart
        header("Location: index.php?page=cart");
        exit;
    }
}

// Calculate cart totals
$subtotal = 0;
$items_count = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $items_count += $item['quantity'];
    }
}

$shipping = 250; // Fixed shipping fee
$tax = round($subtotal * 0.12); // 12% tax
$total = $subtotal + $shipping + $tax;
?>

<div class="container py-5">
    <h1 class="h2 mb-4">Shopping Cart</h1>
    
    <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
    <div class="text-center py-5 bg-white rounded shadow-sm">
        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
        <h2 class="h4 mb-3">Your cart is empty</h2>
        <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
        <a href="index.php" class="btn btn-primary">Start Shopping</a>
    </div>
    <?php else: ?>
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="row mb-4 pb-4 border-bottom">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="bg-image rounded">
                                <img src="<?php echo $item['image']; ?>" class="img-fluid rounded" alt="<?php echo $item['title']; ?>" style="height: 120px; object-fit: cover;">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1"><?php echo $item['title']; ?></h5>
                                    <p class="text-muted mb-2 small">by <?php echo $item['artist']; ?></p>
                                    <p class="mb-3 fw-bold">₱<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to remove this item?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <form method="POST" action="" class="d-flex align-items-center">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary quantity-decrease" onclick="decreaseQuantity('quantity-<?php echo $item['id']; ?>')">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" name="quantity" id="quantity-<?php echo $item['id']; ?>" class="form-control form-control-sm mx-2" style="width: 60px;" value="<?php echo $item['quantity']; ?>" min="1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary quantity-increase" onclick="increaseQuantity('quantity-<?php echo $item['id']; ?>')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-outline-primary ms-3">Update</button>
                                </form>
                                
                                <div class="ms-auto">
                                    <span class="fw-bold">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to clear your cart?')">
                                <i class="fas fa-trash me-2"></i>Clear Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <span>₱<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax (12%)</span>
                        <span>₱<?php echo number_format($tax, 2); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <!-- Checkout Button -->
                    <a href="index.php?page=checkout" class="btn btn-primary w-100 py-2">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function decreaseQuantity(inputId) {
    const input = document.getElementById(inputId);
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

function increaseQuantity(inputId) {
    const input = document.getElementById(inputId);
    const currentValue = parseInt(input.value);
    input.value = currentValue + 1;
}
</script>