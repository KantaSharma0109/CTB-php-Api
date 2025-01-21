<?php
// Include the database connection file
include('conn.php');

// Function to get product by ID
function getUserProductById($data) {
    global $mysqli;

    $response = array();

    if (!isset($data['id'])) {
        $response['error'] = 'Product ID is required';
        echo json_encode($response);
        return;
    }

    $productId = $data['id'];
    $userId = isset($data['user_id']) ? $data['user_id'] : null;

    // Construct the query based on whether user_id is provided or not
    if ($userId) {
        $query = "
            SELECT p.*, c.name AS c_name, 
            (SELECT path FROM images WHERE product_id = p.id LIMIT 1 OFFSET 0) AS image_path,
            (SELECT COUNT(*) FROM cart WHERE cart.product_id = p.id AND cart.user_id = '$userId') AS count
            FROM products p
            INNER JOIN product_categories c ON c.id = p.category_id
            WHERE p.status = 1 AND p.id = '$productId'
            ORDER BY p.created_at DESC
        ";
    } else {
        $query = "
            SELECT p.*, c.name AS c_name, 
            (SELECT path FROM images WHERE product_id = p.id LIMIT 1 OFFSET 0) AS image_path
            FROM products p
            INNER JOIN product_categories c ON c.id = p.category_id
            WHERE p.status = 1 AND p.id = '$productId'
            ORDER BY p.created_at DESC
        ";
    }

    // Execute the query
    if ($result = $mysqli->query($query)) {
        $product = $result->fetch_assoc();

        if ($product) {
            // Check if the share URL is null or empty
            if (empty($product['share_url'])) {
                // Generate a dynamic link (use your actual link generation logic here)
                $shareUrl = createDynamicLink("https://dashboard.cheftarunabirla.com/getUserProductById/{$productId}/{$userId}&product_id={$productId}");

                // Update the product with the generated share URL
                $updateQuery = "UPDATE products SET share_url = '$shareUrl' WHERE id = '$productId'";
                if ($mysqli->query($updateQuery)) {
                    $product['share_url'] = $shareUrl; // Add the new share URL to the product details
                } else {
                    $response['error'] = 'Failed to update the share URL';
                    echo json_encode($response);
                    return;
                }
            }

            // Return the product details
            $response['product'] = $product;
            echo json_encode($response);
        } else {
            $response['error'] = 'Product not found';
            echo json_encode($response);
        }
    } else {
        $response['error'] = 'Database query failed';
        echo json_encode($response);
    }
}

// Simulate the dynamic link creation function (replace with your actual logic)
function createDynamicLink($url) {
    // This should be the actual logic to generate a dynamic link (for now returning the URL as is)
    return $url;
}

// Example of data input
$data = json_decode(file_get_contents('php://input'), true);

// Call the function to get the product by ID
getUserProductById($data);

?>
