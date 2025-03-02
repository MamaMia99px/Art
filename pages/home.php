<?php
// Get featured categories
$categories = getCategories(6);

// Get featured products
$featured_products = getFeaturedProducts(8);

// Get featured artists
$featured_artists = getFeaturedArtists(3);
?>

<!-- Hero Section -->
<section class="position-relative">
    <div class="hero-section" style="height: 500px;">
        <img src="https://images.unsplash.com/photo-1577722422778-eaab0909b1ed?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Cebu Art and Crafts" class="w-100 h-100 object-fit-cover">
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="text-white">
                            <h1 class="display-4 fw-bold mb-3">Discover Authentic Cebu Art & Crafts</h1>
                            <p class="lead mb-4">Support local artists and bring home a piece of Cebu's rich cultural heritage</p>
                            <a href="index.php?page=category" class="btn btn-primary btn-lg px-4 py-2">Explore Products</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Browse Categories</h2>
            <div>
                <button id="category-prev" class="btn btn-outline-primary rounded-circle me-2">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button id="category-next" class="btn btn-outline-primary rounded-circle">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        
        <div class="position-relative overflow-hidden">
            <div class="category-wrapper d-flex transition-all" style="transform: translateX(0)">
                <?php foreach ($categories as $category): ?>
                <div class="category-card me-4" style="min-width: 200px;">
                    <a href="index.php?page=category&id=<?php echo $category['id']; ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100">
                            <div style="height: 150px; overflow: hidden;">
                                <img src="<?php echo $category['image']; ?>" class="card-img-top h-100 object-fit-cover" alt="<?php echo $category['name']; ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $category['name']; ?></h5>
                                <p class="card-text text-muted small"><?php echo $category['product_count'] ?? 0; ?> items</p>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Featured Products</h2>
            <p class="text-muted">Discover unique handcrafted items from Cebu's talented artists</p>
        </div>
        
        <div class="row">
            <?php foreach ($featured_products as $product): ?>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card product-card h-100 border-0 shadow-sm">
                    <div style="height: 200px; overflow: hidden;">
                        <img src="<?php echo $product['image']; ?>" class="card-img-top h-100 object-fit-cover" alt="<?php echo $product['title']; ?>">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $product['title']; ?></h5>
                        <p class="card-text text-muted small">by <?php echo $product['artist_name']; ?></p>
                        <p class="card-text fw-bold text-primary"><?php echo formatPrice($product['price']); ?></p>
                    </div>
                    <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
                        <a href="index.php?page=product&id=<?php echo $product['id']; ?>" class="btn btn-outline-primary">View Details</a>
                        <form method="POST" action="index.php?page=cart">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="index.php?page=category" class="btn btn-outline-primary px-4">View All Products</a>
        </div>
    </div>
</section>

<!-- Featured Artists Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Featured Artists</h2>
            <p class="text-muted">Discover the talented local artists behind our unique Cebuano creations</p>
        </div>
        
        <div class="row">
            <?php foreach ($featured_artists as $artist): ?>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="d-flex align-items-center p-3">
                        <div class="rounded-circle overflow-hidden me-3" style="width: 60px; height: 60px;">
                            <img src="<?php echo $artist['image']; ?>" class="img-fluid h-100 w-100 object-fit-cover" alt="<?php echo $artist['name']; ?>">
                        </div>
                        <div>
                            <h5 class="card-title mb-0"><?php echo $artist['name']; ?></h5>
                            <p class="card-text text-muted small"><?php echo $artist['location']; ?></p>
                        </div>
                    </div>
                    <div style="height: 200px; overflow: hidden;">
                        <img src="<?php echo $artist['work_sample']; ?>" class="img-fluid w-100 h-100 object-fit-cover" alt="Artwork by <?php echo $artist['name']; ?>">
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo substr($artist['bio'], 0, 150); ?>...</p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="index.php?page=artist&id=<?php echo $artist['id']; ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-external-link-alt me-2"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="fw-bold">Ready to showcase your Cebuano art?</h2>
                <p class="lead mb-0">Join our community of artists and reach customers who appreciate authentic local craftsmanship.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#" class="btn btn-light btn-lg px-4">Become a Seller</a>
            </div>
        </div>
    </div>
</section>