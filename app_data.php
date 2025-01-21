<?php
include('conn.php');
// Including the database connection

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_SERVER['HTTP_TOKEN'])) {
        $token = $_SERVER['HTTP_TOKEN'];
        $platform = isset($_SERVER['HTTP_PLATFORM']) ? $_SERVER['HTTP_PLATFORM'] : '';
        $app_version = isset($_SERVER['HTTP_APP_VERSION']) ? $_SERVER['HTTP_APP_VERSION'] : '';

        $userQuery = "SELECT * FROM `users` WHERE `device_id` = '$token'";
        $userResult = $mysqli->query($userQuery);

        if ($userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();

            $versionQuery = $platform === 'android' ?
                "SELECT `app_version` AS version, `phoneNumber`, `share_text` FROM `admin` LIMIT 1" :
                "SELECT `ios_app_version` AS version, `phoneNumber`, `share_text` FROM `admin` LIMIT 1";

            $versionResult = $mysqli->query($versionQuery);
            $versionData = $versionResult->fetch_assoc();

            $mysqli->query("UPDATE `users` SET `version` = '$app_version', `device` = '$platform', `updated` = 1 WHERE `device_id` = '$token'");

            $cartQuery = "SELECT * FROM `cart` WHERE `user_id` = '{$user['id']}'";
            $cartResult = $mysqli->query($cartQuery);
            $cart = $cartResult->fetch_all(MYSQLI_ASSOC);

            $productCategoriesQuery = "SELECT * FROM `product_categories` WHERE `status` = 1";
            $productCategoriesResult = $mysqli->query($productCategoriesQuery);
            $productCategories = $productCategoriesResult->fetch_all(MYSQLI_ASSOC);

            $courseCategoriesQuery = "SELECT * FROM `course_categories` WHERE `status` = 1 ORDER BY `order` ASC";
            $courseCategoriesResult = $mysqli->query($courseCategoriesQuery);
            $courseCategories = $courseCategoriesResult->fetch_all(MYSQLI_ASSOC);

            $impBooksQuery = "SELECT c.*, (SELECT `path` FROM `images` WHERE `images`.`book_id` = c.`id` LIMIT 1) AS image_path FROM `books` c WHERE `status` = 1 AND `imp` = 1 LIMIT 2";
            $impBooksResult = $mysqli->query($impBooksQuery);
            $impBooks = $impBooksResult->fetch_all(MYSQLI_ASSOC);

            $sliderQuery = "SELECT * FROM `slider` WHERE `status` = 1 AND `show_category` = 'all' ORDER BY `date` DESC";
            $sliderResult = $mysqli->query($sliderQuery);
            $slider = $sliderResult->fetch_all(MYSQLI_ASSOC);

            $featuredCoursesQuery = "SELECT c.*, 
                (SELECT `path` FROM `images` WHERE `images`.`course_id` = c.`id` AND `iv_category` = 'image' LIMIT 1) AS image_path,
                (SELECT COUNT(*) FROM `subscription` WHERE `subscription`.`course_id` = c.`id` AND `subscription`.`user_id` = '{$user['id']}' AND `status` = 1) AS subscribed
                FROM `courses` c WHERE `status` = 1 AND `featured` = 1 ORDER BY `created_at` DESC LIMIT 4";
            $featuredCoursesResult = $mysqli->query($featuredCoursesQuery);
            $featuredCourses = $featuredCoursesResult->fetch_all(MYSQLI_ASSOC);

            $featuredProductsQuery = "SELECT p.*, c.`name` AS c_name,
                (SELECT `path` FROM `images` WHERE `images`.`product_id` = p.`id` LIMIT 1) AS image_path,
                (SELECT COUNT(*) FROM `cart` WHERE `cart`.`product_id` = p.`id` AND `cart`.`user_id` = '{$user['id']}') AS count
                FROM `products` p INNER JOIN `product_categories` c ON c.`id` = p.`category_id`
                WHERE p.`status` = 1 AND p.`featured` = 1 ORDER BY p.`created_at` DESC LIMIT 4";
            $featuredProductsResult = $mysqli->query($featuredProductsQuery);
            $featuredProducts = $featuredProductsResult->fetch_all(MYSQLI_ASSOC);

            $response = [
                'message' => 'User_Authenticated_successfully',
                'show_popup' => $versionData['version'] != $app_version,
                'phoneNumber' => $versionData['phoneNumber'],
                'share_text' => $versionData['share_text'],
                'cart' => $cart,
                'product_categories' => $productCategories,
                'course_categories' => $courseCategories,
                'user' => $user,
                'impBooks' => $impBooks,
                'slider' => $slider,
                'featured_courses' => $featuredCourses,
                'featured_products' => $featuredProducts
            ];

            echo json_encode($response);
        } else {
            $mysqli->query("INSERT INTO `users` (`device_id`, `device`, `version`) VALUES ('$token', '$platform', '$app_version')");
            $newUserId = $mysqli->insert_id;
            echo json_encode([
                'message' => 'User_created_successfully',
                'user' => [
                    'id' => $newUserId,
                    'device_id' => $token
                ]
            ]);
        }
    } else {
        echo json_encode(['message' => 'Token_required']);
    }
} else {
    echo json_encode(['message' => 'Invalid_request_method']);
}
?>
