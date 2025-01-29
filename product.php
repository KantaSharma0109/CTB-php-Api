<?php
// Include database connection
include('conn.php');

// // Function to get user products
// function getUserProduct($data) {
//     global $mysqli;
//     $offset = isset($data['offset']) ? (int)$data['offset'] : 0;

//     // Query for products
//     $query = "SELECT p.*, c.`name` AS c_name, 
//                      (SELECT `path` FROM `images` WHERE `images`.`product_id` = p.`id` LIMIT 1 OFFSET 0) AS image_path 
//               FROM `products` p 
//               INNER JOIN `product_categories` c ON c.`id` = p.`category_id` 
//               WHERE p.`status` = 1 
//               ORDER BY p.`created_at` DESC 
//               LIMIT 20 OFFSET $offset";

//     $result = $mysqli->query($query);
//     if ($result) {
//         return $result->fetch_all(MYSQLI_ASSOC);
//     } else {
//         return ["error" => "Query failed: " . $mysqli->error];
//     }
// }

// // Function to get category products
// function getCategoryProduct($data) {
//     global $mysqli;

//     // Query for slider data
//     $product_slider = "SELECT * FROM `slider` 
//                        WHERE `status` = 1 AND `show_category` = 'all' AND `linked_category` = 'product' 
//                        ORDER BY `date` DESC";

//     // Query for category-specific products
//     $offset = isset($data['offset']) ? (int)$data['offset'] : 0;
//     $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
//     $category = $mysqli->real_escape_string($data['category']);

//     $query = "SELECT p.*, c.`name` AS c_name, 
//                      (SELECT `path` FROM `images` WHERE `images`.`product_id` = p.`id` LIMIT 1 OFFSET 0) AS image_path, 
//                      (SELECT COUNT(*) FROM `cart` WHERE `cart`.`product_id` = p.`id` AND `cart`.`user_id` = '$user_id') AS count 
//               FROM `products` p 
//               INNER JOIN `product_categories` c ON c.`id` = p.`category_id` 
//               WHERE p.`status` = 1 AND c.`name` = '$category' 
//               ORDER BY p.`created_at` DESC 
//               LIMIT 20 OFFSET $offset";

//     $result = $mysqli->query($query);
//     if ($result) {
//         $products = $result->fetch_all(MYSQLI_ASSOC);
//     } else {
//         return ["error" => "Query failed: " . $mysqli->error];
//     }

//     // Fetch slider data
//     $slider_result = $mysqli->query($product_slider);
//     if ($slider_result) {
//         $slider = $slider_result->fetch_all(MYSQLI_ASSOC);
//     } else {
//         return ["error" => "Slider query failed: " . $mysqli->error];
//     }

//     return [
//         "data" => $products,
//         "slider" => $slider
//     ];
// }


// function getSearchedProduct($data, $query) {
//     global $mysqli;

//     // Construct search query based on parameters
//     $offset = isset($data['offset']) ? (int)$data['offset'] : 0;
//     $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
//     $category = isset($query['category']) ? $mysqli->real_escape_string($query['category']) : '';
//     $name = isset($data['name']) ? $mysqli->real_escape_string($data['name']) : '';

//     // Build the base query
//     $select_clause = "SELECT p.*, c.`name` AS c_name, 
//                      (SELECT `path` FROM `images` WHERE `images`.`product_id` = p.`id` LIMIT 1 OFFSET 0) AS image_path";
//     $from_clause = "FROM `products` p 
//                     INNER JOIN `product_categories` c ON c.`id` = p.`category_id`";
//     $where_clause = "WHERE p.`status` = 1 AND p.`name` LIKE '%$name%'";

//     // Add category condition if available
//     if ($category) {
//         $where_clause .= " AND p.`category_id` = '$category'";
//     }

//     // Add user cart count if user_id is provided
//     if ($user_id) {
//         $select_clause .= ", (SELECT COUNT(*) FROM `cart` WHERE `cart`.`product_id` = p.`id` AND `cart`.`user_id` = '$user_id') AS count";
//     }

//     // Final query with ordering and pagination
//     $query = "$select_clause $from_clause $where_clause ORDER BY p.`created_at` DESC LIMIT 20 OFFSET $offset";

//     $result = $mysqli->query($query);
//     if ($result) {
//         $products = $result->fetch_all(MYSQLI_ASSOC);
//         return [
//             'status' => true,
//             'data' => $products
//         ];
//     } else {
//         return [
//             'status' => false,
//             'message' => 'Query failed: ' . $mysqli->error
//         ];
//     }
// }

