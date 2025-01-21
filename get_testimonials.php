<?php
// Include the database connection file
include('conn.php');

// // Prepare the SQL query
// $query = "SELECT `id`, `name`, `message`, `status`, `imp`, `image`, `profile_image` FROM `testimonials` WHERE `status` = 1";

// // Execute the query
// $result = $mysqli->query($query);

// // Check if the query returns any rows
// if ($result->num_rows > 0) {
//     $testimonials = array();

//     // Fetch each row and store it in an array
//     while ($row = $result->fetch_assoc()) {
//         $testimonials[] = array(
//             'id' => $row['id'],
//             'name' => $row['name'],
//             'message' => $row['message'],
//             'status' => $row['status'],
//             'imp' => $row['imp'],
//             'image' => $row['image'],
//             'profile_image' => $row['profile_image']
//         );
//     }

//     // Return the result as JSON
//     echo json_encode(array('testimonials' => $testimonials));
// } else {
//     // Return an empty array if no rows found
//     echo json_encode(array('testimonials' => []));
// }

// // Close the database connection
// $mysqli->close();

// Prepare the SQL query
$query = "SELECT `id`, `name`, `message`, `status`, `imp`, 
                 COALESCE(`image`, '') AS `image`, 
                 `profile_image` 
          FROM `testimonials` 
          WHERE `status` = 1";

// Execute the query
$result = $mysqli->query($query);

// Check if the query returns any rows
if ($result->num_rows > 0) {
    $testimonials = array();

    // Fetch each row and store it in an array
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'message' => $row['message'],
            'status' => $row['status'],
            'imp' => $row['imp'],
            'image' => $row['image'],
            'profile_image' => $row['profile_image']
        );
    }

    // Return the result as JSON
    echo json_encode(array('testimonials' => $testimonials));
} else {
    // Return an empty array if no rows found
    echo json_encode(array('testimonials' => []));
}

// Close the database connection
$mysqli->close();


?>
