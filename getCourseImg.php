<?php
include('conn.php'); // Include the connection file

// Function to fetch course images based on category
function getCourseImages($data, $callback) {
    global $mysqli;

    // Check the category
    if ($data['category'] == 'gallery') {
        // If 'gallery' category
        $query = "SELECT * FROM `gallery` WHERE `category` = 'course' AND `item_id` = ?";

        // Add offset to the query if it exists
        if (isset($data['offset'])) {
            $query .= " LIMIT 20 OFFSET ?";
        }

    } else {
        // If not 'gallery', querying the 'images' table
        $query = "SELECT * FROM `images` WHERE `course_id` = ? AND `iv_category` = 'image'";

        // Add offset to the query if it exists
        if (isset($data['offset'])) {
            $query .= " LIMIT 20 OFFSET ?";
        }
    }

    // Prepare and execute the query
    if ($stmt = $mysqli->prepare($query)) {
        // Bind the parameters
        if (isset($data['offset'])) {
            $stmt->bind_param("ii", $data['id'], $data['offset']); // Bind id and offset
        } else {
            $stmt->bind_param("i", $data['id']); // Bind only the id
        }

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Check if there are any results
        if ($result->num_rows > 0) {
            $images = [];
            while ($row = $result->fetch_assoc()) {
                $images[] = $row;
            }

            // Return the results as JSON
            $callback(null, $images); // Callback function with results
        } else {
            $callback("No images found", null); // Return error message if no results
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle query preparation error
        $callback("Error preparing the query", null);
    }
}

// Example usage
$data = [
    'category' => 'course', // Can be 'gallery' or 'course'
    'id' => 1, // The ID for the course/product/book
    'offset' => 0 // Optional offset for pagination
];

getCourseImages($data, function($err, $results) {
    if ($err) {
        echo json_encode(['message' => $err]);
    } else {
        echo json_encode(['data' => $results]);
    }
});
?>
