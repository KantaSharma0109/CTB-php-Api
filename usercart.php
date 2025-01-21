<?php
include('conn.php');  // Your connection file

// Check if the token is present in the request
if(isset($_SERVER['HTTP_TOKEN'])) {
    $data = $_GET;
    $user_id = $data['user_id'];  // Assuming user_id is passed in the query

    // SQL queries
    $coupons = "SELECT * FROM `coupon` WHERE `status` = 1";
    $userWallet = "SELECT `wallet` FROM `users` WHERE `id` = ?";
    $courseCart = "SELECT c.`id`, c.`category`, c.`cart_category`, c.`course_id` AS item_id, c.`quantity`, b.`title` AS name, 
                         b.`discount_price`, b.`image_path`, SUM(b.`discount_price` * c.`quantity`) AS totalAmount 
                  FROM `cart` c
                  JOIN (SELECT `title`, `discount_price`, `id`, 
                               (SELECT `path` FROM `images` WHERE `images`.`course_id` = `courses`.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
                        FROM `courses`) b 
                  ON b.`id` = c.`course_id` 
                  WHERE c.`user_id` = ? AND c.`cart_category` IS NULL AND c.`category` = 'course' 
                  GROUP BY c.`id`";

    $productCart = "SELECT c.`id`, c.`category`, c.`cart_category`, c.`product_id` AS item_id, c.`quantity`, c.`address`, c.`description`, 
                           c.`pincode`, c.`image_path`, b.`name`, b.`discount_price`, SUM(b.`discount_price` * c.`quantity`) AS totalAmount 
                    FROM `cart` c
                    JOIN (SELECT `name`, `discount_price`, `id` FROM `products`) b 
                    ON b.`id` = c.`product_id` 
                    WHERE c.`user_id` = ? AND c.`cart_category` IS NULL AND c.`category` = 'product' 
                    GROUP BY c.`id`";

    $bookCart = "SELECT c.`id`, c.`category`, c.`cart_category`, c.`book_id` AS item_id, c.`quantity`, b.`title` AS name, b.`sub_category`, 
                        b.`discount_price`, b.`image_path`, SUM(b.`discount_price` * c.`quantity`) AS totalAmount 
                 FROM `cart` c
                 JOIN (SELECT `title`, `discount_price`, `id`, `category` AS sub_category, 
                               (SELECT `path` FROM `images` WHERE `images`.`book_id` = `books`.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
                        FROM `books`) b 
                 ON b.`id` = c.`book_id` 
                 WHERE c.`user_id` = ? AND c.`cart_category` IS NULL AND c.`category` = 'book' 
                 GROUP BY c.`id`";

    $bookVideosCart = "SELECT c.`id`, c.`category`, c.`cart_category`, c.`book_id` AS item_id, c.`quantity`, b.`title` AS name, 
                              b.`discount_price`, b.`image_path`, SUM(b.`discount_price` * c.`quantity`) AS totalAmount  
                       FROM `cart` c
                       JOIN (SELECT `title`, `discount_price`, `id`, 
                                    (SELECT `path` FROM `images` WHERE `images`.`book_id` = `books`.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
                             FROM `books`) b 
                       ON b.`id` = c.`book_id` 
                       WHERE c.`user_id` = ? AND c.`cart_category` IS NULL AND c.`category` = 'book-videos' 
                       GROUP BY c.`id`";

    // Execute the queries using prepared statements
    if ($stmt = $mysqli->prepare($coupons)) {
        $stmt->execute();
        $coupons_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    if ($stmt = $mysqli->prepare($userWallet)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $userWallet_result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    // Fetch Course Cart
    if ($stmt = $mysqli->prepare($courseCart)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $courseCart_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Fetch Product Cart
    if ($stmt = $mysqli->prepare($productCart)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $productCart_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Fetch Book Cart
    if ($stmt = $mysqli->prepare($bookCart)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $bookCart_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Fetch Book Videos Cart
    if ($stmt = $mysqli->prepare($bookVideosCart)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $bookVideosCart_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Process Cart Data (implement a similar function like `getCartData`)
    function getCartData($courseCart, $productCart, $bookCart, $bookVideosCart, $coupons, $userWallet, $couponId = null) {
        // You would need to implement similar logic to handle the cart and coupon data processing
        return [
            'courseCart' => $courseCart,
            'productCart' => $productCart,
            'bookCart' => $bookCart,
            'bookVideosCart' => $bookVideosCart,
            'coupons' => $coupons,
            'userWallet' => $userWallet
        ];
    }

    $result = getCartData($courseCart_result, $productCart_result, $bookCart_result, $bookVideosCart_result, $coupons_result, $userWallet_result, $data['couponId']);
    echo json_encode($result);
} else {
    echo json_encode([
        'message' => 'Auth_token_failure',
    ]);
}

?>
