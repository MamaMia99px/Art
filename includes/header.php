<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtiSell - Cebu Local Art E-commerce Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                <span class="fw-bold text-primary">ArtiSell</span>
                <span class="d-none d-md-inline text-muted ms-1 small">Cebu Art Marketplace</span>
            </a>
            
            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=category">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=about">About Us</a>
                    </li>
                </ul>
                
                <!-- Search Form -->
                <form class="d-flex me-3 d-none d-lg-flex">
                    <div class="input-group">
                        <input class="form-control" type="search" placeholder="Search for art..." aria-label="Search">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Cart Button -->
                <a href="index.php?page=cart" class="btn btn-outline-primary position-relative me-2">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php 
                        $cart_count = 0;
                        foreach ($_SESSION['cart'] as $item) {
                            $cart_count += $item['quantity'];
                        }
                        echo $cart_count;
                        ?>
                    </span>
                    <?php endif; ?>
                </a>
                
                <!-- User Account -->
                <?php if (isLoggedIn()): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $_SESSION['user_name']; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="index.php?page=profile">My Profile</a></li>
                        <li><a class="dropdown-item" href="index.php?page=orders">My Orders</a></li>
                        <li><a class="dropdown-item" href="index.php?page=track_order">Track Order</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php?page=logout">Logout</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="index.php?page=login" class="btn btn-primary me-2">Login</a>
                <a href="index.php?page=register" class="btn btn-outline-primary">Register</a>
                <a href="index.php?page=track_order" class="btn btn-outline-secondary ms-2">Track Order</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Search (visible only on small screens) -->
    <div class="container d-lg-none mt-3 mb-3">
        <form class="d-flex">
            <div class="input-group">
                <input class="form-control" type="search" placeholder="Search for art..." aria-label="Search">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Main Content -->