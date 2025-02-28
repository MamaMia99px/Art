<?php
// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details from database
$product = null;

if ($product_id > 0) {
    $query = "SELECT p.*, a.name as artist_name, a.location as artist_location, a.image as artist_image, c.name as category_name 
              FROM products p 
              JOIN artists a ON p.artist_id = a.id 
              JOIN categories c ON p.category_id = c.id 
              WHERE p.id = $product_id";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        
        // Get product images
        $images_query = "SELECT image_url FROM product_images WHERE product_id = $product_id";
        $images_result = mysqli_query($conn, $images_query);
        
        $product['images'] = [];
        if (mysqli_num_rows($images_result) > 0) {
            while ($image = mysqli_fetch_assoc($images_result)) {
                $product['images'][] = $image['image_url'];
            }
        } else {
            // If no images found, use the main product image
            $product['images'] = [$product['image']];
        }
    }
}

// If product not found in database, use sample data
if (!$product) {
    $product = [
        'id' => $product_id ?: 1,
        'title' => 'Cebu Traditional Painting',
        'price' => 2500,
        'description' => 'A beautiful traditional painting showcasing the vibrant culture and landscapes of Cebu. This artwork captures the essence of Cebuano heritage with intricate details and vivid colors. Each brushstroke tells a story of the island\'s rich history and natural beauty.',
        'images' => [
            'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
        ],
        'artist_name' => 'Maria Santos',
        'artist_location' => 'Cebu City',
        'artist_image' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Maria',
        'category_name' => 'Painting',
        'rating' => 4.8,
        'reviews' => 24,
        'in_stock' => true
    ];
}

// Handle Add to Cart action
if (isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if product already in cart
    $product_exists = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $product_id) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
            $product_exists = true;
            break;
        }
    }
    
    // If product not in cart, add it
    if (!$product_exists) {
        $_SESSION['cart'][] = [
            'id' => $product_id,
            'title' => $product['title'],
            'price' => $product['price'],
            'image' => $product['images'][0],
            'artist' => $product['artist_name'],
            'quantity' => $quantity
        ];
    }
    
    // Redirect to prevent form resubmission
    header('Location: index.php?page=product&id=' . $product_id . '&added=1');
    exit;
}

// Get related products
$related_products = [];
$category_id = $product['category_id'] ?? 0;

if ($category_id > 0) {
    $query = "SELECT p.id, p.title, p.price, p.image, a.name as artist_name 
              FROM products p 
              JOIN artists a ON p.artist_id = a.id 
              WHERE p.category_id = $category_id AND p.id != $product_id 
              LIMIT 4";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        while ($related = mysqli_fetch_assoc($result)) {
            $related_products[] = $related;
        }
    }
}

