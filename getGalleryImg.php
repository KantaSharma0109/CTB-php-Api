<?php
include('conn.php'); // Include the connection file

// Function to fetch gallery images
function getGalleryImages($data, $callback) {
    global $mysqli;

    // Prepare the SQL query to fetch gallery images, ordered by date, with pagination
    $query = "SELECT * FROM `gallery` WHERE `category` = 'gallery' ORDER BY `date` DESC LIMIT 20 OFFSET ?";

    // Prepare and execute the query
    if ($stmt = $mysqli->prepare($query)) {
        // Bind the parameters (offset)
        $stmt->bind_param("i", $data['offset']); // Bind the offset parameter

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

            // Return the results as JSON via the callback
            $callback(null, $images); // Callback function with results
        } else {
            // If no images are found
            $callback("No gallery images found", null); // Return error message
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle query preparation error
        $callback("Error preparing the query", null);
    }
}

// Example usage of the function
$data = [
    'offset' => 0 // Offset for pagination
];

getGalleryImages($data, function($err, $results) {
    if ($err) {
        // If error occurs, return error message
        echo json_encode(['message' => $err]);
    } else {
        // Return results as JSON
        echo json_encode(['data' => $results]);
    }
});
?>