// function getProductImages($data) {
//     global $mysqli;

//     // Get the product ID and offset
//     $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
//     $offset = isset($data['offset']) ? (int)$data['offset'] : 0;

//     // Construct query based on whether offset is provided
//     if ($offset) {
//         $query = "SELECT * FROM `images` WHERE `product_id` = $product_id LIMIT 20 OFFSET $offset";
//     } else {
//         $query = "SELECT * FROM `images` WHERE `product_id` = $product_id";
//     }

//     // Execute the query
//     $result = $mysqli->query($query);

//     // Return the results
//     if ($result) {
//         return $result->fetch_all(MYSQLI_ASSOC);
//     } else {
//         return ["error" => "Query failed: " . $mysqli->error];
//     }
// }



// // Determine which function to call based on the request
// $requestMethod = $_SERVER['REQUEST_METHOD'];
// $response = [];

// if ($requestMethod == 'GET') {
//     $url_parts = explode('/', $_SERVER['REQUEST_URI']);
    
    
//     if (count($url_parts) > 3 && $url_parts[3] == 'getProductImages') {
//         // Get product images based on product ID and offset
//         $data = [
//             'product_id' => isset($_GET['product_id']) ? $_GET['product_id'] : 0,
//             'offset' => isset($_GET['offset']) ? $_GET['offset'] : 0
//         ];
//         $response = getProductImages($data);
//     } elseif (count($url_parts) > 3 && $url_parts[3] == 'getUserProduct') {
//         // Get user products
//         $data = [
//             'offset' => isset($_GET['offset']) ? $_GET['offset'] : 0
//         ];
//         $response = getUserProduct($data);
//     } elseif (count($url_parts) > 3 && $url_parts[3] == 'getCategoryProduct') {
//         // Get category products
//         $category = isset($url_parts[4]) ? $url_parts[4] : '';
//         $user_id = isset($url_parts[5]) ? $url_parts[5] : '';
//         $data = [
//             'offset' => isset($_GET['offset']) ? $_GET['offset'] : 0,
//             'user_id' => $user_id,
//             'category' => $category
//         ];
//         $response = getCategoryProduct($data);
//     } elseif (count($url_parts) > 3 && $url_parts[3] == 'getSearchedProduct') {
//         // Get searched products
//         $data = [
//             'offset' => isset($_GET['offset']) ? $_GET['offset'] : 0,
//             'name' => isset($_GET['name']) ? $_GET['name'] : '',
//             'user_id' => isset($_GET['user_id']) ? $_GET['user_id'] : ''
//         ];
//         $query = [
//             'category' => isset($_GET['category']) ? $_GET['category'] : ''
//         ];
//         $response = getSearchedProduct($data, $query);
//     } else {
//         $response = ["error" => "Invalid endpoint"];
//     } 
// }

// // Return JSON response
// header('Content-Type: application/json');
// echo json_encode($response);

// Function to fetch product subcategories by category_id
function getProductSubcategories($category_id) {
    global $mysqli;

    $query = "SELECT * FROM `product_sub_categories` WHERE `status` = 1 AND `category_id` = '$category_id'";
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        return [
            'status' => true,
            'data' => $result->fetch_all(MYSQLI_ASSOC)
        ];
    } else {
        return [
            'status' => false,
            'data' => []
        ];
    }
}



