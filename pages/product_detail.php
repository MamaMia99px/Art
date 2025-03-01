<?php
// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get product details
$product_query = "SELECT p.*, c.name as category_name, a.name as artist_name, a.id as artist_id, a.location as artist_location 
               FROM products p 
               JOIN categories c ON p.category_id = c.id 
               JOIN artists a ON p.artist_id = a.id 
               WHERE p.id = $product_id";
$product_result = mysqli_query($conn, $product_query);

if (mysqli_num_rows($product_result) === 0) {
    header('Location: index.php');
    exit;
}

$product = mysqli_fetch_assoc($product_result);

// Get product images
$images_query = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_primary DESC";
$images_result = mysqli_query($conn, $images_query);
$product_images = [];

while ($image = mysqli_fetch_assoc($images_result)) {
    $product_images[] = $image;
}

// If no images found, use the main product image
if (empty($product_images)) {
    $product_images = [
        ['image_url' => $product['image'], 'is_primary' => 1]
    ];
}

// Get related products
$related_query = "SELECT p.*, a.name as artist_name 
                FROM products p 
                JOIN artists a ON p.artist_id = a.id 
                WHERE p.category_id = {$product['category_id']} AND p.id != $product_id 
                LIMIT 4";
$related_result = mysqli_query($conn, $related_query);
$related_products = [];

while ($related = mysqli_fetch_assoc($related_result)) {
    $related_products[] = $related;
}

// Check if product was added to cart
$added_to_cart = isset($_GET['added']) && $_GET['added'] == 1;

// Handle Buy Now action
if (isset($_POST['buy_now'])) {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($quantity > 0 && $product_id > 0) {
        // Clear current cart and add only this item
        unset($_SESSION['cart']);
        $_SESSION['cart'] = [];
        
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
        
        // Redirect to checkout
        header("Location: index.php?page=checkout");
        exit;
    }
}
?>

<div class="container py-5">
    <?php if ($added_to_cart): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> The product has been added to your cart.
        <a href="index.php?page=cart" class="alert-link">View Cart</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Product Images -->
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="mb-3">
                <img id="main-product-image" src="<?php echo $product_images[0]['image_url']; ?>" alt="<?php echo $product['title']; ?>" class="img-fluid rounded shadow-sm" style="width: 100%; height: 400px; object-fit: cover;">
            </div>
            
            <?php if (count($product_images) > 1): ?>
            <div class="row product-image-thumbs">
                <?php foreach ($product_images as $index => $image): ?>
                <div class="col-3">
                    <img src="<?php echo $image['image_url']; ?>" alt="Product thumbnail" class="img-fluid rounded cursor-pointer <?php echo $index === 0 ? 'border border-primary' : ''; ?>" onclick="changeMainImage(this, '<?php echo $image['image_url']; ?>')" style="height: 80px; object-fit: cover;">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Details -->
        <div class="col-md-6">
            <div class="mb-3">
                <span class="badge bg-secondary"><?php echo $product['category_name']; ?></span>
            </div>
            
            <h1 class="h2 mb-2"><?php echo $product['title']; ?></h1>
            
            <div class="mb-3">
                <p class="mb-1">by <a href="index.php?page=artist&id=<?php echo $product['artist_id']; ?>" class="text-decoration-none"><?php echo $product['artist_name']; ?></a></p>
                <p class="text-muted small"><?php echo $product['artist_location']; ?></p>
            </div>
            
            <div class="mb-4">
                <h3 class="h4 text-primary mb-0">₱<?php echo number_format($product['price'], 2); ?></h3>
                <p class="text-muted small mt-1"><?php echo $product['in_stock'] ? 'In Stock' : 'Out of Stock'; ?></p>
            </div>
            
            <div class="mb-4">
                <h4 class="h5 mb-3">Description</h4>
                <p><?php echo $product['description']; ?></p>
            </div>
            
            <!-- Add to Cart Form -->
            <form method="POST" action="" class="mb-4">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                
                <div class="row align-items-center mb-3">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="quantity" class="form-label">Quantity</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary quantity-decrease" onclick="decreaseQuantity()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantity" name="quantity" class="form-control text-center" value="1" min="1" max="10">
                            <button type="button" class="btn btn-outline-secondary quantity-increase" onclick="increaseQuantity()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <!-- Add to Cart Button -->
                    <button type="submit" formaction="index.php?page=cart" name="action" value="add" class="btn btn-outline-primary flex-grow-1" <?php echo !$product['in_stock'] ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                    
                    <!-- Buy Now Button -->
                    <button type="submit" name="buy_now" class="btn btn-primary flex-grow-1" <?php echo !$product['in_stock'] ? 'disabled' : ''; ?>>
                        <i class="fas fa-bolt me-2"></i>Buy Now
                    </button>
                </div>
            </form>
            
            <div class="d-flex justify-content-between">
                <button class="btn btn-outline-secondary">
                    <i class="far fa-heart me-2"></i>Add to Wishlist
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-share-alt me-2"></i>Share
                </button>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <div class="mt-5">
        <h2 class="h3 mb-4">You May Also Like</h2>
        
        <div class="row">
            <?php foreach ($related_products as $related): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 product-card">
                    <img src="<?php echo $related['image']; ?>" class="card-img-top" alt="<?php echo $related['title']; ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $related['title']; ?></h5>
                        <p class="card-text text-muted small">by <?php echo $related['artist_name']; ?></p>
                        <p class="card-text fw-bold text-primary">₱<?php echo number_format($related['price'], 2); ?></p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="index.php?page=product&id=<?php echo $related['id']; ?>" class="btn btn-outline-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function changeMainImage(thumbnail, imageUrl) {
    // Update main image
    document.getElementById('main-product-image').src = imageUrl;
    
    // Update active thumbnail
    const thumbnails = document.querySelectorAll('.product-image-thumbs img');
    thumbnails.forEach(thumb => thumb.classList.remove('border', 'border-primary'));
    thumbnail.classList.add('border', 'border-primary');
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

function increaseQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue < 10) {
        input.value = currentValue + 1;
    }
}
</script>