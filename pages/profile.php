<?php
// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'index.php?page=profile';
    redirect('index.php?page=login');
}

$user_id = $_SESSION['user_id'];
$user = getUserDetails($user_id);

// Get user addresses
$addresses_query = "SELECT * FROM user_addresses WHERE user_id = $user_id ORDER BY is_default DESC";
$addresses_result = mysqli_query($conn, $addresses_query);
$addresses = [];

while ($address = mysqli_fetch_assoc($addresses_result)) {
    $addresses[] = $address;
}

// Handle profile update
$success_message = '';
$error_message = '';

if (isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($full_name)) {
        $error_message = 'Full name is required';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Valid email is required';
    } else {
        // Check if email already exists for another user
        $check_query = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = 'Email is already in use by another account';
        } else {
            // Update profile
            $update_query = "UPDATE users SET full_name = '$full_name', email = '$email', phone = '$phone' WHERE id = $user_id";
            
            if (mysqli_query($conn, $update_query)) {
                // Update session data
                $_SESSION['user_name'] = $full_name;
                
                // Check if password change is requested
                if (!empty($current_password) && !empty($new_password)) {
                    // Verify current password
                    $password_query = "SELECT password FROM users WHERE id = $user_id";
                    $password_result = mysqli_query($conn, $password_query);
                    $user_data = mysqli_fetch_assoc($password_result);
                    
                    if (password_verify($current_password, $user_data['password'])) {
                        // Check if new passwords match
                        if ($new_password === $confirm_password) {
                            // Update password
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $password_update_query = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
                            
                            if (mysqli_query($conn, $password_update_query)) {
                                $success_message = 'Profile and password updated successfully';
                            } else {
                                $error_message = 'Failed to update password';
                            }
                        } else {
                            $error_message = 'New passwords do not match';
                        }
                    } else {
                        $error_message = 'Current password is incorrect';
                    }
                } else {
                    $success_message = 'Profile updated successfully';
                }
            } else {
                $error_message = 'Failed to update profile';
            }
        }
    }
}

// Handle address actions
if (isset($_POST['add_address'])) {
    $full_name = sanitize($_POST['address_full_name']);
    $address_line = sanitize($_POST['address_line']);
    $city = sanitize($_POST['city']);
    $province = sanitize($_POST['province']);
    $postal_code = sanitize($_POST['postal_code']);
    $phone = sanitize($_POST['address_phone']);
    $email = sanitize($_POST['address_email']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    // Validate inputs
    if (empty($full_name) || empty($address_line) || empty($city) || empty($province) || empty($postal_code) || empty($phone) || empty($email)) {
        $error_message = 'All address fields are required';
    } else {
        // If setting as default, unset current default
        if ($is_default) {
            $update_query = "UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id";
            mysqli_query($conn, $update_query);
        }
        
        // Add new address
        $insert_query = "INSERT INTO user_addresses (user_id, full_name, address_line, city, province, postal_code, phone, email, is_default) 
                        VALUES ($user_id, '$full_name', '$address_line', '$city', '$province', '$postal_code', '$phone', '$email', $is_default)";
        
        if (mysqli_query($conn, $insert_query)) {
            $success_message = 'Address added successfully';
            // Refresh page to show new address
            redirect('index.php?page=profile');
        } else {
            $error_message = 'Failed to add address';
        }
    }
}

if (isset($_GET['delete_address'])) {
    $address_id = intval($_GET['delete_address']);
    
    // Check if address belongs to user
    $check_query = "SELECT id FROM user_addresses WHERE id = $address_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Delete address
        $delete_query = "DELETE FROM user_addresses WHERE id = $address_id";
        
        if (mysqli_query($conn, $delete_query)) {
            $success_message = 'Address deleted successfully';
            // Refresh page
            redirect('index.php?page=profile');
        } else {
            $error_message = 'Failed to delete address';
        }
    } else {
        $error_message = 'Address not found';
    }
}

