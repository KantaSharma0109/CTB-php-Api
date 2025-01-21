<?php
include('conn.php');

// Parse Input JSON Body
function parseInputBody() {
    return json_decode(file_get_contents('php://input'), true);
}

// Add Review
function addReview($mysqli, $body) {
    // Determine the category and construct the query
    $query = "";
    if ($body['category'] == 'course') {
        $query = "INSERT INTO `reviews` (`user_id`, `message`, `category`, `course_id`) VALUES (?, ?, ?, ?)";
    } elseif ($body['category'] == 'product') {
        $query = "INSERT INTO `reviews` (`user_id`, `message`, `category`, `product_id`) VALUES (?, ?, ?, ?)";
    } elseif ($body['category'] == 'book') {
        $query = "INSERT INTO `reviews` (`user_id`, `message`, `category`, `book_id`) VALUES (?, ?, ?, ?)";
    }

    if (!empty($query)) {
        // Prepare the statement
        $stmt = $mysqli->prepare($query);
        
        // Bind parameters
        if ($body['category'] == 'book') {
            $stmt->bind_param("sssi", $body['user_id'], $body['message'], $body['category'], $body['item_id']);
        } else {
            $stmt->bind_param("sssi", $body['user_id'], $body['message'], $body['category'], $body['item_id']);
        }

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(["message" => "Review added successfully"]);
        } else {
            echo json_encode(["message" => "Some error occurred"]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(["message" => "Invalid category"]);
    }
}

// Get Reviews by Item (New functionality)
function getReviewsByItem($mysqli, $data) {
    $query = "";
    
    // Construct the query based on category and item_id
    if ($data['category'] == 'course') {
        $query = "SELECT r.*, u.`name` AS username, u.`phone_number` AS phoneNumber 
                  FROM `reviews` r 
                  INNER JOIN `users` u ON u.`id` = r.`user_id` 
                  WHERE r.`category` = ? AND r.`course_id` = ? 
                  ORDER BY r.`date` DESC LIMIT 10 OFFSET 0";
    } elseif ($data['category'] == 'product') {
        $query = "SELECT r.*, u.`name` AS username, u.`phone_number` AS phoneNumber 
                  FROM `reviews` r 
                  INNER JOIN `users` u ON u.`id` = r.`user_id` 
                  WHERE r.`category` = ? AND r.`product_id` = ? 
                  ORDER BY r.`date` DESC LIMIT 10 OFFSET 0";
    } elseif ($data['category'] == 'book') {
        $query = "SELECT r.*, u.`name` AS username, u.`phone_number` AS phoneNumber 
                  FROM `reviews` r 
                  INNER JOIN `users` u ON u.`id` = r.`user_id` 
                  WHERE r.`category` = ? AND r.`book_id` = ? 
                  ORDER BY r.`date` DESC LIMIT 10 OFFSET 0";
    }

    if (!empty($query)) {
        // Prepare the statement
        $stmt = $mysqli->prepare($query);

        // Bind parameters
        $stmt->bind_param("si", $data['category'], $data['item_id']); // Binding the category and item_id

        // Execute the query
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $reviews = $result->fetch_all(MYSQLI_ASSOC); // Fetch reviews as an associative array
            
            echo json_encode([
                "status" => true,
                "data" => $reviews
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Failed to fetch reviews"
            ]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Invalid category"
        ]);
    }
}


// Get the input data from request body
$body = parseInputBody();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the action is present in the request body
    if (isset($body['action'])) {
        // Action for adding review
        if ($body['action'] === 'addreview') {
            addReview($mysqli, $body);
        }
        // Action for fetching reviews by item
        elseif ($body['action'] === 'getreviewsbyitem') {
            getReviewsByItem($mysqli, $body);
        } else {
            echo json_encode(["message" => "Invalid action"]);
        }
    } else {
        echo json_encode(["message" => "Action not provided"]);
    }
}


$mysqli->close();
?>
