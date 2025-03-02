<?php
/**
 * General functions for the ArtiSell platform
 */

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Format price with currency symbol
 * 
 * @param float $price Price to format
 * @return string Formatted price
 */
function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

/**
 * Get CSS class for order status badge
 * 
 * @param string $status Order status
 * @return string CSS class for the badge
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-warning text-dark';
        case 'processing':
            return 'bg-info text-dark';
        case 'shipped':
            return 'bg-primary';
        case 'delivered':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

/**
 * Get categories from database
 * 
 * @param int $limit Optional limit for number of categories
 * @return array Array of category data
 */
function getCategories($limit = 0) {
    global $conn;
    
    $query = "SELECT * FROM categories ORDER BY name ASC";
    
    if ($limit > 0) {
        $query .= " LIMIT $limit";
    }
    
    $result = mysqli_query($conn, $query);
    $categories = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

/**
 * Get featured products
 * 
 * @param int $limit Optional limit for number of products
 * @return array Array of product data
 */
function getFeaturedProducts($limit = 8) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name, a.name as artist_name 
              FROM products p 
              JOIN categories c ON p.category_id = c.id 
              JOIN artists a ON p.artist_id = a.id 
              WHERE p.is_featured = 1 
              ORDER BY p.created_at DESC 
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $products = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    
    return $products;
}

/**
 * Get featured artists
 * 
 * @param int $limit Optional limit for number of artists
 * @return array Array of artist data
 */
function getFeaturedArtists($limit = 3) {
    global $conn;
    
    $query = "SELECT a.*, 
              (SELECT COUNT(*) FROM products WHERE artist_id = a.id) as product_count,
              (SELECT image FROM products WHERE artist_id = a.id ORDER BY created_at DESC LIMIT 1) as work_sample
              FROM artists a 
              WHERE a.is_featured = 1 
              ORDER BY a.name ASC 
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $artists = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $artists[] = $row;
        }
    }
    
    return $artists;
}

/**
 * Get product details by ID
 * 
 * @param int $product_id Product ID
 * @return array|bool Product data or false if not found
 */
function getProductById($product_id) {
    global $conn;
    
    $product_id = intval($product_id);
    
    $query = "SELECT p.*, c.name as category_name, a.name as artist_name, a.id as artist_id, a.location as artist_location 
              FROM products p 
              JOIN categories c ON p.category_id = c.id 
              JOIN artists a ON p.artist_id = a.id 
              WHERE p.id = $product_id";
    
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

/**
 * Get related products
 * 
 * @param int $product_id Current product ID
 * @param int $category_id Category ID for related products
 * @param int $limit Optional limit for number of products
 * @return array Array of related product data
 */
function getRelatedProducts($product_id, $category_id, $limit = 4) {
    global $conn;
    
    $product_id = intval($product_id);
    $category_id = intval($category_id);
    
    $query = "SELECT p.*, a.name as artist_name 
              FROM products p 
              JOIN artists a ON p.artist_id = a.id 
              WHERE p.category_id = $category_id AND p.id != $product_id 
              ORDER BY RAND() 
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $products = [];
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    
    return $products;
}

/**
 * Calculate cart totals
 * 
 * @return array Array with subtotal, shipping, tax, and total
 */
function calculateCartTotals() {
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
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'items_count' => $items_count
    ];
}
