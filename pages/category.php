<?php
// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch category details if ID is provided
$category = null;
if ($category_id > 0) {
    $query = "SELECT * FROM categories WHERE id = $category_id";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $category = mysqli_fetch_assoc($result);
    }
}

// Fetch all categories for sidebar
$all_categories = [];
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

if (mysqli_num_rows($categories_result) > 0) {
    while ($cat = mysqli_fetch_assoc($categories_result)) {
        $all_categories[] = $cat;
    }
} else {
    // Sample categories if none in database
    $all_categories = [
        ['id' => 1, 'name' => 'Paintings', 'image' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'product_count' => 42],
        ['id' => 2, 'name' => 'Sculptures', 'image' => 'https://images.unsplash.com/photo-1544413164-5f1b295eb435?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'product_count' => 28],
        ['id' => 3, 'name' => 'Crafts', 'image' => 'https://images.unsplash.com/photo-1528396518501-b53b655eb9b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'product_count' => 35],
        ['id' => 4, 'name' => 'Food Products', 'image' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'product_count' => 19],
        ['id' => 5, 'name' => 'Jewelry', 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'product_count' => 23],
        ['id' => 6, 'name' => 'Textiles', 'image' => 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'product_count' => 17]
    ];
}

// Fetch locations for filter
$locations = [];
$locations_query = "SELECT DISTINCT location FROM artists ORDER BY location";
$locations_result = mysqli_query($conn, $locations_query);

if (mysqli_num_rows($locations_result) > 0) {
    while ($location = mysqli_fetch_assoc($locations_result)) {
        $locations[] = $location['location'];
    }
} else {
    // Sample locations if none in database
    $locations = ['Cebu City', 'Carcar', 'Mandaue', 'Lapu-Lapu', 'Mactan', 'Talisay'];
}

// Build query for products
$where_clauses = [];
$params = [];

// Filter by category if specified
if ($category_id > 0) {
    $where_clauses[] = "p.category_id = $category_id";
}

// Filter by search term if provided
if (!empty($search_term)) {
    $where_clauses[] = "(p.title LIKE '%$search_term%' OR a.name LIKE '%$search_term%' OR c.name LIKE '%$search_term%')";
}

// Apply filters from POST request
$selected_categories = [];
$selected_locations = [];
$min_price = 0;
$max_price = 100000; // Default high value
$sort_by = 'featured'; // Default sort

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Category filters
    if (isset($_POST['categories']) && is_array($_POST['categories'])) {
        $selected_categories = $_POST['categories'];
        if (!empty($selected_categories)) {
            $category_ids = implode(',', array_map('intval', $selected_categories));
            $where_clauses[] = "p.category_id IN ($category_ids)";
        }
    }
    
    // Location filters
    if (isset($_POST['locations']) && is_array($_POST['locations'])) {
        $selected_locations = $_POST['locations'];
        if (!empty($selected_locations)) {
            $location_list = implode('","', array_map(function($loc) use ($conn) {
                return mysqli_real_escape_string($conn, $loc);
            }, $selected_locations));
            $where_clauses[] = "a.location IN (\"$location_list\")";
        }
    }
    
    // Price range
    if (isset($_POST['min_price']) && is_numeric($_POST['min_price'])) {
        $min_price = floatval($_POST['min_price']);
        $where_clauses[] = "p.price >= $min_price";
    }
    
    if (isset($_POST['max_price']) && is_numeric($_POST['max_price'])) {
        $max_price = floatval($_POST['max_price']);
        $where_clauses[] = "p.price <= $max_price";
    }
    
    // Sort option
    if (isset($_POST['sort_by'])) {
        $sort_by = $_POST['sort_by'];
    }
}

// Build the complete query
$query = "SELECT p.*, c.name as category_name, a.name as artist_name, a.location as artist_location 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          JOIN artists a ON p.artist_id = a.id";

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

// Add sorting
switch ($sort_by) {
    case 'price-low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price-high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $query .= " ORDER BY p.title ASC";
        break;
    case 'newest':
        $query .= " ORDER BY p.created_at DESC";
        break;
    default: // featured or any other value
        $query .= " ORDER BY p.featured DESC, p.id DESC";
        break;
}

// Execute query
$result = mysqli_query($conn, $query);
$products = [];

if (mysqli_num_rows($result) > 0) {
    while ($product = mysqli_fetch_assoc($result)) {
        $products[] = $product;
    }
} else {
    // Sample products if none match the criteria
    $products = [
        ['id' => 1, 'title' => 'Cebu Traditional Painting', 'price' => 2500, 'artist_name' => 'Maria Santos', 'artist_location' => 'Cebu City', 'category_name' => 'Paintings', 'image' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
        ['id' => 2, 'title' => 'Handcrafted Wooden Sculpture', 'price' => 3800, 'artist_name' => 'Juan Reyes', 'artist_location' => 'Carcar', 'category_name' => 'Sculptures', 'image' => 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
        ['id' => 3, 'title' => 'Woven Basket Set', 'price' => 1200, 'artist_name' => 'Elena Flores', 'artist_location' => 'Mandaue', 'category_name' => 'Crafts', 'image' => 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
        ['id' => 4, 'title' => 'Cebu Landscape Oil Painting', 'price' => 4500, 'artist_name' => 'Carlos Mendoza', 'artist_location' => 'Cebu City', 'category_name' => 'Paintings', 'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
        ['id' => 5, 'title' => 'Traditional Food Delicacies Pack', 'price' => 850, 'artist_name' => 'Lorna Bautista', 'artist_location' => 'Carcar', 'category_name' => 'Food Products', 'image' => 'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'],
        ['id' => 6, 'title' => 'Handmade Ceramic Vase', 'price' => 1800, 'artist_name' => 'Ana Villanueva', 'artist_location' => 'Lapu-Lapu', 'category_name' => 'Crafts', 'image' => 'https://images.unsplash.com/photo-1605721911519-3dfeb3be25e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80']
    ];
}

// Pagination
$products_per_page = 12;
$total_products = count($products);
$total_pages = ceil($total_products / $products_per_page);
$current_page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$offset = ($current_page - 1) * $products_per_page;

// Get products for current page
$current_products = array_slice($products, $offset, $products_per_page);
?>

<div class="container py-5">
    <div class="row">
        <!-- Page Title -->
        <div class="col-12 mb-4">
            <?php if ($category): ?>
                <h1 class="h2"><?php echo $category['name']; ?></h1>
                <?php if (!empty($category['description'])): ?>
                    <p class="text-muted"><?php echo $category['description']; ?></p>
                <?php endif; ?>
            <?php elseif (!empty($search_term)): ?>
                <h1 class="h2">Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h1>
            <?php else: ?>
                <h1 class="h2">All Categories</h1>
                <p class="text-muted">Browse our collection of authentic Cebuano art and crafts</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <!-- Categories Filter -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Categories</h6>
                            <?php foreach ($all_categories as $cat): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo $cat['id']; ?>" id="category-<?php echo $cat['id']; ?>" <?php echo in_array($cat['id'], $selected_categories) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="category-<?php echo $cat['id']; ?>">
                                    <?php echo $cat['name']; ?>
                                    <?php if (isset($cat['product_count'])): ?>
                                    <span class="text-muted small">(<?php echo $cat['product_count']; ?>)</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Location Filter -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Artist Location</h6>
                            <?php foreach ($locations as $location): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="locations[]" value="<?php echo $location; ?>" id="location-<?php echo str_replace(' ', '-', strtolower($location)); ?>" <?php echo in_array($location, $selected_locations) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="location-<?php echo str_replace(' ', '-', strtolower($location)); ?>">
                                    <?php echo $location; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Price Range Filter -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Price Range</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="min-price" class="form-label small">Min Price</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="min-price" name="min_price" value="<?php echo $min_price; ?>" min="0">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="max-price" class="form-label small">Max Price</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="max-price" name="max_price" value="<?php echo $max_price; ?>" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Sort and Count -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="mb-0">
                    <span class="fw-medium"><?php echo $total_products; ?></span> products found
                </p>
                
                <form method="POST" action="" class="d-flex align-items-center">
                    <!-- Hidden inputs to preserve filters when sorting changes -->
                    <?php foreach ($selected_categories as $cat_id): ?>
                    <input type="hidden" name="categories[]" value="<?php echo $cat_id; ?>">
                    <?php endforeach; ?>
                    
                    <?php foreach ($selected_locations as $loc): ?>
                    <input type="hidden" name="locations[]" value="<?php echo $loc; ?>">
                    <?php endforeach; ?>
                    
                    <input type="hidden" name="min_price" value="<?php echo $min_price; ?>">
                    <input type="hidden" name="max_price" value="<?php echo $max_price; ?>">
                    
                    <label for="sort-by" class="me-2">Sort by:</label>
                    <select class="form-select form-select-sm" id="sort-by" name="sort_by" onchange="this.form.submit()">
                        <option value="featured" <?php echo $sort_by === 'featured' ? 'selected' : ''; ?>>Featured</option>
                        <option value="price-low" <?php echo $sort_by === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price-high" <?php echo $sort_by === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name</option>
                        <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    </select>
                </form>
            </div>
            
            <!-- Products -->
            <?php if (!empty($current_products)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <?php foreach ($current_products as $product): ?>
                <div class="col">
                    <div class="card h-100 product-card">
                        <div style="height: 200px; overflow: hidden;">
                            <img src="<?php echo $product['image']; ?>" class="card-img-top h-100 object-fit-cover" alt="<?php echo $product['title']; ?>">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo $product['title']; ?></h5>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-dark"><?php echo $product['category_name']; ?></span>
                                <small class="text-muted"><?php echo $product['artist_location']; ?></small>
                            </div>
                            <p class="card-text text-muted small">by <?php echo $product['artist_name']; ?></p>
                            <p class="card-text fw-bold text-primary mt-auto">₱<?php echo number_format($product['price'], 2); ?></p>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-grid">
                                <a href="index.php?page=product&id=<?php echo $product['id']; ?>" class="btn btn-primary"><i class="fas fa-shopping-cart me-2"></i>Add to Cart</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Product pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=category<?php echo $category_id ? '&id=' . $category_id : ''; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>&page_num=<?php echo $current_page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=category<?php echo $category_id ? '&id=' . $category_id : ''; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>&page_num=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=category<?php echo $category_id ? '&id=' . $category_id : ''; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>&page_num=<?php echo $current_page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-search fa-3x mb-3"></i>
                <h4>No products found</h4>
                <p>Try adjusting your filters or search criteria.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// JavaScript for mobile filter toggle
document.addEventListener('DOMContentLoaded', function() {
    const filterToggleBtn = document.getElementById('filter-toggle');
    const filterSidebar = document.getElementById('filter-sidebar');
    
    if (filterToggleBtn && filterSidebar) {
        filterToggleBtn.addEventListener('click', function() {
            filterSidebar.classList.toggle('d-none');
            filterSidebar.classList.toggle('d-block');
        });
    }
});
</script>