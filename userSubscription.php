<?php
// Include the database connection file
include('conn.php');

// Function to get book subscription details
function getBookSubscription($book_id, $user_id) {
    global $mysqli;

    // Create the query
    $query = "SELECT * FROM `subscription` WHERE `user_id` = '$user_id' AND `book_id` = '$book_id' AND `category` = 'book'";

    // Execute the query
    $result = $mysqli->query($query);

    // Check if query was successful and return results
    if ($result) {
        // Fetch the data
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        if (count($data) > 0) {
            // If data exists, return the data as a JSON response
            echo json_encode(['data' => $data]);
        } else {
            // If no data found, return a message
            echo json_encode(['message' => 'No subscription found']);
        }
    } else {
        // If there was an error, return an error message
        echo json_encode(['message' => 'Some error occurred']);
    }
}

// Function to update book subscription status
function updateBookSubscription($book_id, $user_id) {
    global $mysqli;

    // Create the query
    $query = "UPDATE `subscription` SET `status` = 0 WHERE `user_id` = '$user_id' AND `book_id` = '$book_id' AND `category` = 'book'";

    // Execute the query
    if ($mysqli->query($query)) {
        // If successful, return a success response
        echo json_encode(['message' => 'success']);
    } else {
        // If there was an error, return an error message
        echo json_encode(['message' => 'Some error occurred']);
    }
}

// Function to update book videos subscription
function updateBookVideosSubscription($book_id, $user_id) {
    global $mysqli;

    // Create the query
    $query = "UPDATE `subscription` SET `status` = 0 WHERE `user_id` = '$user_id' AND `book_id` = '$book_id' AND `category` = 'book-videos'";

    // Execute the query
    if ($mysqli->query($query)) {
        // If successful, return a success response
        echo json_encode(['message' => 'success']);
    } else {
        // If there was an error, return an error message
        echo json_encode(['message' => 'Some error occurred']);
    }
}

// Function to get book videos subscription
function getBookVideosSubscription($book_id, $user_id) {
    global $mysqli;

    // Create the query
    $query = "SELECT * FROM `subscription` WHERE `user_id` = '$user_id' AND `book_id` = '$book_id' AND `category` = 'book-videos' AND `status` = 1 ORDER BY `id` DESC LIMIT 1 OFFSET 0";

    // Execute the query
    $result = $mysqli->query($query);

    // Check if query was successful and return results
    if ($result) {
        // Fetch the data
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        if (count($data) > 0) {
            // If data exists, return the data as a JSON response
            echo json_encode(['data' => $data]);
        } else {
            // If no data found, return a message
            echo json_encode(['message' => 'No data found']);
        }
    } else {
        // If there was an error, return an error message
        echo json_encode(['message' => 'Some error occurred']);
    }
}

// Check the action and call the respective function
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $book_id = isset($_GET['book_id']) ? $_GET['book_id'] : null;
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    
    error_log("book_id: $book_id, user_id: $user_id");

    // Call the respective function based on the action
    if ($action === 'updateBookVideosSubscription' && $book_id && $user_id) {
        updateBookVideosSubscription($book_id, $user_id);
    } elseif ($action === 'getBookVideosSubscription' && $book_id && $user_id) {
        getBookVideosSubscription($book_id, $user_id);
    } elseif ($action === 'getBookSubscription' && $book_id && $user_id) {
        getBookSubscription($book_id, $user_id);
    } elseif ($action === 'updateBookSubscription' && $book_id && $user_id) {
        updateBookSubscription($book_id, $user_id);
    } else {
        echo json_encode(['message' => 'Invalid request']);
    }
} else {
    echo json_encode(['message' => 'Action not specified']);
}

?>
