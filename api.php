<?php
include('conn.php');
$base_url = "https://dashboard.cheftarunabirla.com";
$user_id = $_GET['user_id'] ?? '';

// Add the notification query
$token = $_GET['token'] ?? ''; // This is the token sent in the request headers
$notification_query = "
    SELECT COUNT(*) AS notificationCount 
    FROM `notifications` 
    INNER JOIN `users` ON `notifications`.`user_id` = `users`.`id` 
    WHERE `notifications`.`status` = 1 
    AND `users`.`device_id` = '$token'";

// Execute the query to get the notification count
$notification_result = $mysqli->query($notification_query);

// Initialize notification count to 0
$notification_count = 0;

if ($notification_result && $row = $notification_result->fetch_assoc()) {
    $notification_count = $row['notificationCount'];
}
// Query to fetch slider data
$query = "SELECT * FROM `slider` WHERE `status` = 1 AND `show_category` = 'all' ORDER BY `date` DESC";

// Query to fetch course categories data
$course_categories_query = "SELECT `id`, `name`, `path`, `status`, `imp`, `order` FROM `course_categories` WHERE `status` = 1 ORDER BY `order` ASC";

// Query to fetch featured courses
$user_id = $_GET['user_id'] ?? ''; // Example: Pass user_id as a parameter
$featured_courses_query = "
    SELECT c.*,
           (SELECT `path` FROM `images` WHERE `images`.`course_id` = c.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path,
           (SELECT COUNT(*) FROM `subscription` WHERE `subscription`.`course_id` = c.`id` AND `subscription`.`user_id` = '$user_id' AND `status` = 1) AS subscribed
    FROM `courses` c
    WHERE `status` = 1 AND `featured` = 1
    ORDER BY `created_at` DESC
    LIMIT 4 OFFSET 0";
   
    // Query to fetch social links data
$social_links_query = "SELECT `id`, `name`, `url`, `image`, `status`, `show_category`, `linked_category`, `linked_array` FROM `social_links` WHERE `status` = 1";

// Query to fetch gallery data
$gallery_query = "SELECT `id`, `path`, `date`, `iv_category`, `category`, `item_id`, `thumbnail`, `imp`, `is_full_screen` FROM `gallery` ORDER BY `date` DESC LIMIT 3 OFFSET 0";
// Query to fetch featured products
// $featured_products_query = "
//     SELECT p.*, 
//            c.`name` AS c_name, 
//            (SELECT `path` FROM `images` WHERE `images`.`product_id` = p.`id` LIMIT 1 OFFSET 0) AS image_path, 
//            (SELECT COUNT(*) FROM `cart` WHERE `cart`.`product_id` = p.`id` AND `cart`.`user_id` = '$user_id') AS count,
//            (SELECT COUNT(*) FROM `cart` WHERE `cart`.`course_id` = c.`id` AND `cart`.`cart_category` = 'whislist' AND `cart`.`user_id` = '$user_id') AS whislistcount
//     FROM `products` p
//     INNER JOIN `product_categories` c ON c.`id` = p.`category_id`
//     WHERE p.`status` = 1 AND p.`featured` = 1
//     ORDER BY p.`created_at` DESC
//     LIMIT 4 OFFSET 0";
    // Query to fetch product categories data
$product_categories_query = "SELECT `id`, `name`,`path`, `status` FROM `product_categories` WHERE `status` = 1";
// $product_sub_categories_query = "SELECT `id`, `name`,`category_id`, `status` FROM `product_sub_categories` WHERE `status` = 1";

// Query to fetch featured important books
$imp_books_query = "
    SELECT c.*, 
           (SELECT `path` FROM `images` WHERE `images`.`book_id` = c.`id` LIMIT 1 OFFSET 0) AS image_path
    FROM `books` c 
    WHERE `status` = 1 AND `imp` = 1 
    LIMIT 2 OFFSET 0";
//cart query
// $cart_query = "SELECT * FROM `cart` WHERE `user_id` = '$user_id'";
$cart_query = "
    SELECT cart.*, 
           COALESCE(
               (SELECT title FROM books WHERE books.id = cart.book_id),
               (SELECT title FROM courses WHERE courses.id = cart.course_id),
               (SELECT name FROM products WHERE products.id = cart.product_id)
           ) AS name
    FROM cart
    WHERE cart.user_id = '$user_id'";



    
$sliders_result = $mysqli->query($query);
$course_categories_result = $mysqli->query($course_categories_query);
$featured_courses_result = $mysqli->query($featured_courses_query);
$social_links_result = $mysqli->query($social_links_query);
$gallery_result = $mysqli->query($gallery_query);
// $featured_products_result = $mysqli->query($featured_products_query);
$product_categories_result = $mysqli->query($product_categories_query);
// $product_sub_categories_result= $mysqli->query($product_sub_categories_query);
$imp_books_result = $mysqli->query($imp_books_query);
$cart_result = $mysqli->query($cart_query);


if ($sliders_result === false || $course_categories_result === false || $featured_courses_result === false) {
    echo json_encode(array('status' => false, 'message' => 'Query failed. Error: ' . $mysqli->error));
} else {
    // Process sliders data
    $sliders = array();
    while ($row = $sliders_result->fetch_assoc()) {
        $image_path = isset($row['path']) ? $base_url . $row['path'] : null;
        $thumbnail_path = isset($row['thumbnail']) ? $base_url . $row['thumbnail'] : null;
        $video_path = isset($row['video_path']) ? $row['video_path'] : null;

        if ($row['category'] == 'video' && $video_path) {
            $image_path = $thumbnail_path;
        }

        $sliders[] = array(
            'id' => $row['id'],
            'category' => $row['category'],
            'image_path' => $image_path,
            'thumbnail' => $thumbnail_path,
            'video_path' => $video_path,
            'linked_category' => $row['linked_category'] ?? null,
            'linked_array' => $row['linked_array'] ?? null,
        );
    }
}
    // Process course categories data
    $course_categories = array();
    while ($row = $course_categories_result->fetch_assoc()) {
        $course_categories[] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'image_path' => $base_url . $row['path'], // Assuming the 'path' field stores the image path
            'imp' => $row['imp'],
        );
    }

    // Process featured courses data
    $featured_courses = array();
    while ($row = $featured_courses_result->fetch_assoc()) {
        $featured_courses[] = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'promo_video' => $row['promo_video'],
            'price' => $row['price'],
            'discount_price' => $row['discount_price'],
            'days' => $row['days'],
            'category' => $row['category'],
            'image_path' => $base_url . $row['image_path'],
            'share_url' => $row['share_url'],
            'subscribed' => $row['subscribed'],
        );
    }


    // Process social links data
    $social_links = array();
    while ($row = $social_links_result->fetch_assoc()) {
        $social_links[] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'url' => $row['url'],
            'image_path' => $base_url . $row['image'],
            'show_category' => $row['show_category'],
            'linked_category' => $row['linked_category'],
            'linked_array' => $row['linked_array'],
        );
    }
     // Process gallery data
     $gallery = array();
     while ($row = $gallery_result->fetch_assoc()) {
         $gallery[] = array(
             'id' => $row['id'],
             'path' => $base_url . $row['path'],
         );
     }
    
