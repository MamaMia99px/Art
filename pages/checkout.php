<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store current page as redirect destination after login
    $_SESSION['redirect_after_login'] = 'index.php?page=checkout';
    header('Location: index.php?page=login');
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php?page=cart');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get user addresses
$addresses_query = "SELECT * FROM user_addresses WHERE user_id = $user_id ORDER BY is_default DESC";
$addresses_result = mysqli_query($conn, $addresses_query);
$addresses = [];

if (mysqli_num_rows($addresses_result) > 0) {
    while ($address = mysqli_fetch_assoc($addresses_result)) {
        $addresses[] = $address;
    }
}

// Calculate order totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 250;
$tax = round($subtotal * 0.12); // 12% tax
$total = $subtotal + $shipping + $tax;

// Handle form submission
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate shipping address
    $shipping_address_id = isset($_POST['shipping_address']) ? intval($_POST['shipping_address']) : 0;
    $new_address = isset($_POST['new_address']) && $_POST['new_address'] == '1';
    
    if ($new_address) {
        // Validate new address fields
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $address_line1 = trim($_POST['address_line1']);
        $address_line2 = trim($_POST['address_line2'] ?? '');
        $city = trim($_POST['city']);
        $province = trim($_POST['province']);
        $postal_code = trim($_POST['postal_code']);
        
        if (empty($full_name)) {
            $errors[] = 'Full name is required';
        }
        if (empty($phone)) {
            $errors[] = 'Phone number is required';
        }
        if (empty($address_line1)) {
            $errors[] = 'Address is required';
        }
        if (empty($city)) {
            $errors[] = 'City is required';
        }
        if (empty($province)) {
            $errors[] = 'Province is required';
        }
        if (empty($postal_code)) {
            $errors[] = 'Postal code is required';
        }
        
        // If no errors, save new address
        if (empty($errors)) {
            $is_default = isset($_POST['save_as_default']) && $_POST['save_as_default'] == '1' ? 1 : 0;
            
            // If setting as default, unset current default
            if ($is_default) {
                $update_query = "UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id";
                mysqli_query($conn, $update_query);
            }
            
            // Insert new address
            $full_name = mysqli_real_escape_string($conn, $full_name);
            $phone = mysqli_real_escape_string($conn, $phone);
            $address_line1 = mysqli_real_escape_string($conn, $address_line1);
            $address_line2 = mysqli_real_escape_string($conn, $address_line2);
            $city = mysqli_real_escape_string($conn, $city);
            $province = mysqli_real_escape_string($conn, $province);
            $postal_code = mysqli_real_escape_string($conn, $postal_code);
            
            $insert_query = "INSERT INTO user_addresses (user_id, full_name, phone, address_line1, address_line2, city, province, postal_code, is_default) 
                            VALUES ($user_id, '$full_name', '$phone', '$address_line1', '$address_line2', '$city', '$province', '$postal_code', $is_default)";
            
            if (mysqli_query($conn, $insert_query)) {
                $shipping_address_id = mysqli_insert_id($conn);
            } else {
                $errors[] = 'Failed to save address. Please try again.';
            }
        }
    } elseif ($shipping_address_id <= 0) {
        $errors[] = 'Please select a shipping address';
    }
    
    // Validate payment method
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    if (empty($payment_method)) {
        $errors[] = 'Please select a payment method';
    }
    
    // For GCash payment, validate reference number if provided
    if ($payment_method === 'gcash') {
        $gcash_reference = trim($_POST['gcash_reference'] ?? '');
        $gcash_number = trim($_POST['gcash_number'] ?? '');
        
        if (empty($gcash_number)) {
            $errors[] = 'GCash number is required';
        }
        
        if (empty($gcash_reference)) {
            $errors[] = 'GCash reference number is required';
        }
    }
    
    // If no errors, create order
    if (empty($errors)) {
        // Generate order number
        $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Get address details
        $address_query = "SELECT * FROM user_addresses WHERE id = $shipping_address_id AND user_id = $user_id";
        $address_result = mysqli_query($conn, $address_query);
        $address = mysqli_fetch_assoc($address_result);
        
        if (!$address) {
            $errors[] = 'Invalid shipping address';
        } else {
            // Start transaction
            mysqli_begin_transaction($conn);
            
            try {
                // Insert order into database
                $order_status = ($payment_method === 'cod') ? 'pending' : 'processing';
                $payment_status = ($payment_method === 'cod') ? 'pending' : 'completed';
                
                $order_query = "INSERT INTO orders (user_id, order_number, order_date, shipping_address_id, payment_method, 
                                subtotal, shipping_fee, tax, total_amount, status, payment_status) 
                                VALUES ($user_id, '$order_number', NOW(), $shipping_address_id, '$payment_method', 
                                $subtotal, $shipping, $tax, $total, '$order_status', '$payment_status')";
                
                if (mysqli_query($conn, $order_query)) {
                    $order_id = mysqli_insert_id($conn);
                    
                    // Insert order items
                    foreach ($_SESSION['cart'] as $item) {
                        $product_id = $item['id'];
                        $quantity = $item['quantity'];
                        $price = $item['price'];
                        $item_total = $price * $quantity;
                        
                        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                                        VALUES ($order_id, $product_id, $quantity, $price, $item_total)";
                        mysqli_query($conn, $item_query);
                        
                        // Update product inventory (if applicable)
                        $update_inventory = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id AND stock >= $quantity";
                        mysqli_query($conn, $update_inventory);
                    }
                    
                    // Add initial status to history
                    $status_note = ($payment_method === 'cod') ? 'Order received, awaiting payment on delivery.' : 'Order received and payment completed.';
                    $history_query = "INSERT INTO order_status_history (order_id, status, notes, created_at) 
                                    VALUES ($order_id, '$order_status', '$status_note', NOW())";
                    mysqli_query($conn, $history_query);
                    
                    // If GCash payment, save payment details
                    if ($payment_method === 'gcash') {
                        $gcash_reference = mysqli_real_escape_string($conn, $_POST['gcash_reference']);
                        $gcash_number = mysqli_real_escape_string($conn, $_POST['gcash_number']);
                        
                        $payment_query = "INSERT INTO payments (order_id, payment_method, reference_number, account_number, amount, status, created_at) 
                                        VALUES ($order_id, 'gcash', '$gcash_reference', '$gcash_number', $total, 'completed', NOW())";
                        mysqli_query($conn, $payment_query);
                    }
                    
                    // Commit transaction
                    mysqli_commit($conn);
                    
                    // Clear cart
                    unset($_SESSION['cart']);
                    
                    // Redirect to order confirmation page
                    header("Location: index.php?page=order_confirmation&order_id=$order_id");
                    exit;
                } else {
                    throw new Exception("Failed to create order");
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                mysqli_rollback($conn);
                $errors[] = 'Failed to create order. Please try again. Error: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="h3 mb-4">Checkout</h1>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Shipping Address Section -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($addresses)): ?>
                        <div class="mb-3">
                            <?php foreach ($addresses as $address): ?>
                            <div class="form-check mb-3 border p-3 rounded<?php echo $address['is_default'] ? ' border-primary' : ''; ?>">
                                <input class="form-check-input" type="radio" name="shipping_address" id="address-<?php echo $address['id']; ?>" value="<?php echo $address['id']; ?>" <?php echo $address['is_default'] ? 'checked' : ''; ?>>
                                <label class="form-check-label w-100" for="address-<?php echo $address['id']; ?>">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-medium"><?php echo $address['full_name']; ?></span>
                                        <?php if ($address['is_default']): ?>
                                        <span class="badge bg-primary">Default</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small"><?php echo $address['phone']; ?></div>
                                    <div class="mt-1">
                                        <?php echo $address['address_line1']; ?>
                                        <?php if (!empty($address['address_line2'])): ?>, <?php echo $address['address_line2']; ?><?php endif; ?>,
                                        <?php echo $address['city']; ?>, <?php echo $address['province']; ?>, <?php echo $address['postal_code']; ?>
                                    </div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="form-check mb-3 border p-3 rounded">
                                <input class="form-check-input" type="radio" name="shipping_address" id="new-address" value="new">
                                <label class="form-check-label w-100" for="new-address">
                                    <span class="fw-medium">Add a new address</span>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- New Address Form -->
                        <div id="new-address-form" class="<?php echo empty($addresses) ? '' : 'd-none'; ?>">
                            <input type="hidden" name="new_address" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo isset($user) ? $user['full_name'] : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address_line1" class="form-label">Address Line 1</label>
                                <input type="text" class="form-control" id="address_line1" name="address_line1" placeholder="Street address, P.O. box, company name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
                                <input type="text" class="form-control" id="address_line2" name="address_line2" placeholder="Apartment, suite, unit, building, floor, etc.">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="province" class="form-label">Province</label>
                                    <input type="text" class="form-control" id="province" name="province" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="save_as_default" name="save_as_default" value="1" checked>
                                <label class="form-check-label" for="save_as_default">
                                    Save as default address
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Method Section -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cash on Delivery Option -->
                        <div class="form-check mb-3 border p-3 rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment-cod" value="cod" checked>
                            <label class="form-check-label w-100" for="payment-cod">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium">Cash on Delivery</span>
                                        <div class="text-muted small">Pay when you receive your order</div>
                                    </div>
                                </div>
                                <div class="mt-2 p-2 bg-light rounded small">
                                    <p class="mb-0"><i class="fas fa-info-circle me-1"></i> Our delivery partner will collect the payment when they deliver your order.</p>
                                    <p class="mb-0 mt-1"><i class="fas fa-check-circle me-1 text-success"></i> No additional fees for Cash on Delivery</p>
                                </div>
                            </label>
                        </div>
                        
                        <!-- GCash Option -->
                        <div class="form-check mb-3 border p-3 rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment-gcash" value="gcash">
                            <label class="form-check-label w-100" for="payment-gcash">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img src="https://www.gcash.com/wp-content/uploads/2019/04/gcash-logo.png" alt="GCash" width="60">
                                    </div>
                                    <div>
                                        <span class="fw-medium">GCash</span>
                                        <div class="text-muted small">Pay using your GCash account</div>
                                    </div>
                                </div>
                                <div class="mt-2 p-2 bg-light rounded small">
                                    <div><strong>GCash Number:</strong> 0917-123-4567</div>
                                    <div><strong>Account Name:</strong> ArtiSell Inc.</div>
                                </div>
                            </label>
                        </div>
                        
                        <!-- GCash Payment Details (initially hidden) -->
                        <div id="gcash-details" class="mt-3 p-3 border rounded d-none">
                            <h6 class="mb-3">GCash Payment Details</h6>
                            <p class="small text-muted mb-3">Please send the payment to our GCash account and provide the details below:</p>
                            
                            <div class="mb-3">
                                <label for="gcash_number" class="form-label">Your GCash Number</label>
                                <input type="text" class="form-control" id="gcash_number" name="gcash_number" placeholder="09XX-XXX-XXXX">
                            </div>
                            
                            <div class="mb-3">
                                <label for="gcash_reference" class="form-label">GCash Reference Number</label>
                                <input type="text" class="form-control" id="gcash_reference" name="gcash_reference" placeholder="Enter the reference number from your GCash receipt">
                            </div>
                            
                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle me-2"></i>
                                After sending the payment, please enter the reference number from your GCash receipt to complete your order.
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items Section -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0 me-3">
                                <div style="width: 60px; height: 60px;" class="border rounded overflow-hidden">
                                    <img src="<?php echo $item['image']; ?>" class="img-fluid h-100 w-100 object-fit-cover" alt="<?php echo $item['title']; ?>">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo $item['title']; ?></h6>
                                <p class="text-muted small mb-1">by <?php echo $item['artist']; ?></p>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Qty: <?php echo $item['quantity']; ?></span>
                                    <span class="fw-medium">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary py-2">Place Order</button>
                </div>
            </form>
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
                        <span class="fw-medium">₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <span class="fw-medium">₱<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax</span>
                        <span class="fw-medium">₱<?php echo number_format($tax, 2); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-0">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle new address form
    const addressRadios = document.querySelectorAll('input[name="shipping_address"]');
    const newAddressForm = document.getElementById('new-address-form');
    const newAddressInput = document.querySelector('input[name="new_address"]');
    
    addressRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'new') {
                newAddressForm.classList.remove('d-none');
                newAddressInput.value = '1';
            } else {
                newAddressForm.classList.add('d-none');
                newAddressInput.value = '0';
            }
        });
    });
    
    // Toggle GCash payment details
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const gcashDetails = document.getElementById('gcash-details');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'gcash') {
                gcashDetails.classList.remove('d-none');
            } else {
                gcashDetails.classList.add('d-none');
            }
        });
    });
});
</script>