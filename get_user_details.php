<?php
// Include your database connection file (e.g., conn.php)
include('conn.php');

// Set the response header to JSON format
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if phone_number is provided in the request
if (isset($data['phone_number']) && !empty($data['phone_number'])) {
    $phoneNumber = $data['phone_number'];

    // Query to fetch user details from the database
    $sql = "SELECT id, name, email_id, address, pincode FROM users WHERE phone_number = ?";

    if ($stmt = $mysqli->prepare($sql)) {  // Use $mysqli for database interaction
        // Bind the parameter (phone number) to the SQL query
        $stmt->bind_param("s", $phoneNumber);
        
        // Execute the query
        $stmt->execute();
        
        // Store the result
        $stmt->store_result();
        
        // Check if the user exists
        if ($stmt->num_rows > 0) {
            // Bind the result to variables
            $stmt->bind_result($id, $name, $email_id, $address, $pincode);
            
            // Fetch the user data
            $stmt->fetch();

            // Prepare the response data
            $response = array(
                "status" => true,
                "data" => array(
                    array(
                       "id"=> $id,
                        "name" => $name,
                        "email_id" => $email_id,
                        "address" => $address,
                        "pincode" => $pincode
                    )
                )
            );
        } else {
            // If no user is found
            $response = array(
                "status" => false,
                "message" => "User not found"
            );
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // If query preparation fails
        $response = array(
            "status" => false,
            "message" => "Failed to prepare SQL query"
        );
    }

    // Close the database connection
    $mysqli->close();

    // Return the JSON response
    echo json_encode($response);
} else {
    // If phone number is not provided
    $response = array(
        "status" => false,
        "message" => "Phone number is required"
    );
    echo json_encode($response);
}
?>