// Check if the query was successful
// if ($featured_products_result === false) {
//     echo json_encode(array('status' => false, 'message' => 'Query failed. Error: ' . $mysqli->error));
// } 
// else 
{
//     // Process the featured products data
//     $featured_products = array();
//     while ($row = $featured_products_result->fetch_assoc()) {
//         $featured_products[] = array(
//             'id' => $row['id'],
//             'name' => $row['name'],
//             'description' => $row['description'],
//             'c_name' => $row['c_name'],
//             'category_id' => $row['category_id'],
//             'price' => $row['price'],
//             'discount_price' => $row['discount_price'],
//             'stock' => $row['stock'],
//             'image_path' => $base_url . $row['image_path'],
//             'share_url' => $row['share_url'],
//         );
    // }
   
// Check if the query was successful
// if ($product_categories_result === false) {
//     echo json_encode(array('status' => false, 'message' => 'Query failed. Error: ' . $mysqli->error));
// } else {
//     // Process the product categories data
//     $product_categories = array();
//     while ($row = $product_categories_result->fetch_assoc()) {
//         $product_categories[] = array(
//             'id' => $row['id'],
//             'name' => $row['name'],
//         );
//     }
// }
if ($product_categories_result === false) {
    echo json_encode(array('status' => false, 'message' => 'Query failed. Error: ' . $mysqli->error));
} else {
    // Process the product categories data
    $product_categories = array();
    while ($row = $product_categories_result->fetch_assoc()) {
        $product_categories[] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'image_path' => $base_url . $row['path'],
            'status' => $row['status'],
        );
    }
    // echo json_encode(array('status' => true, 'product_categories' => $product_categories));
}

