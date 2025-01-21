<?php
// Include the database connection file
include('conn.php');

header('Content-Type: application/json');

if (isset($_SERVER['HTTP_TOKEN'])) {
    $data = $_GET;
    $share_url = '';

    if (isset($data['course_id'])) {
        $course_id = $mysqli->real_escape_string($data['course_id']);
        $user_id = $mysqli->real_escape_string($data['user_id']);

        $subscription_query = "
            SELECT DATEDIFF(end_date, CURRENT_TIMESTAMP) AS subscribeddays, `subscription`.`status`, `subscription`.`id` AS subscription_id
            FROM `subscription`
            INNER JOIN `users` ON `users`.`id` = `subscription`.`user_id`
            WHERE `users`.`id` = '$user_id' AND `subscription`.`course_id` = '$course_id'
            ORDER BY `subscription`.`id` DESC
            LIMIT 1";

        $videos_query = "
            SELECT `name`, `path`, `is_full_screen`
            FROM `images`
            WHERE `course_id` = '$course_id' AND `iv_category` = 'video'
            ORDER BY `name`";

        $pdf_query = "
            SELECT `pdflink`
            FROM `recipies`
            WHERE `course_id` = '$course_id'";

        $course_query = "
            SELECT *, 
                (SELECT `path` FROM `images` WHERE `images`.`course_id` = `courses`.`id` AND `iv_category` = 'image' LIMIT 1) AS image_path,
                (SELECT COUNT(*) FROM `cart` WHERE `cart_category` IS NULL AND `course_id` = '$course_id') AS cartcount
            FROM `courses`
            WHERE `status` = 1 AND `id` = '$course_id'";

        $course_result = $mysqli->query($course_query);

        if ($course_result) {
            $course = $course_result->fetch_assoc();

            $subscription_result = $mysqli->query($subscription_query);
            $subscription = $subscription_result ? $subscription_result->fetch_assoc() : null;

            $videos_result = $mysqli->query($videos_query);
            $videos = $videos_result ? $videos_result->fetch_all(MYSQLI_ASSOC) : [];

            $pdf_result = $mysqli->query($pdf_query);
            $pdf = $pdf_result ? $pdf_result->fetch_all(MYSQLI_ASSOC) : [];

            if (empty($course['share_url'])) {
                // Generate dynamic link (mock implementation)
                $course['share_url'] = "https://example.com/courses_api/getEachCourse?course_id=$course_id";

                $update_share_url_query = "
                    UPDATE `courses` 
                    SET `share_url` = '{$course['share_url']}'
                    WHERE `id` = '$course_id'";
                $mysqli->query($update_share_url_query);
            }

            if ($subscription) {
                if ($subscription['subscribeddays'] <= 0) {
                    $status_update_query = "
                        UPDATE `subscription` 
                        SET `status` = 0 
                        WHERE `id` = '{$subscription['subscription_id']}'";
                    $mysqli->query($status_update_query);

                    echo json_encode([
                        "message" => "no_subscription_available",
                        "course" => $course,
                        "pdf" => $pdf,
                        "videos" => $videos,
                        "subscribeddays" => 0,
                        "issubscribed" => $course['category'] === 'free',
                        "show_popup" => true,
                        "shareText" => "{$course['title']} Watch promo here {$course['promo_video']} \n\n Explore more courses: {$course['share_url']}"
                    ]);
                } else {
                    echo json_encode([
                        "message" => "subscription_available",
                        "course" => $course,
                        "pdf" => $pdf,
                        "videos" => $videos,
                        "subscribeddays" => $subscription['subscribeddays'],
                        "issubscribed" => true,
                        "show_popup" => false,
                        "shareText" => "{$course['title']} Watch promo here {$course['promo_video']} \n\n Explore more courses: {$course['share_url']}"
                    ]);
                }
            } else {
                echo json_encode([
                    "message" => "no_subscription_available",
                    "course" => $course,
                    "pdf" => $pdf,
                    "videos" => $videos,
                    "subscribeddays" => 0,
                    "issubscribed" => $course['category'] === 'free',
                    "show_popup" => false,
                    "shareText" => "{$course['title']} Watch promo here {$course['promo_video']} \n\n Explore more courses: {$course['share_url']}"
                ]);
            }
        } else {
            echo json_encode(["message" => "some_error_occurred"]);
        }
    } else {
        echo json_encode(["message" => "some_error_occurred"]);
    }
} else {
    echo json_encode(["message" => "Auth_token_failure"]);
}

$mysqli->close();
?>
