<?php
// Include the database connection
include('conn.php');

if ($_GET['action'] === 'getUserWishlist') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    if ($user_id <= 0) {
        echo json_encode(["status" => false, "message" => "Invalid user ID"]);
        exit;
    }

    $query = "
        SELECT 
            c.*,
            CASE 
                WHEN `category` = 'course' THEN (SELECT `title` FROM `courses` WHERE `courses`.`id` = c.`course_id`)
                WHEN `category` = 'product' THEN (SELECT `name` FROM `products` WHERE `products`.`id` = c.`product_id`)
                WHEN `category` = 'book' THEN (SELECT `title` FROM `books` WHERE `books`.`id` = c.`book_id`)
            END AS name,
            CASE 
                WHEN `category` = 'course' THEN (SELECT `category` FROM `courses` WHERE `courses`.`id` = c.`course_id`)
                WHEN `category` = 'product' THEN (SELECT `category_id` FROM `products` WHERE `products`.`id` = c.`product_id`)
                WHEN `category` = 'book' THEN (SELECT `category` FROM `books` WHERE `books`.`id` = c.`book_id`)
            END AS item_category,
            CASE 
                WHEN `category` = 'course' THEN (SELECT `discount_price` FROM `courses` WHERE `courses`.`id` = c.`course_id`)
                WHEN `category` = 'product' THEN (SELECT `discount_price` FROM `products` WHERE `products`.`id` = c.`product_id`)
                WHEN `category` = 'book' THEN (SELECT `discount_price` FROM `books` WHERE `books`.`id` = c.`book_id`)
            END AS price,
            CASE 
                WHEN `category` = 'course' THEN (SELECT `path` FROM `images` WHERE `images`.`course_id` = c.`course_id` LIMIT 1)
                WHEN `category` = 'product' THEN (SELECT `path` FROM `images` WHERE `images`.`product_id` = c.`product_id` LIMIT 1)
                WHEN `category` = 'book' THEN (SELECT `path` FROM `images` WHERE `images`.`book_id` = c.`book_id` LIMIT 1)
            END AS image_path
        FROM 
            `cart` c 
        WHERE 
            `user_id` = ? AND `cart_category` = 'whislist' 
        LIMIT 20 OFFSET ?
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $user_id, $offset);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["status" => true, "data" => $data]);
    } else {
        echo json_encode(["status" => false, "message" => "Failed to fetch wishlist"]);
    }

    $stmt->close();
}
?>
