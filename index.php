<?php
// Start session for user authentication and cart management
session_start();

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'artisell_db';

$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get current page from URL parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Include header
include 'includes/header.php';

// Load appropriate page content
switch ($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'product':
        include 'pages/product_detail.php';
        break;
    case 'cart':
        include 'pages/cart.php';
        break;
    case 'checkout':
        include 'pages/checkout.php';
        break;
    case 'order_confirmation':
        include 'pages/order_confirmation.php';
        break;
    case 'orders':
        include 'pages/orders.php';
        break;
    case 'login':
        include 'pages/login.php';
        break;
    case 'register':
        include 'pages/register.php';
        break;
    case 'artist':
        include 'pages/artist_profile.php';
        break;
    case 'category':
        include 'pages/category.php';
        break;
    default:
        include 'pages/home.php';
        break;
}

// Include footer
include 'includes/footer.php';

// Close database connection
mysqli_close($conn);
?>