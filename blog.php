<?php
// Include database connection file
include('conn.php');

// Function to fetch blogs
function getBlogs($offset) {
    global $mysqli;
    $sql = "SELECT * FROM `blog` WHERE `status` = 1 LIMIT 20 OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $blogs = array();
    
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
    $stmt->close();
    return $blogs;
}

// Function to search blogs by title
function getSearchedBlogs($searchValue) {
    global $mysqli;
    $sql = "SELECT * FROM `blog` WHERE `status` = 1 AND `title` LIKE ?";
    $stmt = $mysqli->prepare($sql);
    $searchValue = "%" . $searchValue . "%";
    $stmt->bind_param("s", $searchValue);
    $stmt->execute();
    $result = $stmt->get_result();
    $blogs = array();
    
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
    $stmt->close();
    return $blogs;
}

// Function to fetch blog images by blog id
// function getBlogImages($blogId) {
//     global $mysqli;
//     $sql = "SELECT * FROM `images` WHERE `blog_id` = ?";
//     $stmt = $mysqli->prepare($sql);
//     $stmt->bind_param("i", $blogId);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $images = array();
    
//     while ($row = $result->fetch_assoc()) {
//         $images[] = $row;
//     }
//     $stmt->close();
//     return $images;
// }
function getBlogImages($blogId) {
    global $mysqli;
    $sql = "SELECT * FROM `images` WHERE `blog_id` = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $blogId);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = array();

    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    $stmt->close();

    // Return data in the required format for Flutter
    return array(
        'status' => true,
        'data' => $images
    );
}


// Handling API calls
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'getBlogs':
            $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
            $blogs = getBlogs($offset);
            echo json_encode(['status' => true, 'data' => $blogs]);
            break;
        
        case 'getSearchedBlogs':
            $searchValue = isset($_GET['search']) ? $_GET['search'] : '';
            $blogs = getSearchedBlogs($searchValue);
            echo json_encode(['status' => true, 'data' => $blogs]);
            break;
        
        case 'getBlogImages':
            $blogId = isset($_GET['id']) ? $_GET['id'] : 0;
            $images = getBlogImages($blogId);
            echo json_encode(['status' => true, 'data' => $images]);
            break;
        
        default:
            echo json_encode(['status' => false, 'message' => 'Invalid action']);
            break;
    }
}
?>
