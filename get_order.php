<?php
// Include your database connection
include('conn.php'); // Make sure to include your database connection file

// Function to get user orders
function getUserOrders($user_id) {
    global $mysqli;

    // SQL query
    $query = "SELECT c.*, c.image_path AS order_image,
        CASE WHEN `category` = 'course' THEN 
            (SELECT `title` FROM `courses` WHERE `courses`.`id` = c.`course_id`)
        WHEN `category` = 'product' THEN 
            (SELECT `name` FROM `products` WHERE `products`.`id` = c.`product_id`)
        WHEN `category` = 'book' THEN 
            (SELECT `title` FROM `books` WHERE `books`.`id` = c.`book_id`) 
        END AS name,
        CASE WHEN `category` = 'course' THEN 
            (SELECT `discount_price` FROM `courses` WHERE `courses`.`id` = c.`course_id`)
        WHEN `category` = 'product' THEN 
            (SELECT `discount_price` FROM `products` WHERE `products`.`id` = c.`product_id`)
        WHEN `category` = 'book' THEN 
            (SELECT `discount_price` FROM `books` WHERE `books`.`id` = c.`book_id`) 
        END AS price,
        CASE WHEN `category` = 'course' THEN 
            (SELECT (SELECT `path` FROM `images` WHERE `images`.`course_id` = `courses`.`id` AND `images`.`iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
             FROM `courses` WHERE `courses`.`id` = c.`course_id`)
        WHEN `category` = 'product' THEN 
            (SELECT (SELECT `path` FROM `images` WHERE `images`.`product_id` = `products`.`id` AND `images`.`iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
             FROM `products` WHERE `products`.`id` = c.`product_id`)
        WHEN `category` = 'book' THEN 
            (SELECT (SELECT `path` FROM `images` WHERE `images`.`book_id` = `books`.`id` AND `images`.`iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
             FROM `books` WHERE `books`.`id` = c.`book_id`)
        END AS image_path
    FROM `orders` c 
    WHERE c.user_id = ?";

    // Prepare the statement
    if ($stmt = $mysqli->prepare($query)) {
        // Bind the user_id parameter
        $stmt->bind_param("i", $user_id);

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Check if there are results
        if ($result->num_rows > 0) {
            $orders = [];
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }

            // Return the results in the same format as expected by Flutter
            echo json_encode([
                'status' => true,
                'data' => $orders
            ]);
        } else {
            // Return a message if no orders are found
            echo json_encode([
                'status' => false,
                'message' => 'No orders found'
            ]);
        }

        // Close the statement
        $stmt->close();
    } else {
        // Return an error if the query preparation fails
        echo json_encode([
            'status' => false,
            'message' => 'Error in query preparation'
        ]);
    }
}

// Call the function and pass the user_id
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    getUserOrders($user_id);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'User ID is required'
    ]);
}
?>
