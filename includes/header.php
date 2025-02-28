<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtiSell - Cebu Art Marketplace</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                <span class="fw-bold text-primary fs-4">ArtiSell</span>
                <span class="d-none d-md-inline-block ms-2 text-muted small">Cebu Art Marketplace</span>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=category">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About Us</a>
                    </li>
                </ul>
                
                <!-- Search and User Actions -->
                <div class="d-flex align-items-center">
                    <!-- Search Form -->
                    <form class="d-none d-md-flex me-3" action="index.php" method="GET">
                        <input type="hidden" name="page" value="category">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search for art...">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Cart Icon -->
                    <a href="index.php?page=cart" class="btn btn-outline-secondary position-relative me-2">
                        <i class="fas fa-shopping-cart"></i>
                        <?php
                        // Display cart item count if items exist
                        if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                            echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">';
                            echo count($_SESSION['cart']);
                            echo '</span>';
                        }
                        ?>
                    </a>
                    
                    <!-- User Account -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <li><span class="dropdown-item-text">Hi, <?php echo $_SESSION['user_name']; ?></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?page=profile">My Profile</a></li>
                                <li><a class="dropdown-item" href="index.php?page=orders">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="includes/logout.php">Logout</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="index.php?page=login">Login</a></li>
                                <li><a class="dropdown-item" href="index.php?page=register">Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <main class="py-4">