<?php
// Get artist ID from URL
$artist_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch artist details from database
$artist = null;

if ($artist_id > 0) {
    $query = "SELECT * FROM artists WHERE id = $artist_id";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $artist = mysqli_fetch_assoc($result);
        
        // Get artist's social links
        $links_query = "SELECT * FROM artist_social_links WHERE artist_id = $artist_id";
        $links_result = mysqli_query($conn, $links_query);
        
        $artist['social_links'] = [];
        if (mysqli_num_rows($links_result) > 0) {
            while ($link = mysqli_fetch_assoc($links_result)) {
                $artist['social_links'][$link['platform']] = $link['url'];
            }
        }
        
        // Get artist's products
        $products_query = "SELECT id, title, price, image FROM products WHERE artist_id = $artist_id LIMIT 8";
        $products_result = mysqli_query($conn, $products_query);
        
        $artist['products'] = [];
        if (mysqli_num_rows($products_result) > 0) {
            while ($product = mysqli_fetch_assoc($products_result)) {
                $product['artist'] = $artist['name'];
                $artist['products'][] = $product;
            }
        }
    }
}

// If artist not found in database, use sample data
if (!$artist) {
    $artist = [
        'id' => $artist_id ?: 1,
        'name' => 'Maria Santos',
        'location' => 'Cebu City',
        'bio' => 'Maria Santos is a renowned Cebuano artist specializing in traditional paintings that capture the vibrant culture and landscapes of the region. With over 15 years of experience, her work has been featured in galleries across the Philippines and internationally. Maria draws inspiration from the rich heritage of Cebu, incorporating elements of local folklore, natural scenery, and daily life into her colorful and expressive pieces.',
        'image' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Maria',
        'cover_image' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'social_links' => [
            'website' => 'https://mariasantos.com',
            'instagram' => 'https://instagram.com/mariasantos',
            'facebook' => 'https://facebook.com/mariasantos'
        ],
        'products' => [
            ['id' => 1, 'title' => 'Cebu Traditional Painting', 'price' => 2500, 'artist' => 'Maria Santos', 'image' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
            ['id' => 4, 'title' => 'Cebu Landscape Oil Painting', 'price' => 4500, 'artist' => 'Maria Santos', 'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
            ['id' => 7, 'title' => 'Sinulog Festival Scene', 'price' => 3200, 'artist' => 'Maria Santos', 'image' => 'https://images.unsplash.com/photo-1574182245530-967d9b3831af?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
            ['id' => 8, 'title' => 'Cebu Seascape', 'price' => 2800, 'artist' => 'Maria Santos', 'image' => 'https://images.unsplash.com/photo-1578301978162-7aae4d755744?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80']
        ]
    ];
}
?>

<!-- Cover Image -->
<div class="position-relative" style="height: 300px; background: url('<?php echo $artist['cover_image']; ?>') no-repeat center center; background-size: cover;">
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10"></div>
</div>

<div class="container">
    <!-- Artist Info Card -->
    <div class="card shadow-sm mt-n5 position-relative z-index-1 mb-5">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex flex-column flex-md-row">
                        <!-- Artist Image -->
                        <div class="flex-shrink-0 me-md-4 mb-3 mb-md-0">
                            <div class="rounded-circle border-4 border-white shadow-sm overflow-hidden" style="width: 120px; height: 120px; margin-top: -60px;">
                                <img src="<?php echo $artist['image']; ?>" class="img-fluid w-100 h-100 object-fit-cover" alt="<?php echo $artist['name']; ?>">
                            </div>
                        </div>
                        
                        <!-- Artist Details -->
                        <div>
                            <h1 class="h2 mb-1"><?php echo $artist['name']; ?></h1>
                            <p class="text-muted mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i><?php echo $artist['location']; ?>
                            </p>
                            
                            <!-- Social Links -->
                            <div class="d-flex flex-wrap gap-2">
                                <?php if (isset($artist['social_links']['website'])): ?>
                                <a href="<?php echo $artist['social_links']['website']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-globe me-2"></i>Website
                                </a>
                                <?php endif; ?>
                                
                                <?php if (isset($artist['social_links']['instagram'])): ?>
                                <a href="<?php echo $artist['social_links']['instagram']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="fab fa-instagram me-2"></i>Instagram
                                </a>
                                <?php endif; ?>
                                
                                <?php if (isset($artist['social_links']['facebook'])): ?>
                                <a href="<?php echo $artist['social_links']['facebook']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="fab fa-facebook-f me-2"></i>Facebook
                                </a>
                                <?php endif; ?>
                                
                                <button class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-envelope me-2"></i>Contact
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Tabs -->
            <ul class="nav nav-tabs" id="artistTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab">About</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab">Products</button>
                </li>
            </ul>
            
            <div class="tab-content pt-4" id="artistTabsContent">
                <!-- About Tab -->
                <div class="tab-pane fade show active" id="about" role="tabpanel">
                    <h3 class="h4 mb-3">About <?php echo $artist['name']; ?></h3>
                    <p class="text-muted"><?php echo $artist['bio']; ?></p>
                </div>
                
                <!-- Products Tab -->
                <div class="tab-pane fade" id="products" role="tabpanel">
                    <h3 class="h4 mb-4">Products by <?php echo $artist['name']; ?></h3>
                    
                    <div class="row">
                        <?php foreach ($artist['products'] as $product): ?>
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                            <div class="card h-100 product-card">
                                <div style="height: 200px; overflow: hidden;">
                                    <img src="<?php echo $product['image']; ?>" class="card-img-top h-100 object-fit-cover" alt="<?php echo $product['title']; ?>">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo $product['title']; ?></h5>
                                    <p class="card-text text-muted small">by <?php echo $product['artist']; ?></p>
                                    <p class="card-text fw-bold text-primary mt-auto">₱<?php echo number_format($product['price'], 2); ?></p>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-grid">
                                        <a href="index.php?page=product&id=<?