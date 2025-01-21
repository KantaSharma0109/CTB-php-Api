<?php
include('conn.php');

function getCoursesByCategory($mysqli, $headers, $query) {
    if (isset($headers['token'])) {
        $category = isset($query['category']) ? $query['category'] : null;
        $userId = isset($query['user_id']) ? $query['user_id'] : null;
        $offset = isset($query['offset']) ? (int) $query['offset'] : 0;

        // Query to get the total count of courses
        if ($category) {
            $totalCoursesQuery = "SELECT COUNT(*) AS total FROM `courses` WHERE `status` = 1 AND `category` = ?";
            $coursesQuery = "SELECT c.*, 
                            (SELECT DATEDIFF(end_date, CURRENT_TIMESTAMP) 
                             FROM `subscription` 
                             INNER JOIN `users` ON `users`.`id` = `subscription`.`user_id` 
                             WHERE `users`.`id` = ? 
                             AND `subscription`.`course_id` = c.`id` 
                             AND `subscription`.`status` = 1 
                             ORDER BY `subscription`.`id` DESC LIMIT 1) AS subscribeddays,
                            (SELECT `path` 
                             FROM `images` 
                             WHERE `images`.`course_id` = c.`id` 
                             AND `iv_category` = 'image' 
                             LIMIT 1) AS image_path
                            FROM `courses` c 
                            WHERE `status` = 1 
                            AND `category` = ? 
                            ORDER BY `created_at` ASC
                            LIMIT 20 OFFSET ?";
        } else {
            $totalCoursesQuery = "SELECT COUNT(*) AS total FROM `courses` WHERE `status` = 1";
            $coursesQuery = "SELECT c.*, 
                            (SELECT DATEDIFF(end_date, CURRENT_TIMESTAMP) 
                             FROM `subscription` 
                             INNER JOIN `users` ON `users`.`id` = `subscription`.`user_id` 
                             WHERE `users`.`id` = ? 
                             AND `subscription`.`course_id` = c.`id` 
                             AND `subscription`.`status` = 1 
                             ORDER BY `subscription`.`id` DESC LIMIT 1) AS subscribeddays,
                            (SELECT `path` 
                             FROM `images` 
                             WHERE `images`.`course_id` = c.`id` 
                             AND `iv_category` = 'image' 
                             LIMIT 1) AS image_path
                            FROM `courses` c 
                            WHERE `status` = 1 
                            ORDER BY `created_at` ASC 
                            LIMIT 20 OFFSET ?";
        }

        // Prepare and execute queries
        $stmt = $mysqli->prepare($totalCoursesQuery);
        if ($category) {
            $stmt->bind_param("s", $category); // Bind category for filtering
        } else {
            $stmt->bind_param("s", $userId); // Bind userId for subscription
        }
        $stmt->execute();
        $totalCoursesResult = $stmt->get_result();
        $stmt->close();

        // Fetch total count
        $totalCourses = $totalCoursesResult->fetch_assoc();

        // Query to get the courses
        $stmt = $mysqli->prepare($coursesQuery);
        if ($category) {
            $stmt->bind_param("ssi", $userId, $category, $offset); // Bind userId, category, and offset
        } else {
            $stmt->bind_param("si", $userId, $offset); // Bind only userId and offset
        }
        $stmt->execute();
        $coursesResult = $stmt->get_result();
        $stmt->close();

        // If the query was successful, return the data
        if ($totalCoursesResult && $coursesResult) {
            $courses = [];
            while ($row = $coursesResult->fetch_assoc()) {
                $courses[] = $row;
            }

            echo json_encode([
                "message" => "success",
                "total" => $totalCourses['total'],
                "meta" => [
                    "total" => $totalCourses['total'],
                    "offset" => $offset,
                    "totalitems" => count($courses)
                ],
                "courses" => $courses
            ]);
        } else {
            echo json_encode(["message" => "some_error_occurred"]);
        }
    } else {
        echo json_encode(["message" => "Auth_token_failure"]);
    }
}



function getSearchedCourses($mysqli, $headers, $value, $query) {
    if (isset($headers['token'])) {
        $userId = isset($query['user_id']) ? $query['user_id'] : null;
        $coursesQuery = "SELECT c.*, 
                        (SELECT DATEDIFF(end_date, CURRENT_TIMESTAMP) 
                         FROM `subscription` 
                         INNER JOIN `users` ON `users`.`id` = `subscription`.`user_id` 
                         WHERE `users`.`id` = '$userId' 
                         AND `subscription`.`course_id` = c.`id` 
                         AND `subscription`.`status` = 1 
                         ORDER BY `subscription`.`id` DESC LIMIT 1) AS subscribeddays,
                        (SELECT `path` 
                         FROM `images` 
                         WHERE `images`.`course_id` = c.`id` 
                         AND `iv_category` = 'image' 
                         LIMIT 1) AS image_path
                        FROM `courses` c 
                        WHERE `status` = 1 
                        AND `title` LIKE '%$value%' 
                        ORDER BY `created_at` DESC";

        $result = $mysqli->query($coursesQuery);

        if ($result) {
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }

            echo json_encode([
                "message" => "success",
                "courses" => $courses
            ]);
        } else {
            echo json_encode(["message" => "some_error_occurred"]);
        }
    } else {
        echo json_encode(["message" => "Auth_token_failure"]);
    }
}

// Usage example:
$headers = getallheaders(); // Get headers
$query = $_GET; // Get query params

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'getCoursesByCategory') {
        getCoursesByCategory($mysqli, $headers, $query);
    } elseif (isset($_GET['action']) && $_GET['action'] === 'getSearchedCourses') {
        getSearchedCourses($mysqli, $headers, $_GET['value'], $query);
    }
}
?>
