<?php
// fetch_subcategories.php
include('conn.php'); // Ensure this file connects to your database

// Check if category_id is provided via GET or POST
$category_id = $_GET['category_id'] ?? $_POST['category_id'] ?? null;

if (!$category_id) {
    echo json_encode(['error' => 'category_id is required']);
    exit;
}

// Use $mysqli instead of $conn
$query = "SELECT `id`, `name`, `category_id`, `status` 
          FROM `product_sub_categories` 
          WHERE `category_id` = ? AND `status` = 1";

$stmt = $mysqli->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare query']);
    exit;
}

$stmt->bind_param("i", $category_id);
$stmt->execute();

$result = $stmt->get_result();
$subcategories = [];

while ($row = $result->fetch_assoc()) {
    $subcategories[] = $row;
}

echo json_encode($subcategories);
?>
