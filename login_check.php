<?php
include('conn.php'); // Include the connection file

// Check if the phone number is passed
if (isset($_POST['phone_number'])) {
    $phone_number = $_POST['phone_number'];

    // Query to check if the phone number exists
    $query = "SELECT `id`, `phone_number`, `name`, `email_id`, `address`, `pincode`, `country`, `state`, `city` FROM `users` WHERE `phone_number` = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Phone number exists, fetch user data
        $user_data = $result->fetch_assoc();
        echo json_encode(["status" => "success", "user_data" => $user_data]);
    } else {
        // Phone number does not exist
        echo json_encode(["status" => "error", "message" => "Phone number not found. Please sign up first."]);
    }

    $stmt->close();
    $mysqli->close();
}
?>
