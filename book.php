<?php
// Include the database connection file
include('conn.php');

// Debug: Check if parameters are received correctly
if (!isset($_GET['id'])) {
    echo json_encode(['status' => false, 'message' => 'Missing book_id parameter']);
    exit;
}

// Function to get user book by ID
function getUserBookById($id, $user_id) {
    global $mysqli;

    if ($user_id) {
        $query = "SELECT c.*, 
                  (SELECT `path` FROM `images` WHERE `images`.`book_id` = c.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path, 
                  (SELECT COUNT(*) FROM `cart` WHERE `cart`.`book_id` = c.`id` AND `cart`.`user_id` = '$user_id') AS count 
                  FROM `books` c 
                  WHERE `status` = 1 AND `id` = '$id' 
                  ORDER BY `created_at` ASC";
    } else {
        $query = "SELECT c.*, 
                  (SELECT `path` FROM `images` WHERE `images`.`book_id` = c.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
                  FROM `books` c 
                  WHERE `status` = 1 AND `id` = '$id' 
                  ORDER BY `created_at` ASC";
    }

    $result = $mysqli->query($query);

    if ($result) {
        $bookData = $result->fetch_assoc();
        
        if (empty($bookData['share_url'])) {
            $share_url = createDynamicLink("https://dashboard.cheftarunabirla.com/getUserBookbyId/$id/$user_id&book_id=$id");
            $updateShareUrl = "UPDATE `books` SET `share_url` = '$share_url' WHERE `id` = '$id'";
            $mysqli->query($updateShareUrl);
            $bookData['share_url'] = $share_url;
        }

        $response = [
            'data' => [$bookData],
            'shareText' => $bookData['title'] . " \n\n To explore more books click on the link given below\n\n👇\n\n" . $bookData['share_url']
        ];

        return $response;
    } else {
        return ['status' => false, 'message' => 'Database query error!'];
    }
}

// Function to get user books
function getUserBooks($user_id) {
    global $mysqli;

    $query = "SELECT c.*, 
              (SELECT `path` FROM `images` WHERE `images`.`book_id` = c.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path, 
              (SELECT COUNT(*) FROM `cart` WHERE `cart`.`book_id` = c.`id` AND `cart`.`user_id` = '$user_id') AS count 
              FROM `books` c 
              WHERE `status` = 1 
              ORDER BY `created_at` ASC";

    $result = $mysqli->query($query);

    if ($result) {
        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        return [
            'status' => true,
            'data' => $books
        ];
    } else {
        return ['status' => false, 'message' => 'Database query error!'];
    }
}



// Function to get book videos
function getBookVideos($bookId) {
    global $mysqli;

    $query = "SELECT * FROM `images` WHERE `book_id` = '$bookId' AND `iv_category` = 'video'";
    $result = $mysqli->query($query);

    // Debugging: Log the query
    error_log("Executing query: $query");

    if ($result) {
        $videos = [];
        while ($row = $result->fetch_assoc()) {
            $videos[] = [
                'name' => $row['name'],
                'path' => $row['path']
            ];
        }

        if (!empty($videos)) {
            return ['status' => true, 'data' => $videos];
        } else {
            return ['status' => false, 'message' => 'No videos found for this book.'];
        }
    } else {
        return ['status' => false, 'message' => 'Database query error: ' . $mysqli->error];
    }
}


// Parameters
$id = $_GET['id'];
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : 'getUserBook'; // Default action

// if ($action === 'getBookVideos') {
//     $response = getBookVideos($id);
// } else {
//     $response = getUserBookById($id, $user_id);
// }
if ($action === 'getBookVideos') {
    $response = getBookVideos($id);
} elseif ($action === 'getUserBooks') {
    $response = getUserBooks($user_id);
} else {
    $response = getUserBookById($id, $user_id);
}

echo json_encode($response);
?>
