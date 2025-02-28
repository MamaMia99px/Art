<?php
// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$success_message = '';
$form_data = [
    'full_name' => '',
    'email' => '',
];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['full_name'] = trim($_POST['full_name']);
    $form_data['email'] = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms_accepted = isset($_POST['terms_accepted']) ? true : false;
    
    // Validate full name
    if (empty($form_data['full_name']) || strlen($form_data['full_name']) < 2) {
        $error_message = 'Full name must be at least 2 characters.';
    }
    // Validate email
    elseif (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    }
    // Check if email already exists
    else {
        $email = $form_data['email'];
        $query = "SELECT id FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $error_message = 'Email address is already registered.';
        }
    }
    
    // Validate password
    if (empty($error_message)) {
        if (empty($password) || strlen($password) < 6) {
            $error_message = 'Password must be at least 6 characters.';
        }
        elseif ($password !== $confirm_password) {
            $error_message = 'Passwords do not match.';
        }
    }
    
    // Validate terms acceptance
    if (empty($error_message) && !$terms_accepted) {
        $error_message = 'You must accept the terms and conditions.';
    }
    
    // If no errors, create user account
    if (empty($error_message)) {
        $full_name = $form_data['full_name'];
        $email = $form_data['email'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (full_name, email, password, created_at) VALUES ('$full_name', '$email', '$hashed_password', NOW())";
        
        if (mysqli_query($conn, $query)) {
            $success_message = 'Your account has been created successfully. You can now <a href="index.php?page=login">login</a>.';
            // Clear form data
            $form_data = [
                'full_name' => '',
                'email' => '',
            ];
        } else {
            $error_message = 'An error occurred. Please try again later.';
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Create an Account</h2>
                        <p class="text-muted">Join ArtiSell to discover and purchase unique Cebuano art</p>
                    </div>
                    
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php else: ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($form_data['full_name']); ?>" placeholder="John Doe" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" placeholder="you@example.com" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="******" required>
                            <div class="form-text">Password must be at least 6 characters.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="******" required>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms_accepted" name="terms_accepted" required>
                                <label class="form-check-label" for="terms_accepted">
                                    I agree to the <a href="#" class="text-decoration-none">terms of service</a> and <a href="#" class="text-decoration-none">privacy policy</a>
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2">Create Account</button>
                        </div>
                    </form>
                    
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <a href="index.php?page=login" class="text-decoration-none">Sign in</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>