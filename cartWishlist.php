<?php
include('conn.php');

// Add Course to Cart
function addToCart($mysqli, $headers, $body) {
    if (isset($headers['token'])) {
        $query = "INSERT INTO `cart` (`category`, `course_id`, `user_id`) VALUES ('course', ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $body['id'], $body['user_id']);

        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "some_error_occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Auth_token_failure"]);
    }
}

// Add Course to Wishlist
function addToWishlist($mysqli, $headers, $body) {
    if (isset($headers['token'])) {
        $query = "INSERT INTO `cart` (`category`, `course_id`, `user_id`, `cart_category`) VALUES ('course', ?, ?, 'whislist')";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $body['id'], $body['user_id']);

        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "some_error_occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Auth_token_failure"]);
    }
}



// Remove Course from Cart
function removeFromCart($mysqli, $headers, $body) {
    if (isset($headers['token'])) {
        $query = "DELETE FROM `cart` WHERE `category` = 'course' AND `course_id` = ? AND `user_id` = ? AND `cart_category` IS NULL";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $body['id'], $body['user_id']);

        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "some_error_occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Auth_token_failure"]);
    }
}

// Remove Course from Wishlist
function removeFromWishlist($mysqli, $headers, $body) {
    if (isset($headers['token'])) {
        $query = "DELETE FROM `cart` WHERE `category` = 'course' AND `course_id` = ? AND `user_id` = ? AND `cart_category` = 'whislist'";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $body['id'], $body['user_id']);

        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "some_error_occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Auth_token_failure"]);
    }
}

// Update Cart Quantity
function updateCartQuantity($mysqli, $headers, $body) {
    if (isset($headers['token'])) {
        // Increase cart quantity
        $query = "UPDATE `cart` SET `quantity` = `quantity` + 1 WHERE `id` = ? AND `cart_category` IS NULL";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $body['id']);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "some_error_occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Auth_token_failure"]);
    }
}

// Subtract Cart Quantity
function subtractCartQuantity($mysqli, $headers, $body) {
    if (isset($headers['token'])) {
        // Decrease cart quantity
        $query = "UPDATE `cart` SET `quantity` = `quantity` - 1 WHERE `id` = ? AND `cart_category` IS NULL";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $body['id']);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "some_error_occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Auth_token_failure"]);
    }
}


// Parse Input JSON Body
function parseInputBody() {
    return json_decode(file_get_contents('php://input'), true);
}

// Handle Requests
$headers = getallheaders(); // Get headers
$body = parseInputBody(); // Parse JSON body

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_GET['action'] === 'addToCart') {
        addToCart($mysqli, $headers, $body);
    } elseif ($_GET['action'] === 'addToWishlist') {
        addToWishlist($mysqli, $headers, $body);
    } elseif ($_GET['action'] === 'removeFromCart') {
        removeFromCart($mysqli, $headers, $body);
    } elseif ($_GET['action'] === 'removeFromWishlist') {
        removeFromWishlist($mysqli, $headers, $body);
    } elseif ($_GET['action'] === 'updateCartQuantity') {
        updateCartQuantity($mysqli, $headers, $body);
    } elseif ($_GET['action'] === 'subtractCartQuantity') {
        subtractCartQuantity($mysqli, $headers, $body);
    }
}

?>
