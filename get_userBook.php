<?php
// Include the database connection
include('conn.php');

$user_id = $_GET['user_id'];  // Get the user ID from the URL parameter

// Prepare the SQL query
$query = "
    SELECT c.*, 
           `books`.*, 
           `books`.`category` AS sub_category,
           (SELECT `path` FROM `images` WHERE `images`.`book_id` = `books`.`id` AND `images`.`iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path,
           (SELECT COUNT(*) FROM `cart` WHERE `cart`.`book_id` = `books`.`id` AND `cart`.`user_id` = ?) AS count
    FROM `subscription` c
    INNER JOIN `books` ON `books`.`id` = c.`book_id`
    WHERE c.`user_id` = ? AND c.`status` = 1
";

// Prepare the statement
$stmt = $mysqli->prepare($query);

// Bind the parameters (user_id is used in both places)
$stmt->bind_param("ii", $user_id, $user_id);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch all the results
$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

// Close the statement
$stmt->close();

// Close the database connection
$mysqli->close();

// Return the data in JSON format
header('Content-Type: application/json');
echo json_encode(['data' => $books]);
?>
