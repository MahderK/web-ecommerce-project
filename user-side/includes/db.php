<?php
// Database helper using a JSON file to persist products.
// This allows the admin dashboard to add/delete products without requiring MySQL setup.

define('PRODUCTS_JSON_FILE', __DIR__ . '/products.json');

function get_products() {
    if (!file_exists(PRODUCTS_JSON_FILE)) {
        // Initialize with the default 12 plants if the file doesn't exist yet
        $default_products = [
            ['id' => 1, 'name' => 'Monstera Deliciosa',  'price' => 24.00, 'category' => 'Indoor',    'badge' => 'Best Seller', 'image' => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?w=400&q=80'],
            ['id' => 2, 'name' => 'Snake Plant',          'price' => 18.00, 'category' => 'Indoor',    'badge' => '',            'image' => 'https://images.unsplash.com/photo-1512428813834-c702c7702b78?w=400&q=80'],
            ['id' => 3, 'name' => 'Fiddle Leaf Fig',      'price' => 22.00, 'category' => 'Indoor',    'badge' => 'New',         'image' => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=400&q=80'],
            ['id' => 4, 'name' => 'Aloe Vera',            'price' => 14.00, 'category' => 'Succulents','badge' => '',            'image' => 'https://images.unsplash.com/photo-1616677102255-f6f5d4e7749d?w=400&q=80'],
            ['id' => 5, 'name' => 'Bird of Paradise',     'price' => 35.00, 'category' => 'Tropical',  'badge' => 'Popular',     'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80'],
            ['id' => 6, 'name' => 'Pothos',               'price' => 12.00, 'category' => 'Indoor',    'badge' => '',            'image' => 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=400&q=80'],
            ['id' => 7, 'name' => 'Cactus Mix',           'price' => 10.00, 'category' => 'Succulents','badge' => 'Sale',        'image' => 'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=400&q=80'],
            ['id' => 8, 'name' => 'Peace Lily',           'price' => 20.00, 'category' => 'Indoor',    'badge' => '',            'image' => 'https://images.unsplash.com/photo-1591958911259-bee2173bdccc?w=400&q=80'],
            ['id' => 9, 'name' => 'Bamboo Palm',          'price' => 28.00, 'category' => 'Outdoor',   'badge' => '',            'image' => 'https://images.unsplash.com/photo-1584467735871-8e85353a8413?w=400&q=80'],
            ['id' => 10, 'name' => 'Lavender',             'price' => 16.00, 'category' => 'Outdoor',   'badge' => 'New',         'image' => 'https://images.unsplash.com/photo-1444930694458-01babf71870c?w=400&q=80'],
            ['id' => 11, 'name' => 'Rubber Plant',         'price' => 26.00, 'category' => 'Tropical',  'badge' => '',            'image' => 'https://images.unsplash.com/photo-1620803366004-119b57f54cd6?w=400&q=80'],
            ['id' => 12, 'name' => 'Echeveria Succulent',  'price' => 9.00,  'category' => 'Succulents','badge' => 'Sale',        'image' => 'https://images.unsplash.com/photo-1555173274-ae64e5a4f51d?w=400&q=80']
        ];
        save_products($default_products);
        return $default_products;
    }
    $content = file_get_contents(PRODUCTS_JSON_FILE);
    return json_decode($content, true) ?: [];
}

function save_products($products) {
    file_put_contents(PRODUCTS_JSON_FILE, json_encode($products, JSON_PRETTY_PRINT));
}

function add_product($name, $price, $category, $badge, $image) {
    $products = get_products();
    $max_id = 0;
    foreach ($products as $p) {
        if (isset($p['id']) && $p['id'] > $max_id) {
            $max_id = $p['id'];
        }
    }
    $new_id = $max_id + 1;
    $new_product = [
        'id' => $new_id,
        'name' => htmlspecialchars(trim($name)),
        'price' => floatval($price),
        'category' => htmlspecialchars(trim($category)),
        'badge' => htmlspecialchars(trim($badge)),
        'image' => trim($image) ?: 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=400&q=80'
    ];
    $products[] = $new_product;
    save_products($products);
    return $new_id;
}

function delete_product($id) {
    $products = get_products();
    $filtered = [];
    $found = false;
    foreach ($products as $p) {
        if ($p['id'] == $id) {
            $found = true;
            continue;
        }
        $filtered[] = $p;
    }
    if ($found) {
        save_products($filtered);
    }
    return $found;
}
