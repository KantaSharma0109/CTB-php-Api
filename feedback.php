<?php
include('conn.php');
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);


// Retrieve data from the POST request
$user_id = $_POST['user_id'] ?? null;
$name = $_POST['name'] ?? null;
$number = $_POST['number'] ?? null;
$message = $_POST['message'] ?? null;


// Validate fields
if (empty($user_id) || empty($name) || empty($number) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit();
}

// Check for duplicate feedback
$checkQuery = "SELECT * FROM feedback WHERE user_id = ?";
$stmt = $mysqli->prepare($checkQuery);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
    exit();
}
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'duplicate']);
    exit();
}

// Insert feedback
$insertQuery = "INSERT INTO feedback (user_id, name, number, message, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $mysqli->prepare($insertQuery);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
    exit();
}
$stmt->bind_param("ssss", $user_id, $name, $number, $message);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>
