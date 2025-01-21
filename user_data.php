<?php
// Include the database connection file
include('conn.php');

// Check if required fields are present in the request
if (isset($_POST['mobileNumber']) && isset($_POST['name']) && isset($_POST['email']) && isset($_POST['address']) && isset($_POST['pincode']) && isset($_POST['deviceToken'])) {
    $mobileNumber = $_POST['mobileNumber'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $pincode = $_POST['pincode'];
    $deviceToken = $_POST['deviceToken'];

    // Debug received data
    error_log("Received Data: " . json_encode($_POST));

    // Insert the user data into the database
    $query = "INSERT INTO `users`(`phone_number`, `name`, `email_id`, `address`, `pincode` ,`device_id`) 
              VALUES ('$mobileNumber', '$name', '$email', '$address', '$pincode' ,'$deviceToken')";
    $exe = mysqli_query($mysqli, $query);

    $arr = [];
    if ($exe) {
        // Get the auto-generated ID of the inserted row
        $inserted_id = mysqli_insert_id($mysqli);

        // Fetch the newly inserted data from the table
        $fetch_query = "SELECT `id`, `phone_number`, `name`, `email_id`, `address`, `pincode` ,`device_id`
                        FROM `users` WHERE `id` = $inserted_id";
        $result = mysqli_query($mysqli, $fetch_query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            $arr["success"] = "true";
            $arr["data"] = $user_data;
        } else {
            $arr["success"] = "false";
            $arr["error"] = "Failed to fetch user data";
        }
    } else {
        $arr["success"] = "false";
        $arr["error"] = mysqli_error($mysqli); // Return error details for debugging
    }
    print(json_encode($arr));
} else {
    echo json_encode(["success" => "false", "message" => "Missing required fields"]);
}
?>
