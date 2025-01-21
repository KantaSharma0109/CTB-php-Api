<?php
// Database connection
include('conn.php');


// Check if the user is logged in and get the user ID
// For example, assuming user_id is passed via a GET parameter, or is available in the session
$user_id = $_GET['user_id']; // You should properly handle session or authentication here

// Query to get the total amount and total order count for the user
$sql = "SELECT SUM(paid_price) AS total_amount, COUNT(id) AS total_orders FROM orders WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the result
$row = $result->fetch_assoc();
$total_amount = $row['total_amount'] ? $row['total_amount'] : 0;
$total_orders = $row['total_orders'] ? $row['total_orders'] : 0;

// Return the result as JSON
echo json_encode([
    'total_amount' => $total_amount,
    'total_orders' => $total_orders
]);

// Close the database connection
$stmt->close();
$mysqli->close();
?>