// if ($product_sub_categories_result === false) {
//     echo json_encode(array('status' => false, 'message' => 'Query failed. Error: ' . $mysqli->error));
// } else {
//     // Process the product categories data
//     $product_sub_categories = array();
//     while ($row = $product_sub_categories_result->fetch_assoc()) {
//         $product_sub_categories[] = array(
//             'id' => $row['id'],
//             'name' => $row['name'],
//             'category_id' =>$row['category_id'],
//             'status' => $row['status'],
//         );
//     }
//     // echo json_encode(array('status' => true, 'product_categories' => $product_categories));
// }

if ($imp_books_result === false) {
    echo json_encode(array('status' => false, 'message' => 'Query failed. Error: ' . $mysqli->error));
} else {
    // Process the impBooks data
    $imp_books = array();
    while ($row = $imp_books_result->fetch_assoc()) {
        $imp_books[] = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'price' => $row['price'],
            'discount_price' => $row['discount_price'],
            'days' => $row['days'],
            'category' => $row['category'],
            'image_path' => $base_url . $row['image_path'],
            'pdflink' => $row['pdf'] ?? '',
            'price_with_video' => $row['price_with_video'] ?? '',
            'discount_price_with_video' => $row['discount_price_with_video'] ?? '',
            'video_days' => $row['video_days'] ?? 0,
            'only_video_price' => $row['only_video_price'] ?? '',
            'only_video_discount_price' => $row['only_video_discount_price'] ?? '',
            'share_url' => $row['share_url'] ?? '',
            'include_videos' => $row['include_videos'] ?? ''
        );
    }
}

// Process cart data
$cart = array();
while ($row = $cart_result->fetch_assoc()) {
    $cart[] = array(
        'id' => $row['id'],
        'user_id' => $row['user_id'],
        'product_id' => $row['product_id'],
        'course_id' => $row['course_id'],
        'book_id' => $row['book_id'],
        'category' => $row['category'],
        'quantity' => $row['quantity'],
        'name' => $row['name'],
        'image_path' => $base_url . $row['image_path'],
        'cart_category' => $row['cart_category'], // e.g., 'wishlist' or 'cart'
    );
}

    // Return JSON response with sliders, course categories, and featured courses
    header('Content-Type: application/json');
    echo json_encode(array(
        'status' => true,
        'data' => array(
            
            'sliders' => $sliders,
            'course_categories' => $course_categories,
            'featured_courses' => $featured_courses,
            'social_links' => $social_links,
             'gallery' => $gallery,
            //  'featured_products' => $featured_products,
             'product_categories' => $product_categories,
            //  'product_sub_categories' => $product_sub_categories,
             'impBooks' => $imp_books,
             'cart' => $cart,
             'notification_count' => $notification_count,
        )
    ), JSON_UNESCAPED_SLASHES);
}

$mysqli->close();
?>
