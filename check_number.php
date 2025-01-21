<?php
// Include the database connection file
include('conn.php');

// Retrieve the mobile number from POST
if (isset($_POST['mobileNumber'])) {
    $mobileNumber = $_POST['mobileNumber'];

    // Check if the number already exists
    $query = "SELECT * FROM `users` WHERE `phone_number` = '$mobileNumber'";
    $result = mysqli_query($mysqli, $query);

    $response = [];
    if (mysqli_num_rows($result) > 0) {
        $response["exists"] = "true";
        $response["message"] = "This number is already registered. Please sign in.";
    } else {
        $response["exists"] = "false";
        $response["message"] = "Number not found. Proceeding to OTP.";
    }
    echo json_encode($response);
} else {
    echo json_encode(["exists" => "false", "message" => "Mobile number is missing."]);
}
?>
