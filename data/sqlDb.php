<?php

// Connect to MySQL server
$conn = mysqli_connect("localhost", "root", "");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS ecommerce";

if (!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

// Select database
mysqli_select_db($conn, "ecommerce");



//User Table

$usersTable = "
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
";

mysqli_query($conn, $usersTable);



//Categories Table

$categoriesTable = "
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT
)
";

mysqli_query($conn, $categoriesTable);

//Products Table

$productsTable = "
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL,
    image_url VARCHAR(255),
    badge VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id)
    REFERENCES categories(category_id)
)
";

mysqli_query($conn, $productsTable);



//Cart Table

$cartTable = "
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
)
";

mysqli_query($conn, $cartTable);


//Cart Items Table

$cartItemsTable = "
CREATE TABLE IF NOT EXISTS cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT,
    product_id INT,
    quantity INT NOT NULL,

    FOREIGN KEY (cart_id)
    REFERENCES cart(cart_id),

    FOREIGN KEY (product_id)
    REFERENCES products(product_id)
)
";

mysqli_query($conn, $cartItemsTable);


//Orders Table

$ordersTable = "
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    order_status VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
)
";

mysqli_query($conn, $ordersTable);


//Order Items Table

$orderItemsTable = "
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (order_id)
    REFERENCES orders(order_id),

    FOREIGN KEY (product_id)
    REFERENCES products(product_id)
)
";

mysqli_query($conn, $orderItemsTable);


//Payment Table

$paymentsTable = "
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(50) NOT NULL,
    paid_at DATETIME,

    FOREIGN KEY (order_id)
    REFERENCES orders(order_id)
)
";

mysqli_query($conn, $paymentsTable);



//Reviews Table

$reviewsTable = "
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    rating INT NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(user_id),

    FOREIGN KEY (product_id)
    REFERENCES products(product_id)
)
";

mysqli_query($conn, $reviewsTable);



// Check if categories are already populated
$catCheck = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories");
$catCount = mysqli_fetch_assoc($catCheck)['count'];
if ($catCount == 0) {
    // Seed Categories
    mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('Indoor'), ('Outdoor'), ('Succulents'), ('Tropical')");
    
    // Seed Products
    $productsSeed = [
        ['Monstera Deliciosa', 24.00, 1, 'Best Seller', 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?w=400&q=80'],
        ['Snake Plant', 18.00, 1, '', 'https://images.unsplash.com/photo-1512428813834-c702c7702b78?w=400&q=80'],
        ['Fiddle Leaf Fig', 22.00, 1, 'New', 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=400&q=80'],
        ['Aloe Vera', 14.00, 3, '', 'https://images.unsplash.com/photo-1596547609652-9cf5d8d76921?w=400&q=80'],
        ['Bird of Paradise', 35.00, 4, 'Popular', 'https://images.unsplash.com/photo-1603436326446-74e2d65f3168?w=400&q=80'],
        ['Pothos', 12.00, 1, '', 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=400&q=80'],
        ['Cactus Mix', 10.00, 3, 'Sale', 'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=400&q=80'],
        ['Peace Lily', 20.00, 1, '', 'https://images.unsplash.com/photo-1591958911259-bee2173bdccc?w=400&q=80'],
        ['Bamboo Palm', 28.00, 2, '', 'https://images.unsplash.com/photo-1532920161727-344adb090f7f?w=400&q=80'],
        ['Lavender', 16.00, 2, 'New', 'https://images.unsplash.com/photo-1528183429752-a97d0bf99b5a?w=400&q=80'],
        ['Rubber Plant', 26.00, 4, '', 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=400&q=80'],
        ['Echeveria Succulent', 9.00, 3, 'Sale', 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?w=400&q=80']
    ];
    
    foreach ($productsSeed as $p) {
        $name = mysqli_real_escape_string($conn, $p[0]);
        $price = floatval($p[1]);
        $catId = intval($p[2]);
        $badge = mysqli_real_escape_string($conn, $p[3]);
        $img = mysqli_real_escape_string($conn, $p[4]);
        
        $sqlProd = "INSERT INTO products (name, price, category_id, badge, image_url, stock_quantity, description) 
                    VALUES ('$name', $price, $catId, '$badge', '$img', 50, 'A beautiful addition to any plant lover\'s home.')";
        mysqli_query($conn, $sqlProd);
    }
}

// Check if users are populated
$userCheck = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$userCount = mysqli_fetch_assoc($userCheck)['count'];
if ($userCount == 0) {
    // Seed default admin user (password: admin123)
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, email, password_hash, role) VALUES ('Admin User', 'admin@plantea.com', '$admin_pass', 'admin')");
}

echo "All tables created and default catalog seeded successfully!";

mysqli_close($conn);
?>