// If no related products found, use sample data
if (empty($related_products)) {
    $related_products = [
        ['id' => 2, 'title' => 'Handcrafted Wooden Sculpture', 'price' => 3800, 'artist_name' => 'Juan Reyes', 'image' => 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
        ['id' => 3, 'title' => 'Woven Basket Set', 'price' => 1200, 'artist_name' => 'Elena Flores', 'image' => 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
        ['id' => 4, 'title' => 'Cebu Landscape Oil Painting', 'price' => 4500, 'artist_name' => 'Carlos Mendoza', 'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
        ['id' => 5, 'title' => 'Traditional Food Delicacies Pack', 'price' => 850, 'artist_name' => 'Lorna Bautista', 'image' => 'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80']
    ];
}
?>

<div class="container py-5">
    <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> Product has been added to your cart.
        <a href="index.php?page=cart" class="alert-link">View Cart</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="card mb-5">
        <div class="card-body">
            <div class="row">
                <!-- Product Images -->
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="product-image-main mb-3">
                        <img src="<?php echo $product['images'][0]; ?>" id="main-product-image" class="img-fluid rounded" alt="<?php echo $product['title']; ?>">
                    </div>
                    <div class="row product-image-thumbs">
                        <?php foreach ($product['images'] as $index => $image): ?>
                        <div class="col-3">
                            <img src="<?php echo $image; ?>" class="img-thumbnail <?php echo ($index === 0) ? 'border-primary' : ''; ?>" 
                                 onclick="changeMainImage('<?php echo $image; ?>', this)" alt="<?php echo $product['title']; ?> - view <?php echo $index + 1; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="col-md-6">
                    <span class="badge bg-secondary mb-2"><?php echo $product['category_name']; ?></span>
                    <h1 class="h2 mb-2"><?php echo $product['title']; ?></h1>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo ($i <= floor($product['rating'])) ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-muted small">
                                <?php echo $product['rating']; ?> (<?php echo $product['reviews']; ?> reviews)
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <img src="<?php echo $product['artist_image']; ?>" class="rounded-circle me-2" width="40" height="40" alt="<?php echo $product['artist_name']; ?>">
                        <div>
                            <p class="mb-0 fw-medium">Artist: <?php echo $product['artist_name']; ?></p>
                            <p class="mb-0 small text-muted"><?php echo $product['artist_location']; ?></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-4">
                        <h3 class="h4 text-primary">₱<?php echo number_format($product['price'], 2); ?></h3>
                        <p class="text-muted small">
                            <?php echo $product['in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                        </p>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="d-flex align-items-center mb-4">
                            <div class="input-group me-3" style="width: 130px;">
                                <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity()">-</button>
                                <input type="number" name="quantity" id="quantity" class="form-control text-center" value="1" min="1">
                                <button type="button" class="btn btn-outline-secondary" onclick="increaseQuantity()">+</button>
                            </div>
                            
                            <button type="submit" name="add_to_cart" class="btn btn-primary flex-grow-1" <?php echo $product['in_stock'] ? '' : 'disabled'; ?>>
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary">
                                <i class="far fa-heart"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </form>
                    
                    <ul class="nav nav-tabs mt-5" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">Description</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab">Shipping</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="productTabsContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel">
                            <p><?php echo $product['description']; ?></p>
                        </div>
                        <div class="tab-pane fade" id="details" role="tabpanel">
                            <ul class="list-unstyled">
                                <li class="mb-2"><span class="fw-medium">Medium:</span> Acrylic on Canvas</li>
                                <li class="mb-2"><span class="fw-medium">Dimensions:</span> 24" x 36"</li>
                                <li class="mb-2"><span class="fw-medium">Created:</span> 2023</li>
                                <li class="mb-2"><span class="fw-medium">Authenticity:</span> Signed by artist, includes certificate</li>
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="shipping" role="tabpanel">
                            <ul class="list-unstyled">
                                <li class="mb-2"><span class="fw-medium">Delivery:</span> 3-5 business days within Cebu, 5-7 days nationwide</li>
                                <li class="mb-2"><span class="fw-medium">Shipping Fee:</span> Free shipping within Cebu, ₱250 nationwide</li>
                                <li class="mb-2"><span class="fw-medium">Packaging:</span> Securely packaged to prevent damage during transit</li>
                                <li class="mb-2"><span class="fw-medium">Returns:</span> 7-day return policy for damaged items</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <h2 class="h3 mb-4">You May Also Like</h2>
    <div class="row">
        <?php foreach ($related_products as $related): ?>
        <div class="col-sm-6 col-md-3 mb-4">
            <div class="card h-100 product-card">
                <div style="height: 200px; overflow: hidden;">
                    <img src="<?php echo $related['image']; ?>" class="card-img-top h-100 object-fit-cover" alt="<?php echo $related['title']; ?>">
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo $related['title']; ?></h5>
                    <p class="card-text text-muted small">by <?php echo $related['artist_name']; ?></p>
                    <p class="card-text fw-bold text-primary mt-auto">₱<?php echo number_format($related['price'], 2); ?></p>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-grid">
                        <a href="index.php?page=product&id=<?php echo $related['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function changeMainImage(imageUrl, thumbElement) {
    // Update main image
    document.getElementById('main-product-image').src = imageUrl;
    
    // Update thumbnail selection
    const thumbs = document.querySelectorAll('.product-image-thumbs img');
    thumbs.forEach(thumb => thumb.classList.remove('border-primary'));
    thumbElement.classList.add('border-primary');
}

function decreaseQuantity() {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    if (currentValue > 1) {
        quantityInput.value = currentValue - 1;
    }
}

function increaseQuantity() {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    quantityInput.value = currentValue + 1;
}
</script>