<!-- Hero Section -->
<section class="hero-section position-relative" style="height: 500px; background: url('https://images.unsplash.com/photo-1577722422778-eaab0909b1ed?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') no-repeat center center; background-size: cover;">
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-40"></div>
    <div class="container h-100 position-relative">
        <div class="row h-100 align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold text-white mb-4">Discover Authentic Cebu Art & Crafts</h1>
                <p class="lead text-white mb-5">Support local artists and bring home a piece of Cebu's rich cultural heritage</p>
                <a href="index.php?page=category" class="btn btn-primary btn-lg px-4 py-2">Explore Products</a>
            </div>
        </div>
    </div>
    <div class="position-absolute bottom-0 start-0 w-100 h-4 bg-gradient-to-t from-white to-transparent"></div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Browse Categories</h2>
            <div>
                <button class="btn btn-outline-secondary rounded-circle me-2" id="category-prev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-outline-secondary rounded-circle" id="category-next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        
        <div class="category-slider position-relative overflow-hidden">
            <div class="category-wrapper d-flex" style="transition: transform 0.3s ease;">
                <?php
                // Fetch categories from database
                $query = "SELECT * FROM categories ORDER BY name";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($category = mysqli_fetch_assoc($result)) {
                        echo '<div class="category-card card me-3" style="min-width: 200px; cursor: pointer;" onclick="window.location=\'index.php?page=category&id=' . $category['id'] . '\'">';
                        echo '<div class="category-img-container" style="height: 150px; overflow: hidden;">';
                        echo '<img src="' . $category['image'] . '" class="card-img-top h-100 object-fit-cover" alt="' . $category['name'] . '">';
                        echo '</div>';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . $category['name'] . '</h5>';
                        echo '<p class="card-text text-muted small">' . $category['product_count'] . ' items</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    // Sample categories if none in database
                    $sample_categories = [
                        ['name' => 'Paintings', 'image' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'count' => 42],
                        ['name' => 'Sculptures', 'image' => 'https://images.unsplash.com/photo-1544413164-5f1b295eb435?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'count' => 28],
                        ['name' => 'Crafts', 'image' => 'https://images.unsplash.com/photo-1528396518501-b53b655eb9b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'count' => 35],
                        ['name' => 'Food Products', 'image' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'count' => 19],
                        ['name' => 'Jewelry', 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'count' => 23],
                        ['name' => 'Textiles', 'image' => 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'count' => 17]
                    ];
                    
                    foreach ($sample_categories as $category) {
                        echo '<div class="category-card card me-3" style="min-width: 200px; cursor: pointer;" onclick="window.location=\'index.php?page=category\'">';
                        echo '<div class="category-img-container" style="height: 150px; overflow: hidden;">';
                        echo '<img src="' . $category['image'] . '" class="card-img-top h-100 object-fit-cover" alt="' . $category['name'] . '">';
                        echo '</div>';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . $category['name'] . '</h5>';
                        echo '<p class="card-text text-muted small">' . $category['count'] . ' items</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Featured Products</h2>
            <p class="text-muted">Discover unique handcrafted items from Cebu's talented artists</p>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="product-search" placeholder="Search products or artists...">
                </div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-2"></i> Filter
                    </button>
                    <div class="dropdown-menu p-3" style="width: 250px;">
                        <h6 class="mb-3">Categories</h6>
                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="category-painting">
                            <label class="form-check-label" for="category-painting">Painting</label>
                        </div>
                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="category-sculpture">
                            <label class="form-check-label" for="category-sculpture">Sculpture</label>
                        </div>
                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="category-crafts">
                            <label class="form-check-label" for="category-crafts">Crafts</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="category-food">
                            <label class="form-check-label" for="category-food">Food Products</label>
                        </div>
                        
                        <h6 class="mb-3">Location</h6>
                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="location-cebu">
                            <label class="form-check-label" for="location-cebu">Cebu City</label>
                        </div>
                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="location-carcar">
                            <label class="form-check-label" for="location-carcar">Carcar</label>
                        </div>
                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="location-mandaue">
                            <label class="form-check-label" for="location-mandaue">Mandaue</label>
                        </div>
                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="location-lapu">
                            <label class="form-check-label" for="location-lapu">Lapu-Lapu</label>
                        </div>
                        
                        <button class="btn btn-primary btn-sm w-100 mt-3">Apply Filters</button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="sort-products">
                    <option value="featured">Featured</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="name">Name</option>
                </select>
            </div>
        </div>
        
        <div class="row">
            <?php
            // Fetch products from database
            $query = "SELECT p.*, a.name as artist_name FROM products p 
                      JOIN artists a ON p.artist_id = a.id 
                      WHERE p.featured = 1 
                      ORDER BY p.id DESC LIMIT 8";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                while ($product = mysqli_fetch_assoc($result)) {
                    echo '<div class="col-sm-6 col-md-4 col-lg-3 mb-4">';
                    echo '<div class="card h-100 product-card">';
                    echo '<div style="height: 200px; overflow: hidden;">';
                    echo '<img src="' . $product['image'] . '" class="card-img-top h-100 object-fit-cover" alt="' . $product['title'] . '">';
                    echo '</div>';
                    echo '<div class="card-body d-flex flex-column">';
                    echo '<h5 class="card-title">' . $product['title'] . '</h5>';
                    echo '<p class="card-text text-muted small">by ' . $product['artist_name'] . '</p>';
                    echo '<p class="card-text fw-bold text-primary mt-auto">₱' . number_format($product['price'], 2) . '</p>';
                    echo '</div>';
                    echo '<div class="card-footer bg-white border-top-0">';
                    echo '<div class="d-grid">';
                    echo '<a href="index.php?page=product&id=' . $product['id'] . '" class="btn btn-primary"><i class="fas fa-shopping-cart me-2"></i>Add to Cart</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                // Sample products if none in database
                $sample_products = [
                    ['id' => 1, 'title' => 'Cebu Traditional Painting', 'price' => 2500, 'artist' => 'Maria Santos', 'image' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
                    ['id' => 2, 'title' => 'Handcrafted Wooden Sculpture', 'price' => 3800, 'artist' => 'Juan Reyes', 'image' => 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
                    ['id' => 3, 'title' => 'Woven Basket Set', 'price' => 1200, 'artist' => 'Elena Flores', 'image' => 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
                    ['id' => 4, 'title' => 'Cebu Landscape Oil Painting', 'price' => 4500, 'artist' => 'Carlos Mendoza', 'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
                    ['id' => 5, 'title' => 'Traditional Food Delicacies Pack', 'price' => 850, 'artist' => 'Lorna Bautista', 'image' => 'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
                    ['id' => 6, 'title' => 'Handmade Ceramic Vase', 'price' => 1800, 'artist' => 'Ana Villanueva', 'image' => 'https://images.unsplash.com/photo-1605721911519-3dfeb3be25e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
                    ['id' => 7, 'title' => 'Cebu Seascape', 'price' => 2800, 'artist' => 'Maria Santos', 'image' => 'https://images.unsplash.com/photo-1578301978162-7aae4d755744?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
                    ['id' => 8, 'title' => 'Sinulog Festival Scene', 'price' => 3200, 'artist' => 'Maria Santos', 'image' => 'https://images.unsplash.com/photo-1574182245530-967d9b3831af?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80']
                ];
                
                foreach ($sample_products as $product) {
                    echo '<div class="col-sm-6 col-md-4 col-lg-3 mb-4">';
                    echo '<div class="card h-100 product-card">';
                    echo '<div style="height: 200px; overflow: hidden;">';
                    echo '<img src="' . $product['image'] . '" class="card-img-top h-100 object-fit-cover" alt="' . $product['title'] . '">';
                    echo '</div>';
                    echo '<div class="card-body d-flex flex-column">';
                    echo '<h5 class="card-title">' . $product['title'] . '</h5>';
                    echo '<p class="card-text text-muted small">by ' . $product['artist'] . '</p>';
                    echo '<p class="card-text fw-bold text-primary mt-auto">₱' . number_format($product['price'], 2) . '</p>';
                    echo '</div>';
                    echo '<div class="card-footer bg-white border-top-0">';
                    echo '<div class="d-grid">';
                    echo '<a href="index.php?page=product&id=' . $product['id'] . '" class="btn btn-primary"><i class="fas fa-shopping-cart me-2"></i>Add to Cart</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- Artist Spotlight Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Featured Artists</h2>
            <p class="text-muted">Discover the talented local artists behind our unique Cebuano creations</p>
        </div>
        
        <div class="row">
            <?php
            // Fetch artists from database
            $query = "SELECT * FROM artists WHERE featured = 1 LIMIT 3";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                while ($artist = mysqli_fetch_assoc($result)) {
                    echo '<div class="col-md-4 mb-4">';
                    echo '<div class="card h-100">';
                    echo '<div class="card-header bg-white p-3">';
                    echo '<div class="d-flex align-items-center">';
                    echo '<div class="flex-shrink-0">';
                    echo '<img src="' . $artist['image'] . '" class="rounded-circle" width="64" height="64" alt="' . $artist['name'] . '">';
                    echo '</div>';
                    echo '<div class="ms-3">';
                    echo '<h5 class="card-title mb-0">' . $artist['name'] . '</h5>';
                    echo '<p class="card-text text-muted small">' . $artist['location'] . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div style="height: 200px; overflow: hidden;">';
                    echo '<img src="' . $artist['work_sample'] . '" class="card-img-top h-100 object-fit-cover" alt="Artwork by ' . $artist['name'] . '">';
                    echo '</div>';
                    echo '<div class="card-body">';
                    echo '<p class="card-text">' . $artist['bio'] . '</p>';
                    echo '</div>';
                    echo '<div class="card-footer bg-white border-top-0">';
                    echo '<a href="index.php?page=artist&id=' . $artist['id'] . '" class="btn btn-outline-primary w-100"><i class="fas fa-external-link-alt me-2"></i>View Profile</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                // Sample artists if none in database
                $sample_artists = [
                    ['id' => 1, 'name' => 'Maria Santos', 'location' => 'Cebu City', 'bio' => 'Specializing in traditional Cebuano paintings that capture the vibrant culture and landscapes of the region.', 'image' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Maria', 'work_sample' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
                    ['id' => 2, 'name' => 'Juan Reyes', 'location' => 'Car Car', 'bio' => 'Master craftsman creating intricate wood carvings that tell stories of Cebuano heritage and traditions.', 'image' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Juan', 'work_sample' => 'https://images.unsplash.com/photo-1558997519-83ea9252edf8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
                    ['id' => 3, 'name' => 'Elena Flores', 'location' => 'Mactan', 'bio' => 'Contemporary artist blending traditional techniques with modern themes, focusing on marine life and coastal scenes.', 'image' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Elena', 'work_sample' => 'https://images.unsplash.com/photo-1578926375605-eaf7559b1458?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80']
                ];
                
                foreach ($sample_artists as $artist) {
                    echo '<div class="col-md-4 mb-4">';
                    echo '<div class="card h-100">';
                    echo '<div class="card-header bg-white p-3">';
                    echo '<div class="d-flex align-items-center">';
                    echo '<div class="flex-shrink-0">';
                    echo '<img src="' . $artist['image'] . '" class="rounded-circle" width="64" height="64" alt="' . $artist['name'] . '">';
                    echo '</div>';
                    echo '<div class="ms-3">';
                    echo '<h5 class="card-title mb-0">' . $artist['name'] . '</h5>';
                    echo '<p class="card-text text-muted small">' . $artist['location'] . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div style="height: 200px; overflow: hidden;">';
                    echo '<img src="' . $artist['work_sample'] . '" class="card-img-top h-100 object-fit-cover" alt="Artwork by ' . $artist['name'] . '">';
                    echo '</div>';
                    echo '<div class="card-body">';
                    echo '<p class="card-text">' . $artist['bio'] . '</p>';
                    echo '</div>';
                    echo '<div class="card-footer bg-white border-top-0">';
                    echo '<a href="index.php?page=artist&id=' . $artist['id'] . '" class="btn btn-outline-primary w-100"><i class="fas fa-external-link-alt me-2"></i>View Profile</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</section>

<script>
// Category slider functionality
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.querySelector('.category-wrapper');
    const cards = document.querySelectorAll('.category-card');
    const cardWidth = 220; // card width + margin
    const visibleCards = Math.floor(wrapper.offsetWidth / cardWidth);
    let position = 0;
    
    document.getElementById('category-prev').addEventListener('click', function() {
        if (position > 0) {
            position--;
            updateSliderPosition();
        }
    });
    
    document.getElementById('category-next').addEventListener('click', function() {
        if (position < cards.length - visibleCards) {
            position++;
            updateSliderPosition();
        }
    });
    
    function updateSliderPosition() {
        wrapper.style.transform = `translateX(-${position * cardWidth}px)`;
    }
});
</script>