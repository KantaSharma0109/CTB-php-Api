<?php
header("Content-Type: application/json");

// Include database connection
include('conn.php'); // make sure you have a db_connection.php file for DB connection

// Get the book ID from the request (assuming it's passed as a parameter)
$book_id = isset($_GET['id']) ? $_GET['id'] : '';

// Initialize response array
$response = array();

// Check if book ID is provided
if (!empty($book_id)) {
    // SQL query to fetch images for the given book_id
    $query = "SELECT `id`, `path`, `name`, `iv_category`, `category`, `blog_id`, `product_id`, `course_id`, `slider_id`, `product_category_id`, `order_id`, `book_id`, `user_id`, `is_full_screen` 
              FROM `images` 
              WHERE `book_id` = '$book_id'";

    // Execute the query
    $result = mysqli_query($mysqli, $query); // Changed $conn to $mysqli

    // Check if we got results
    if (mysqli_num_rows($result) > 0) {
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row; // Add each image record to the data array
        }
        // Send response with success status
        $response['status'] = true;
        $response['data'] = $data;
    } else {
        // If no images are found
        $response['status'] = false;
        $response['message'] = "No images found for this book.";
    }
} else {
    // If book_id is missing
    $response['status'] = false;
    $response['message'] = "Book ID is required.";
}

// Return the response as JSON
echo json_encode($response);
?>
