<?php
// Include the database connection file
include('conn.php');

// Check if `user_id` is provided in the query string
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    // Prepare the SQL query to fetch notifications for the given user
    $query = "SELECT * FROM `notifications` WHERE `user_id` = ?";
    
    // Prepare the statement to prevent SQL injection
    if ($stmt = $mysqli->prepare($query)) {
        
        // Bind the user_id parameter to the prepared statement
        $stmt->bind_param('i', $user_id);
        
        // Execute the query
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $notifications = [];

            // Fetch the results and store them in the notifications array
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }

            // Return the notifications as a JSON response
            echo json_encode([
                'notifications' => $notifications
            ]);
        } else {
            // Handle query execution failure
            echo json_encode(['message' => 'Some error occurred']);
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Handle SQL preparation failure
        echo json_encode(['message' => 'Database query preparation failed']);
    }
} else {
    // Handle missing user_id in the query string
    echo json_encode(['message' => 'user_id is required']);
}
?>
