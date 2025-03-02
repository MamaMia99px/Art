<?php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';
require_once 'includes/buy_now_functions.php';

// Get page from URL parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Include header
include 'includes/header.php';

// Load appropriate page content
switch ($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'category':
        include 'pages/category.php';
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
    case 'order_detail':
        include 'pages/order_detail.php';
        break;
    case 'orders':
        include 'pages/orders.php';
        break;
    case 'track_order':
        include 'pages/track_order.php';
        break;
    case 'login':
        include 'pages/login.php';
        break;
    case 'register':
        include 'pages/register.php';
        break;
    case 'logout':
        include 'includes/logout.php';
        break;
    case 'artist':
        include 'pages/artist_profile.php';
        break;
    case 'about':
        include 'pages/about.php';
        break;
    case 'profile':
        include 'pages/profile.php';
        break;
    default:
        include 'pages/home.php';
        break;
}

// Include footer
include 'includes/footer.php';
?>