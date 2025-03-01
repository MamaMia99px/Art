-- Create database
CREATE DATABASE IF NOT EXISTS artisell_db;
USE artisell_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User addresses table
CREATE TABLE IF NOT EXISTS user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Remember tokens table for "remember me" functionality
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    product_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Artists table
CREATE TABLE IF NOT EXISTS artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    bio TEXT,
    image VARCHAR(255),
    cover_image VARCHAR(255),
    work_sample VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Artist social links table
CREATE TABLE IF NOT EXISTS artist_social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artist_id INT NOT NULL,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    artist_id INT NOT NULL,
    featured BOOLEAN DEFAULT FALSE,
    in_stock BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (artist_id) REFERENCES artists(id)
);

-- Product images table
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    shipping_address_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    shipping_fee DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (shipping_address_id) REFERENCES user_addresses(id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    reference_number VARCHAR(100),
    account_number VARCHAR(50),
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Order status history table
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Sample data for categories
INSERT INTO categories (name, description, image, product_count) VALUES
('Paintings', 'Beautiful paintings showcasing Cebuano culture and landscapes', 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 42),
('Sculptures', 'Handcrafted sculptures made by local Cebuano artists', 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 28),
('Crafts', 'Traditional crafts representing Cebuano heritage', 'https://images.unsplash.com/photo-1528396518501-b53b655eb9b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 35),
('Food Products', 'Delicious local delicacies from different areas of Cebu', 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 19),
('Jewelry', 'Handcrafted jewelry inspired by Cebuano culture', 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 23),
('Textiles', 'Traditional textiles and fabrics from Cebu', 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 17);

-- Sample data for artists
INSERT INTO artists (name, location, bio, image, cover_image, work_sample, featured) VALUES
('Maria Santos', 'Cebu City', 'Maria Santos is a renowned Cebuano artist specializing in traditional paintings that capture the vibrant culture and landscapes of the region. With over 15 years of experience, her work has been featured in galleries across the Philippines and internationally.', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Maria', 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', TRUE),
('Juan Reyes', 'Carcar', 'Master craftsman creating intricate wood carvings that tell stories of Cebuano heritage and traditions.', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Juan', 'https://images.unsplash.com/photo-1541753866388-0b3c701627d3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1558997519-83ea9252edf8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', TRUE),
('Elena Flores', 'Mactan', 'Contemporary artist blending traditional techniques with modern themes, focusing on marine life and coastal scenes.', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Elena', 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1578926375605-eaf7559b1458?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', TRUE),
('Carlos Mendoza', 'Cebu City', 'Specializing in oil paintings that capture the natural beauty of Cebu landscapes.', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Carlos', 'https://images.unsplash.com/photo-1578301978018-927651d076b6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', FALSE),
('Lorna Bautista', 'Carcar', 'Preserving traditional food recipes and creating delicious local delicacies from Carcar.', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Lorna', 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', FALSE),
('Ana Villanueva', 'Lapu-Lapu', 'Ceramic artist creating functional and decorative pieces inspired by Cebuano culture.', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Ana', 'https://images.unsplash.com/photo-1605721911519-3dfeb3be25e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1605721911519-3dfeb3be25e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', FALSE);

-- Sample data for artist social links
INSERT INTO artist_social_links (artist_id, platform, url) VALUES
(1, 'website', 'https://mariasantos.com'),
(1, 'instagram', 'https://instagram.com/mariasantos'),
(1, 'facebook', 'https://facebook.com/mariasantos'),
(2, 'instagram', 'https://instagram.com/juanreyes'),
(2, 'facebook', 'https://facebook.com/juanreyes'),
(3, 'website', 'https://elenaflores.com'),
(3, 'instagram', 'https://instagram.com/elenaflores');

-- Sample data for products
INSERT INTO products (title, description, price, image, category_id, artist_id, featured, in_stock) VALUES
('Cebu Traditional Painting', 'A beautiful traditional painting showcasing the vibrant culture and landscapes of Cebu. This artwork captures the essence of Cebuano heritage with intricate details and vivid colors.', 2500.00, 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1, 1, TRUE, TRUE),
('Handcrafted Wooden Sculpture', 'Intricately carved wooden sculpture representing Cebuano folklore and traditions. Each piece is handcrafted with attention to detail.', 3800.00, 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 2, 2, TRUE, TRUE),
('Woven Basket Set', 'Traditional woven baskets made from locally sourced materials. Perfect for storage or as decorative pieces.', 1200.00, 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 3, 3, FALSE, TRUE),
('Cebu Landscape Oil Painting', 'Oil painting depicting the beautiful landscapes of Cebu, from mountains to beaches. A stunning piece to add to your collection.', 4500.00, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1, 4, TRUE, TRUE),
('Traditional Food Delicacies Pack', 'A selection of traditional Cebuano delicacies, including dried mangoes, otap, and more. Perfect as a gift or to enjoy yourself.', 850.00, 'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 4, 5, FALSE, TRUE),
('Handmade Ceramic Vase', 'Beautiful ceramic vase handcrafted with traditional techniques. Each piece is unique with subtle variations in glaze and form.', 1800.00, 'https://images.unsplash.com/photo-1605721911519-3dfeb3be25e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 3, 6, FALSE, TRUE),
('Cebu Seascape', 'A stunning painting capturing the beautiful coastal views of Cebu. Perfect for bringing a piece of paradise into your home.', 2800.00, 'https://images.unsplash.com/photo-1578301978162-7aae4d755744?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1, 1, FALSE, TRUE),
('Sinulog Festival Scene', 'Vibrant painting depicting the colorful Sinulog Festival, one of Cebu\'s most famous cultural celebrations.', 3200.00, 'https://images.unsplash.com/photo-1574182245530-967d9b3831af?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1, 1, FALSE, TRUE);

-- Sample data for product images
INSERT INTO product_images (product_id, image_url, is_primary) VALUES
(1, 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', TRUE),
(1, 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', FALSE),
(1, 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', FALSE),
(2, 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', TRUE),
(2, 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', FALSE),
(3, 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', TRUE),
(4, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', TRUE);

-- Sample user for testing
INSERT INTO users (full_name, email, password) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Sample address for testing user
INSERT INTO user_addresses (user_id, full_name, phone, address_line1, address_line2, city, province, postal_code, is_default) VALUES
(1, 'John Doe', '0917-123-4567', '123 Main Street', 'Apartment 4B', 'Cebu City', 'Cebu', '6000', TRUE);
