<?php
// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
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

// Get user's addresses
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
$success = false;
$order_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate shipping information
    $shipping_name = trim($_POST['shipping_name'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $shipping_city = trim($_POST['shipping_city'] ?? '');
    $shipping_province = trim($_POST['shipping_province'] ?? '');
    $shipping_postal_code = trim($_POST['shipping_postal_code'] ?? '');
    $shipping_phone = trim($_POST['shipping_phone'] ?? '');
    $shipping_email = trim($_POST['shipping_email'] ?? '');
    $save_address = isset($_POST['save_address']) ? 1 : 0;
    
    // Validate payment information
    $payment_method = $_POST['payment_method'] ?? '';
    $card_number = trim($_POST['card_number'] ?? '');
    $card_name = trim($_POST['card_name'] ?? '');
    $card_expiry = trim($_POST['card_expiry'] ?? '');
    $card_cvv = trim($_POST['card_cvv'] ?? '');
    
    // Basic validation
    if (empty($shipping_name)) $errors[] = 'Full name is required';
    if (empty($shipping_address)) $errors[] = 'Address is required';
    if (empty($shipping_city)) $errors[] = 'City is required';
    if (empty($shipping_province)) $errors[] = 'Province is required';
    if (empty($shipping_postal_code)) $errors[] = 'Postal code is required';
    if (empty($shipping_phone)) $errors[] = 'Phone number is required';
    if (empty($shipping_email) || !filter_var($shipping_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    
    if (empty($payment_method)) {
        $errors[] = 'Payment method is required';
    } else if ($payment_method === 'credit_card') {
        if (empty($card_number) || !preg_match('/^[0-9]{16}$/', str_replace(' ', '', $card_number))) $errors[] = 'Valid card number is required';
        if (empty($card_name)) $errors[] = 'Name on card is required';
        if (empty($card_expiry) || !preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $card_expiry)) $errors[] = 'Valid expiry date (MM/YY) is required';
        if (empty($card_cvv) || !preg_match('/^[0-9]{3,4}$/', $card_cvv)) $errors[] = 'Valid CVV is required';
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Save address if requested
            if ($save_address) {
                $address_query = "INSERT INTO user_addresses (user_id, full_name, address_line, city, province, postal_code, phone, email, is_default) 
                                VALUES ($user_id, '$shipping_name', '$shipping_address', '$shipping_city', '$shipping_province', '$shipping_postal_code', '$shipping_phone', '$shipping_email', 0)";
                mysqli_query($conn, $address_query);
            }
            
            // Create order
            $order_date = date('Y-m-d H:i:s');
            $payment_status = ($payment_method === 'cod') ? 'pending' : 'paid';
            $order_status = 'processing';
            
            $order_query = "INSERT INTO orders (user_id, order_date, subtotal, shipping_fee, tax, total_amount, shipping_name, shipping_address, shipping_city, shipping_province, shipping_postal_code, shipping_phone, shipping_email, payment_method, payment_status, order_status) 
                            VALUES ($user_id, '$order_date', $subtotal, $shipping, $tax, $total, '$shipping_name', '$shipping_address', '$shipping_city', '$shipping_province', '$shipping_postal_code', '$shipping_phone', '$shipping_email', '$payment_method', '$payment_status', '$order_status')";
            
            mysqli_query($conn, $order_query);
            $order_id = mysqli_insert_id($conn);
            
            // Add order items
            foreach ($_SESSION['cart'] as $item) {
                $product_id = $item['id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $subtotal = $price * $quantity;
                
                $order_item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                                    VALUES ($order_id, $product_id, $quantity, $price, $subtotal)";
                mysqli_query($conn, $order_item_query);
                
                // Update product inventory (if applicable)
                $update_inventory_query = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id AND stock >= $quantity";
                mysqli_query($conn, $update_inventory_query);
            }
            
            // If payment method is credit card, save payment details
            if ($payment_method === 'credit_card') {
                // In a real application, you would integrate with a payment gateway here
                // For this example, we'll just simulate a successful payment
                $last_four = substr(str_replace(' ', '', $card_number), -4);
                $payment_query = "INSERT INTO payments (order_id, payment_method, amount, status, transaction_id, card_last_four) 
                                VALUES ($order_id, 'credit_card', $total, 'completed', CONCAT('TXN-', FLOOR(RAND() * 1000000)), '$last_four')";
                mysqli_query($conn, $payment_query);
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Clear cart
            unset($_SESSION['cart']);
            
            // Set success flag
            $success = true;
            
            // Redirect to order confirmation page
            header("Location: index.php?page=order_confirmation&id=$order_id");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $errors[] = 'An error occurred while processing your order. Please try again.';
        }
    }
}
?>

<div class="container py-5">
    <h1 class="h2 mb-4">Checkout</h1>
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" id="checkout-form">
        <div class="row">
            <!-- Shipping Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($addresses)): ?>
                        <div class="mb-4">
                            <label class="form-label">Select a saved address</label>
                            <select class="form-select" id="saved-address">
                                <option value="">Use a new address</option>
                                <?php foreach ($addresses as $address): ?>
                                <option value="<?php echo $address['id']; ?>" data-name="<?php echo $address['full_name']; ?>" data-address="<?php echo $address['address_line']; ?>" data-city="<?php echo $address['city']; ?>" data-province="<?php echo $address['province']; ?>" data-postal="<?php echo $address['postal_code']; ?>" data-phone="<?php echo $address['phone']; ?>" data-email="<?php echo $address['email']; ?>">
                                    <?php echo $address['full_name']; ?> - <?php echo $address['address_line']; ?>, <?php echo $address['city']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="shipping_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="shipping_name" name="shipping_name" value="<?php echo $user['full_name'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="col-12">
                                <label for="shipping_address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="shipping_address" name="shipping_address" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="shipping_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="shipping_province" class="form-label">Province</label>
                                <input type="text" class="form-control" id="shipping_province" name="shipping_province" required>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="shipping_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="shipping_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone" value="<?php echo $user['phone'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="shipping_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="shipping_email" name="shipping_email" value="<?php echo $user['email']; ?>" required>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="save_address" name="save_address" value="1">
                                    <label class="form-check-label" for="save_address">Save this address for future orders</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_credit_card" value="credit_card" checked>
                                <label class="form-check-label" for="payment_credit_card">
                                    <i class="fab fa-cc-visa me-2"></i>Credit / Debit Card
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod">
                                <label class="form-check-label" for="payment_cod">
                                    <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                                </label>
                            </div>
                        </div>
                        
                        <div id="credit-card-form">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                                </div>
                                
                                <div class="col-12">
                                    <label for="card_name" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control" id="card_name" name="card_name">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="card_expiry" class="form-label">Expiration Date (MM/YY)</label>
                                    <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="card_cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="card_cvv" name="card_cvv" placeholder="123">
                                </div>
                            </div>
                        </div>
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
                        <div class="mb-4">
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>
                                    <?php echo $item['title']; ?> 
                                    <span class="text-muted">x <?php echo $item['quantity']; ?></span>
                                </span>
                                <span>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <hr>
                        
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
                        
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold fs-5">₱<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2">Place Order</button>
                        
                        <div class="text-center mt-3">
                            <a href="index.php?page=cart" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-2"></i>Return to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Handle saved address selection
document.addEventListener('DOMContentLoaded', function() {
    const savedAddressSelect = document.getElementById('saved-address');
    if (savedAddressSelect) {
        savedAddressSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                // Fill form with selected address
                document.getElementById('shipping_name').value = selectedOption.dataset.name;
                document.getElementById('shipping_address').value = selectedOption.dataset.address;
                document.getElementById('shipping_city').value = selectedOption.dataset.city;
                document.getElementById('shipping_province').value = selectedOption.dataset.province;
                document.getElementById('shipping_postal_code').value = selectedOption.dataset.postal;
                document.getElementById('shipping_phone').value = selectedOption.dataset.phone;
                document.getElementById('shipping_email').value = selectedOption.dataset.email;
            } else {
                // Clear form
                document.getElementById('shipping_name').value = '<?php echo $user['full_name'] ?? ''; ?>';
                document.getElementById('shipping_address').value = '';
                document.getElementById('shipping_city').value = '';
                document.getElementById('shipping_province').value = '';
                document.getElementById('shipping_postal_code').value = '';
                document.getElementById('shipping_phone').value = '<?php echo $user['phone'] ?? ''; ?>';
                document.getElementById('shipping_email').value = '<?php echo $user['email']; ?>';
            }
        });
    }
    
    // Toggle credit card form based on payment method
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const creditCardForm = document.getElementById('credit-card-form');
    
    paymentMethods.forEach(function(method) {
        method.addEventListener('change', function() {
            if (this.value === 'credit_card') {
                creditCardForm.style.display = 'block';
            } else {
                creditCardForm.style.display = 'none';
            }
        });
    });
    
    // Format credit card number with spaces
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            e.target.value = formattedValue;
        });
    }
    
    // Format expiry date
    const expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            e.target.value = value;
        });
    }
});
</script>