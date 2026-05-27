<?php
// Establish connection to MySQL
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ecommerce";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch all products (using a JOIN to get the text name of the category)
function get_products() {
    global $conn;
    $sql = "SELECT p.product_id AS id, p.name, p.price, c.category_name AS category, p.badge, p.image_url AS image, p.description, p.stock_quantity 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id";
    $result = mysqli_query($conn, $sql);
    $products = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    return $products;
}

// Add a product (used by Admin)
function add_product($name, $price, $category_name, $badge, $image) {
    global $conn;
    
    // Sanitize values
    $name = htmlspecialchars(trim($name));
    $category_name = htmlspecialchars(trim($category_name));
    $badge = htmlspecialchars(trim($badge));
    $image = trim($image) ?: 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=400&q=80';
    $price = floatval($price);

    // Get category ID or insert it if it doesn't exist
    $stmt = mysqli_prepare($conn, "SELECT category_id FROM categories WHERE category_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $category_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row) {
        $category_id = $row['category_id'];
    } else {
        $stmt_ins = mysqli_prepare($conn, "INSERT INTO categories (category_name) VALUES (?)");
        mysqli_stmt_bind_param($stmt_ins, "s", $category_name);
        mysqli_stmt_execute($stmt_ins);
        $category_id = mysqli_insert_id($conn);
    }
    
    $stmt_prod = mysqli_prepare($conn, "INSERT INTO products (name, price, category_id, badge, image_url, stock_quantity, description) VALUES (?, ?, ?, ?, ?, 50, 'Standard product description.')");
    mysqli_stmt_bind_param($stmt_prod, "sdiss", $name, $price, $category_id, $badge, $image);
    mysqli_stmt_execute($stmt_prod);
    return mysqli_insert_id($conn);
}

// Delete a product (used by Admin)
function delete_product($id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}
?>
