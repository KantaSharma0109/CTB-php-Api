<?php
// Include database connection
include('conn.php');

// Function to get user products
function getUserProduct($data) {
    global $mysqli;
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;

    // Query for products
    $query = "SELECT p.*, c.`name` AS c_name, 
                     (SELECT `path` FROM `images` WHERE `images`.`product_id` = p.`id` LIMIT 1 OFFSET 0) AS image_path 
              FROM `products` p 
              INNER JOIN `product_categories` c ON c.`id` = p.`category_id` 
              WHERE p.`status` = 1 
              ORDER BY p.`created_at` DESC 
              LIMIT 20 OFFSET $offset";

    $result = $mysqli->query($query);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return ["error" => "Query failed: " . $mysqli->error];
    }
}

// Function to get category products
function getCategoryProduct($data) {
    global $mysqli;

    // Query for slider data
    $product_slider = "SELECT * FROM `slider` 
                       WHERE `status` = 1 AND `show_category` = 'all' AND `linked_category` = 'product' 
                       ORDER BY `date` DESC";

    // Query for category-specific products
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;
    $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    $category = $mysqli->real_escape_string($data['category']);

    $query = "SELECT p.*, c.`name` AS c_name, 
                     (SELECT `path` FROM `images` WHERE `images`.`product_id` = p.`id` LIMIT 1 OFFSET 0) AS image_path, 
                     (SELECT COUNT(*) FROM `cart` WHERE `cart`.`product_id` = p.`id` AND `cart`.`user_id` = '$user_id') AS count 
              FROM `products` p 
              INNER JOIN `product_categories` c ON c.`id` = p.`category_id` 
              WHERE p.`status` = 1 AND c.`name` = '$category' 
              ORDER BY p.`created_at` DESC 
              LIMIT 20 OFFSET $offset";

    $result = $mysqli->query($query);
    if ($result) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return ["error" => "Query failed: " . $mysqli->error];
    }

    // Fetch slider data
    $slider_result = $mysqli->query($product_slider);
    if ($slider_result) {
        $slider = $slider_result->fetch_all(MYSQLI_ASSOC);
    } else {
        return ["error" => "Slider query failed: " . $mysqli->error];
    }

    return [
        "data" => $products,
        "slider" => $slider
    ];
}


function getSearchedProduct($data, $query) {
    global $mysqli;

    // Construct search query based on parameters
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;
    $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    $category = isset($query['category']) ? $mysqli->real_escape_string($query['category']) : '';
    $name = isset($data['name']) ? $mysqli->real_escape_string($data['name']) : '';

    // Build the base query
    $select_clause = "SELECT p.*, c.`name` AS c_name, 
                     (SELECT `path` FROM `images` WHERE `images`.`product_id` = p.`id` LIMIT 1 OFFSET 0) AS image_path";
    $from_clause = "FROM `products` p 
                    INNER JOIN `product_categories` c ON c.`id` = p.`category_id`";
    $where_clause = "WHERE p.`status` = 1 AND p.`name` LIKE '%$name%'";

    // Add category condition if available
    if ($category) {
        $where_clause .= " AND p.`category_id` = '$category'";
    }

    // Add user cart count if user_id is provided
    if ($user_id) {
        $select_clause .= ", (SELECT COUNT(*) FROM `cart` WHERE `cart`.`product_id` = p.`id` AND `cart`.`user_id` = '$user_id') AS count";
    }

    // Final query with ordering and pagination
    $query = "$select_clause $from_clause $where_clause ORDER BY p.`created_at` DESC LIMIT 20 OFFSET $offset";

    $result = $mysqli->query($query);
    if ($result) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
        return [
            'status' => true,
            'data' => $products
        ];
    } else {
        return [
            'status' => false,
            'message' => 'Query failed: ' . $mysqli->error
        ];
    }
}

function getProductImages($data) {
    global $mysqli;

    // Get the product ID and offset
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;

    // Construct query based on whether offset is provided
    if ($offset) {
        $query = "SELECT * FROM `images` WHERE `product_id` = $product_id LIMIT 20 OFFSET $offset";
    } else {
        $query = "SELECT * FROM `images` WHERE `product_id` = $product_id";
    }

    // Execute the query
    $result = $mysqli->query($query);

    // Return the results
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return ["error" => "Query failed: " . $mysqli->error];
    }
}



// Determine which function to call based on the request
$requestMethod = $_SERVER['REQUEST_METHOD'];
$response = [];

if ($requestMethod == 'GET') {
    $url_parts = explode('/', $_SERVER['REQUEST_URI']);
    
    
    if (count($url_parts) > 3 && $url_parts[3] == 'getProductImages') {
        // Get product images based on product ID and offset
        $data = [
            'product_id' => isset($_GET['product_id']) ? $_GET['product_id'] : 0,
            'offset' => isset($_GET['offset']) ? $_GET['offset'] : 0
        ];
        $response = getProductImages($data);
    } elseif (count($url_parts) > 3 && $url_parts[3] == 'getUserProduct') {
        // Get user products
        $data = [
            'offset' => isset($_GET['offset']) ? $_GET['offset'] : 0
        ];
        $response = getUserProduct($data);
    } elseif (count($url_parts) > 3 && $url_parts[3] == 'getCategoryProduct') {
        // Get category products
        $category = isset($url_parts[4]) ? $url_parts[4] : '';
        $user_id = isset($url_parts[5]) ? $url_parts[5] : '';
        $data = [
            'offset' => isset($_GET['offset']) ? $_GET['offset'] : 0,
            'user_id' => $user_id,
            'category' => $category
        ];
        $response = getCategoryProduct($data);
    } elseif (count($url_parts) > 3 && $url_parts[3] == 'getSearchedProduct') {
        // Get searched products
        $data = [
            'offset' => isset($_GET['offset']) ? $_GET['offset'] : 0,
            'name' => isset($_GET['name']) ? $_GET['name'] : '',
            'user_id' => isset($_GET['user_id']) ? $_GET['user_id'] : ''
        ];
        $query = [
            'category' => isset($_GET['category']) ? $_GET['category'] : ''
        ];
        $response = getSearchedProduct($data, $query);
    } else {
        $response = ["error" => "Invalid endpoint"];
    } 
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

?>
