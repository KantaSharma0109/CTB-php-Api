<?php
include('conn.php'); // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $message = $_POST['message'];
    $status = 1; // Default value
    $imp = 1; // Default value
    $profileImage = null;

    // Check if a file is uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Make sure this directory exists and is writable
        $fileName = basename($_FILES['profile_image']['name']);
        $targetFilePath = $uploadDir . uniqid() . '_' . $fileName;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFilePath)) {
            $profileImage = $targetFilePath;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
            exit;
        }
    }

    // Insert into the database
    $stmt = $mysqli->prepare("INSERT INTO testimonials (name, message, status, imp, image, profile_image) VALUES (?, ?, ?, ?, NULL, ?)");
    $stmt->bind_param("ssiis", $name, $message, $status, $imp, $profileImage);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Testimonial submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$mysqli->close();
?>
