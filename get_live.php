<?php
// Include the database connection file
include('conn.php');

// Check if `user_id` is provided
if (!isset($_GET['user_id'])) {
    echo json_encode(['status' => false, 'message' => 'user_id is required']);
    exit();
}

$user_id = $_GET['user_id'];
$live_id = isset($_GET['live_id']) ? $_GET['live_id'] : null;

$query = "";
if ($live_id) {
    $query = "
        SELECT 
            c.*, 
            `courses`.`id` AS course_id,
            CAST((SELECT COUNT(*) FROM `live_subscription` 
             WHERE `live_subscription`.`live_id` = c.`id` 
             AND `live_subscription`.`user_id` = '$user_id') AS SIGNED) AS subscribed,
            CAST((SELECT COUNT(*) FROM `live_subscription` 
             WHERE `live_subscription`.`live_id` = c.`id`) AS SIGNED) AS live_users_count 
        FROM 
            `live` c 
        INNER JOIN 
            `courses` ON `courses`.`live_id` = c.`id` 
        WHERE 
            c.`status` = 1 
            AND c.`id` = '$live_id' 
        ORDER BY 
            `live_date`";
} else {
    $query = "
        SELECT 
            c.*, 
            `courses`.`id` AS course_id,
            CAST((SELECT COUNT(*) FROM `live_subscription` 
             WHERE `live_subscription`.`live_id` = c.`id` 
             AND `live_subscription`.`user_id` = '$user_id') AS SIGNED) AS subscribed,
            CAST((SELECT COUNT(*) FROM `live_subscription` 
             WHERE `live_subscription`.`live_id` = c.`id`) AS SIGNED) AS live_users_count 
        FROM 
            `live` c 
        INNER JOIN 
            `courses` ON `courses`.`live_id` = c.`id` 
        WHERE 
            c.`status` = 1 
        ORDER BY 
            `live_date`";
}

// Execute the query
$result = $mysqli->query($query);

if ($result) {
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // Ensure numeric fields are cast to appropriate types
    foreach ($data as &$row) {
        $row['subscribed'] = (int) $row['subscribed'];
        $row['live_users_count'] = (int) $row['live_users_count'];
    }

    echo json_encode(['status' => true, 'data' => $data]);
} else {
    echo json_encode(['status' => false, 'message' => 'Query failed', 'error' => $mysqli->error]);
}
?>