if (isset($_GET['set_default'])) {
    $address_id = intval($_GET['set_default']);
    
    // Check if address belongs to user
    $check_query = "SELECT id FROM user_addresses WHERE id = $address_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Unset current default
        $update_query = "UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id";
        mysqli_query($conn, $update_query);
        
        // Set new default
        $default_query = "UPDATE user_addresses SET is_default = 1 WHERE id = $address_id";
        
        if (mysqli_query($conn, $default_query)) {
            $success_message = 'Default address updated';
            // Refresh page
            redirect('index.php?page=profile');
        } else {
            $error_message = 'Failed to update default address';
        }
    } else {
        $error_message = 'Address not found';
    }
}
?>

<div class="container py-5">
    <h1 class="h2 mb-4">My Profile</h1>
    
    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-4 mb-4 mb-lg-0">
            <!-- Profile Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 24px;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0"><?php echo $user['full_name']; ?></h5>
                            <p class="text-muted mb-0"><?php echo $user['email']; ?></p>
                        </div>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <a href="#profile-info" class="list-group-item list-group-item-action active" data-bs-toggle="list">Profile Information</a>
                        <a href="#addresses" class="list-group-item list-group-item-action" data-bs-toggle="list">My Addresses</a>
                        <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="list">Change Password</a>
                        <a href="index.php?page=orders" class="list-group-item list-group-item-action">My Orders</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="tab-content">
                <!-- Profile Information -->
                <div class="tab-pane fade show active" id="profile-info">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Addresses -->
                <div class="tab-pane fade" id="addresses">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">My Addresses</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="fas fa-plus me-1"></i> Add New Address
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (empty($addresses)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">You don't have any saved addresses yet.</p>
                            </div>
                            <?php else: ?>
                            <div class="row">
                                <?php foreach ($addresses as $address): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 <?php echo $address['is_default'] ? 'border-primary' : ''; ?>">
                                        <div class="card-body">
                                            <?php if ($address['is_default']): ?>
                                            <div class="badge bg-primary mb-2">Default</div>
                                            <?php endif; ?>
                                            
                                            <h6 class="card-title"><?php echo $address['full_name']; ?></h6>
                                            <p class="card-text">
                                                <?php echo $address['address_line']; ?><br>
                                                <?php echo $address['city']; ?>, <?php echo $address['province']; ?> <?php echo $address['postal_code']; ?><br>
                                                Phone: <?php echo $address['phone']; ?><br>
                                                Email: <?php echo $address['email']; ?>
                                            </p>
                                            
                                            <div class="d-flex justify-content-between mt-3">
                                                <?php if (!$address['is_default']): ?>
                                                <a href="index.php?page=profile&set_default=<?php echo $address['id']; ?>" class="btn btn-sm btn-outline-primary">Set as Default</a>
                                                <?php else: ?>
                                                <div></div>
                                                <?php endif; ?>
                                                
                                                <a href="index.php?page=profile&delete_address=<?php echo $address['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this address?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="tab-pane fade" id="password">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="full_name" value="<?php echo $user['full_name']; ?>">
                                <input type="hidden" name="email" value="<?php echo $user['email']; ?>">
                                <input type="hidden" name="phone" value="<?php echo $user['phone']; ?>">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAddressModalLabel">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="address_full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="address_full_name" name="address_full_name" value="<?php echo $user['full_name']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="address_phone" name="address_phone" value="<?php echo $user['phone']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address_line" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address_line" name="address_line" placeholder="Street address, building, etc." required>
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
                    
                    <div class="mb-3">
                        <label for="address_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="address_email" name="address_email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1">
                        <label class="form-check-label" for="is_default">Set as default address</label>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_address" class="btn btn-primary">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Activate tab based on URL hash
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash || '#profile-info';
    const tab = document.querySelector(`a[href="${hash}"]`);
    if (tab) {
        const bsTab = new bootstrap.Tab(tab);
        bsTab.show();
    }
    
    // Update URL hash when tab changes
    const tabs = document.querySelectorAll('a[data-bs-toggle="list"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            window.location.hash = e.target.getAttribute('href');
        });
    });
});
</script>