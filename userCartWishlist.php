<?php
include('conn.php');

// Parse Input JSON Body
function parseInputBody() {
    return json_decode(file_get_contents('php://input'), true);
}

// Add to Wishlist
function addToWishlist($mysqli, $body) {
    $query = "";
    if ($body['category'] == 'product') {
        $query = "INSERT INTO `cart` (`category`, `product_id`, `user_id`, `cart_category`) VALUES ('product', ?, ?, 'whislist')";
    } elseif ($body['category'] == 'course') {
        $query = "INSERT INTO `cart` (`category`, `course_id`, `user_id`, `cart_category`) VALUES ('course', ?, ?, 'whislist')";
    } elseif ($body['category'] == 'book' || $body['category'] == 'book-videos') {
        $query = "INSERT INTO `cart` (`category`, `book_id`, `user_id`, `cart_category`) VALUES ('" . $body['category'] . "', ?, ?, 'whislist')";
    }

    if (!empty($query)) {
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $body['id'], $body['user_id']);
        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "Some error occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Invalid category"]);
    }
}

// Remove from Wishlist
function removeFromWishlist($mysqli, $body) {
    $query = "";
    if ($body['category'] == 'product') {
        $query = "DELETE FROM `cart` WHERE `category` = 'product' AND `product_id` = ? AND `user_id` = ? AND `cart_category` = 'whislist'";
    } elseif ($body['category'] == 'course') {
        $query = "DELETE FROM `cart` WHERE `category` = 'course' AND `course_id` = ? AND `user_id` = ? AND `cart_category` = 'whislist'";
    } elseif ($body['category'] == 'book' || $body['category'] == 'book-videos') {
        $query = "DELETE FROM `cart` WHERE `category` = ? AND `book_id` = ? AND `user_id` = ? AND `cart_category` = 'whislist'";
    }

    if (!empty($query)) {
        $stmt = $mysqli->prepare($query);
        if ($body['category'] == 'book' || $body['category'] == 'book-videos') {
            $stmt->bind_param("sii", $body['category'], $body['id'], $body['user_id']);
        } else {
            $stmt->bind_param("si", $body['id'], $body['user_id']);
        }
        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "Some error occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Invalid category"]);
    }
}

// Add to Cart
function addToCart($mysqli, $body) {
    $query = "";
    if ($body['category'] == 'product') {
        $query = "INSERT INTO `cart` (`category`, `product_id`, `user_id`, `cart_category`) VALUES ('product', ?, ?, NULL)";
    } elseif ($body['category'] == 'course') {
        $query = "INSERT INTO `cart` (`category`, `course_id`, `user_id`, `cart_category`) VALUES ('course', ?, ?, NULL)";
    } elseif ($body['category'] == 'book' || $body['category'] == 'book-videos') {
        $query = "INSERT INTO `cart` (`category`, `book_id`, `user_id`, `cart_category`) VALUES ('" . $body['category'] . "', ?, ?, NULL)";
    }

    if (!empty($query)) {
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $body['id'], $body['user_id']);
        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "Some error occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Invalid category"]);
    }
}


// Remove from Cart
function removeFromCart($mysqli, $body) {
    $query = "";
    if ($body['category'] == 'product') {
        $query = "DELETE FROM `cart` WHERE `category` = 'product' AND `product_id` = ? AND `user_id` = ? AND `cart_category` IS NULL";
    } elseif ($body['category'] == 'course') {
        $query = "DELETE FROM `cart` WHERE `category` = 'course' AND `course_id` = ? AND `user_id` = ? AND `cart_category` IS NULL";
    } elseif ($body['category'] == 'book' || $body['category'] == 'book-videos') {
        $query = "DELETE FROM `cart` WHERE `category` = ? AND `book_id` = ? AND `user_id` = ? AND `cart_category` IS NULL";
    }

    if (!empty($query)) {
        $stmt = $mysqli->prepare($query);
        if ($body['category'] == 'book' || $body['category'] == 'book-videos') {
            $stmt->bind_param("sii", $body['category'], $body['id'], $body['user_id']);
        } else {
            $stmt->bind_param("si", $body['id'], $body['user_id']);
        }
        if ($stmt->execute()) {
            echo json_encode(["message" => "success"]);
        } else {
            echo json_encode(["message" => "Some error occurred"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Invalid category"]);
    }
}

// Add Book to Cart
function addBookToCart($mysqli, $body) {
    // Prepare the SQL query to insert a book into the cart
    $query = "INSERT INTO `cart` (`category`, `book_id`, `user_id`, `address`, `quantity`, `pincode`) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    // Prepare the statement
    $stmt = $mysqli->prepare($query);
    
    // Bind parameters for the prepared statement
    $stmt->bind_param("siisis", $body['category'], $body['id'], $body['user_id'], $body['address'], $body['quantity'], $body['pincode']);
    
    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(["message" => "success"]);
    } else {
        echo json_encode(["message" => "Some error occurred"]);
    }

    // Close the statement
    $stmt->close();
}

// Update Cart Quantity
// function updateCartQuantity($mysqli, $body) {
//     $query = "UPDATE `cart` SET `quantity` = `quantity` + 1 WHERE `id` = ? AND `cart_category` IS NULL";
//     $stmt = $mysqli->prepare($query);
//     $stmt->bind_param("i", $body['id']);
    
//     if ($stmt->execute()) {
//         echo json_encode(["message" => "success"]);
//     } else {
//         echo json_encode(["message" => "Some error occurred"]);
//     }
//     $stmt->close();
// }

if ($_GET['action'] === 'updateCartQuantity') {
    $body = parseInputBody();
    $query = "UPDATE `cart` SET `quantity` = `quantity` + 1 WHERE `id` = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $body['id']);
    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "Quantity updated"]);
    } else {
        echo json_encode(["status" => false, "message" => "Error updating quantity"]);
    }
    $stmt->close();
}


// Subtract Cart Quantity
// function subtractCartQuantity($mysqli, $body) {
//     $query = "UPDATE `cart` SET `quantity` = `quantity` - 1 WHERE `id` = ? AND `cart_category` IS NULL";
//     $stmt = $mysqli->prepare($query);
//     $stmt->bind_param("i", $body['id']);
    
//     if ($stmt->execute()) {
//         echo json_encode(["message" => "success"]);
//     } else {
//         echo json_encode(["message" => "Some error occurred"]);
//     }
//     $stmt->close();
// }
if ($_GET['action'] === 'subtractCartQuantity') {
    $body = parseInputBody();
    $query = "UPDATE `cart` SET `quantity` = `quantity` - 1 WHERE `id` = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $body['id']);
    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "Quantity updated"]);
    } else {
        echo json_encode(["status" => false, "message" => "Error updating quantity"]);
    }
    $stmt->close();
}



$body = parseInputBody();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_GET['action'] === 'addtowhislist') {
        addToWishlist($mysqli, $body);
    } elseif ($_GET['action'] === 'removefromwhislist') {
        removeFromWishlist($mysqli, $body);
    } elseif ($_GET['action'] === 'addtocart') {
        addToCart($mysqli, $body);
    } elseif ($_GET['action'] === 'removefromcart') {
        removeFromCart($mysqli, $body);
    }elseif ($_GET['action'] === 'addbooktocart') {
        addBookToCart($mysqli, $body); 
     } if ($_GET['action'] === 'updatecartquantity') {
        updateCartQuantity($mysqli, $body);
    } elseif ($_GET['action'] === 'subtractcartquantity') {
        subtractCartQuantity($mysqli, $body);
        
    }else {
        echo json_encode(["message" => "Invalid action"]);
    }
}

$mysqli->close();

?>