// Function to search for products based on criteria
function getSearchedProduct($data, $query, $languageId) {
    global $mysqli;

    // Handling the category search
    if (!empty($query['category'])) {
        $category = $query['category'];
        if ($data['user_id']) {
            // If user_id exists, include the count of cart items
            $sql = "SELECT p.*, c.name AS c_name, 
                        (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path, 
                        (SELECT COUNT(*) FROM cart WHERE cart.product_id = p.id AND cart.user_id = '{$data['user_id']}') AS count
                    FROM products p
                    INNER JOIN product_categories c ON c.id = p.category_id
                    WHERE p.status = 1 
                    AND p.name LIKE '%" . ($data['name'] ? $data['name'] : '') . "%'
                    AND p.category_id = '$category'
                    ORDER BY p.created_at DESC";
        } else {
            $sql = "SELECT p.*, c.name AS c_name, 
                        (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path
                    FROM products p
                    INNER JOIN product_categories c ON c.id = p.category_id
                    WHERE p.status = 1 
                    AND p.name LIKE '%" . ($data['name'] ? $data['name'] : '') . "%'
                    AND p.category_id = '$category'
                    ORDER BY p.created_at DESC";
        }
    } else {
        // If no category is specified, search all products
        if ($data['user_id']) {
            $sql = "SELECT p.*, c.name AS c_name, 
                        (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path, 
                        (SELECT COUNT(*) FROM cart WHERE cart.product_id = p.id AND cart.user_id = '{$data['user_id']}') AS count
                    FROM products p
                    INNER JOIN product_categories c ON c.id = p.category_id
                    WHERE p.status = 1 
                    AND p.name LIKE '%" . ($data['name'] ? $data['name'] : '') . "%'
                    ORDER BY p.created_at DESC";
        } else {
            $sql = "SELECT p.*, c.name AS c_name, 
                        (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path
                    FROM products p
                    INNER JOIN product_categories c ON c.id = p.category_id
                    WHERE p.status = 1 
                    AND p.name LIKE '%" . ($data['name'] ? $data['name'] : '') . "%'
                    ORDER BY p.created_at DESC";
        }
        echo $sql;
    }

    // Execute the query
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        // Return the results as an associative array
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}


// Function to fetch user products
function getUserProducts($offset = 0, $userId, $languageId) {
    global $mysqli;
    
    if ($offset) {
        $query = "SELECT p.*, c.name AS c_name, (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path
                  FROM products p
                  INNER JOIN product_categories c ON c.id = p.category_id
                  WHERE p.status = 1
                  ORDER BY p.created_at DESC
                  LIMIT 20 OFFSET $offset";
    } else {
        $query = "SELECT p.*, c.name AS c_name, (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path
                  FROM products p
                  INNER JOIN product_categories c ON c.id = p.category_id
                  WHERE p.status = 1
                  ORDER BY p.created_at DESC";
    }

    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
        return $products;
    } else {
        return [];
    }
}

// Function to fetch category products
// function getCategoryProducts($category, $userId, $offset = 0, $languageId) {
//     global $mysqli;

//     // Query to fetch slider data for the given category
//     // $product_slider = "SELECT * FROM slider WHERE status = 1 AND show_category = 'all' AND linked_category = 'product' ORDER BY date DESC";

//     if ($offset) {
//         $query = "SELECT p.*, c.name AS c_name, (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path,
//                   (SELECT COUNT(*) FROM cart WHERE cart.product_id = p.id AND cart.user_id = '$userId') AS count
//                   FROM products p
//                   INNER JOIN product_categories c ON c.id = p.category_id
//                   WHERE p.status = 1 AND c.name = '$category'
//                   ORDER BY p.created_at DESC LIMIT 20 OFFSET $offset";
//     } else {
//         $query = "SELECT p.*, c.name AS c_name, (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path,
//                   (SELECT COUNT(*) FROM cart WHERE cart.product_id = p.id AND cart.user_id = '$userId') AS count
//                   FROM products p
//                   INNER JOIN product_categories c ON c.id = p.category_id
//                   WHERE p.status = 1 AND c.name = '$category'
//                   ORDER BY p.created_at DESC";
//     }

//     $result = $mysqli->query($query);
//     // $sliderResult = $mysqli->query($product_slider);

//     if ($result->num_rows > 0) {
//         $products = $result->fetch_all(MYSQLI_ASSOC);
//         // $slider = $sliderResult->fetch_all(MYSQLI_ASSOC);

//         return ['data' => $products, 
//         // 'slider' => $slider
//     ];
//     } 
//     // else {
//     //     return ['data' => [], 'slider' => []];
//     // }
// }
function getCategoryProducts($category, $subcategory, $userId, $offset = 0, $languageId) {
    global $mysqli;

    // Check if both category and subcategory are provided
    if ($subcategory) {
        // Query for products matching both category and subcategory
        if ($offset) {
            $query = "SELECT p.*, c.name AS c_name, (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path,
                      (SELECT COUNT(*) FROM cart WHERE cart.product_id = p.id AND cart.user_id = '$userId') AS count
                      FROM products p
                      INNER JOIN product_categories c ON c.id = p.category_id
                      INNER JOIN product_sub_categories sc ON sc.id = p.sub_category_id
                      WHERE p.status = 1 AND c.name = '$category' AND sc.name = '$subcategory'
                      ORDER BY p.created_at DESC LIMIT 20 OFFSET $offset";
        } else {
            $query = "SELECT p.*, c.name AS c_name, (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path,
                      (SELECT COUNT(*) FROM cart WHERE cart.product_id = p.id AND cart.user_id = '$userId') AS count
                      FROM products p
                      INNER JOIN product_categories c ON c.id = p.category_id
                      INNER JOIN product_sub_categories sc ON sc.id = p.sub_category_id
                      WHERE p.status = 1 AND c.name = '$category' AND sc.name = '$subcategory'
                      ORDER BY p.created_at DESC";
        }
    } else {
        // If no subcategory is provided, fetch products based on category only
        if ($offset) {
            $query = "SELECT p.*, c.name AS c_name, (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path,
                      (SELECT COUNT(*) FROM cart WHERE cart.product_id = p.id AND cart.user_id = '$userId') AS count
                      FROM products p
                      INNER JOIN product_categories c ON c.id = p.category_id
                      WHERE p.status = 1 AND c.name = '$category'
                      ORDER BY p.created_at DESC LIMIT 20 OFFSET $offset";
        } else {
            $query = "SELECT p.*, c.name AS c_name, (SELECT path FROM images WHERE images.product_id = p.id LIMIT 1 OFFSET 0) AS image_path,
                      (SELECT COUNT(*) FROM cart WHERE cart.product_id = p.id AND cart.user_id = '$userId') AS count
                      FROM products p
                      INNER JOIN product_categories c ON c.id = p.category_id
                      WHERE p.status = 1 AND c.name = '$category'
                      ORDER BY p.created_at DESC";
        }
    }

    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
        return ['data' => $products];
    } 
    return ['data' => []];
}

// Function to fetch slider data
function getSliderData() {
    global $mysqli;

    // Query to fetch slider data
    $query = "SELECT * FROM slider WHERE status = 1 AND show_category = 'all' AND linked_category = 'product' ORDER BY date DESC";

    // Execute the query
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}


// Function to fetch products based on category_id, sub_category_id, and language_id
// function getProductsByCategoryAndSubCategory($category_id, $sub_category_id, $language_id, $offset = 0) {
//     global $mysqli;

//     // Query to fetch products matching the specified category_id, sub_category_id, and language_id
//     $query = "SELECT * FROM products 
//               WHERE category_id = '$category_id' 
//               AND sub_category_id = '$sub_category_id' 
//               AND language_id = '$language_id' 
//               AND status = 1 
//               ORDER BY created_at DESC 
//               LIMIT 20 OFFSET $offset";

//     // Execute the query
//     $result = $mysqli->query($query);

//     if ($result->num_rows > 0) {
//         // Return the results as an associative array
//         return $result->fetch_all(MYSQLI_ASSOC);
//     } else {
//         return [];
//     }
// }

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['user_id']) && isset($_GET['language_id'])) {
        $userId = $_GET['user_id'];
        $languageId = $_GET['language_id'];
        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $search = isset($_GET['search']) ? $_GET['search'] : ''; // New search parameter
        $subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : ''; // New subcategory parameter

        if ($category == '' || $category == 'All') {
            // Fetching all products
            $products = getUserProducts($offset, $userId, $languageId);
            echo json_encode(['data' => $products]);
        } else {
            // Fetching products for selected category
            $categoryData = getCategoryProducts($category, $subcategory, $userId, $offset, $languageId);
            echo json_encode($categoryData);
        }
    } elseif (isset($_GET['search'])) {
        // Handling search functionality
        $search = $_GET['search'];
        $data = ['name' => $search, 'user_id' => null]; // Assuming no user_id for search
        $searchResults = getSearchedProduct($data, $_GET, $_GET['language_id']);
        echo json_encode(['data' => $searchResults]);
    } elseif (isset($_GET['category_id']) && !isset($_GET['sub_category_id']) && !isset($_GET['language_id'])) {
        // If only category_id is provided, return subcategories
        $category_id = $_GET['category_id'];
        $subcategories = getProductSubcategories($category_id);
        echo json_encode(['data' => $subcategories]);
    }
    // elseif (isset($_GET['category_id'], $_GET['sub_category_id'], $_GET['language_id'])) {
    //     // If category_id, sub_category_id, and language_id are provided, return products
    //     $category_id = $_GET['category_id'];
    //     $sub_category_id = $_GET['sub_category_id'];
    //     $language_id = $_GET['language_id'];
    //     $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;

    //     $products = getProductsByCategoryAndSubCategory($category_id, $sub_category_id, $language_id, $offset);
    //     echo json_encode(['data' => $products]);
    // }
    elseif (isset($_GET['slider']) && $_GET['slider'] == 'true') {
        $slider = getSliderData();
        echo json_encode(['slider' => $slider]);
        exit;
    }
    else {
        echo json_encode(['error' => 'Invalid parameters']);
    }
}
?>
