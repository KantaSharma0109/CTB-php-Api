<?php

include('conn.php'); // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the POST data
    $id = $_POST['id']; // User ID
    $name = $_POST['name'];
    $phone_number = $_POST['phone_number'];
    $email_id = $_POST['email_id'];
    $address = $_POST['address'];

    // Sanitize the input values to prevent SQL injection
    $id = mysqli_real_escape_string($mysqli, $id);
    $name = mysqli_real_escape_string($mysqli, $name);
    $phone_number = mysqli_real_escape_string($mysqli, $phone_number);
    $email_id = mysqli_real_escape_string($mysqli, $email_id);
    $address = mysqli_real_escape_string($mysqli, $address);

    // SQL query to update the user details
    $sql = "UPDATE users 
            SET name = '$name', phone_number = '$phone_number', email_id = '$email_id', address = '$address' 
            WHERE id = '$id'";

    if ($mysqli->query($sql) === TRUE) {
        // Successfully updated user details
        echo json_encode(["status" => "success", "message" => "User details updated successfully"]);
    } else {
        // Error updating user details
        echo json_encode(["status" => "error", "message" => "Error updating user details"]);
    }

    // Close the database connection
    $mysqli->close();
}
?>
