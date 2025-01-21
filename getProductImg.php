<?php
// Include the database connection file
include('conn.php');

// Function to get product images
function getProductImages($product_id, $offset = null) {
    global $mysqli;
    
    // Check if offset is provided
    if ($offset !== null) {
        // Prepare the query with LIMIT and OFFSET
        $query = "SELECT * FROM `images` WHERE `product_id` = ? LIMIT 20 OFFSET ?";
        
        // Prepare the statement
        if ($stmt = $mysqli->prepare($query)) {
            // Bind parameters
            $stmt->bind_param("ii", $product_id, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Fetch data
            if ($result->num_rows > 0) {
                $data = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(['data' => $data]);
            } else {
                echo json_encode(['message' => 'No images found']);
            }
            
            $stmt->close();
        } else {
            echo json_encode(['message' => 'Query preparation failed']);
        }
    } else {
        // If offset is not provided, fetch all images
        $query = "SELECT * FROM `images` WHERE `product_id` = ?";
        
        // Prepare the statement
        if ($stmt = $mysqli->prepare($query)) {
            // Bind parameter
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Fetch data
            if ($result->num_rows > 0) {
                $data = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(['data' => $data]);
            } else {
                echo json_encode(['message' => 'No images found']);
            }
            
            $stmt->close();
        } else {
            echo json_encode(['message' => 'Query preparation failed']);
        }
    }
}

// Example usage (for testing)
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;
$offset = isset($_GET['offset']) ? $_GET['offset'] : null;

if ($product_id) {
    getProductImages($product_id, $offset);
} else {
    echo json_encode(['message' => 'Product ID not provided']);
}
?>
