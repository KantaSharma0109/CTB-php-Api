<?php
// Include the database connection file
include('conn.php');

// Function to get cart data
function getCartData($coursecart = [], $productCart = [], $bookCart = [], $bookVideosCart = [], $coupons = [], $userWallet = [], $couponId = null) {
    // Initialize sums for each category
    $coursesum = $productsum = $booksum = $bookvideossum = 0;
    
    // Sum for each cart category
    foreach ($coursecart as $cart) {
        $coursesum += $cart['totalAmount'];
    }
    foreach ($productCart as $cart) {
        $productsum += $cart['totalAmount'];
    }
    foreach ($bookCart as $cart) {
        $booksum += $cart['totalAmount'];
    }
    foreach ($bookVideosCart as $cart) {
        $bookvideossum += $cart['totalAmount'];
    }

    // Calculate total sum of all categories
    $sumTotal = $coursesum + $productsum + $booksum + $bookvideossum;
    $paybleAmount = 0 + (count($productCart) != 0 ? (count(array_filter($productCart, fn($element) => $element['pincode'] != '311001')) != 0 ? 90 : 0) : 0);
    
    $availableCoupons = [];
    $cartvisetotal = null;
    $courseSingleCouponTotal = [];
    $productSingleCouponTotal = [];
    $bookSingleCouponTotal = [];
    $courseIds = array_map(fn($element) => (string) $element['item_id'], $coursecart);
    $productIds = array_map(fn($element) => (string) $element['item_id'], $productCart);
    $bookIds = array_map(fn($element) => (string) $element['item_id'], $bookCart);
    
    // Cartvise coupon logic
    $cartVise = array_filter($coupons, fn($coupon) => $coupon['linked_category'] === 'cartvise');
    if ($cartVise) {
        $cartVise = $cartVise[0]; // assuming only one cartvise coupon
        if ($sumTotal >= $cartVise['minimum'] && $sumTotal <= $cartVise['maximum']) {
            $cartvisetotal = [
                'amount' => $sumTotal - ($sumTotal * $cartVise['dis'] / 100),
                'couponName' => $cartVise['ccode'],
                'isApplied' => true,
                'id' => $cartVise['id'],
                'discount' => $cartVise['dis'],
                'category' => $cartVise['linked_category'],
            ];
            $availableCoupons[] = $cartvisetotal;
        } else {
            $cartvisetotal = [
                'amount' => $sumTotal,
                'couponName' => $cartVise['ccode'],
                'isApplied' => false,
                'id' => $cartVise['id'],
                'discount' => $cartVise['dis'],
                'category' => $cartVise['linked_category'],
            ];
        }
    }

    // Coupon application for courses, products, and books
    foreach ($coupons as $coupon) {
        $linkedArray = explode(',', $coupon['linked_array']);
        if ($coupon['linked_category'] == 'course') {
            // Single or multiple course coupon logic
            $courseSum = 0;
            foreach ($coursecart as $course) {
                if (in_array((string) $course['item_id'], $linkedArray)) {
                    $courseSum += ($course['discount_price'] - (($course['discount_price'] * $coupon['dis']) / 100)) * $course['quantity'];
                } else {
                    $courseSum += $course['discount_price'] * $course['quantity'];
                }
            }
            $courseSingleCouponTotal[] = [
                'amount' => $courseSum,
                'couponName' => $coupon['ccode'],
                'isApplied' => $courseSum < $coursesum,
                'id' => $coupon['id'],
                'discount' => $coupon['dis'],
                'category' => $coupon['linked_category'],
            ];
        } elseif ($coupon['linked_category'] == 'product') {
            // Single or multiple product coupon logic
            $productSum = 0;
            foreach ($productCart as $product) {
                if (in_array((string) $product['item_id'], $linkedArray)) {
                    $productSum += ($product['discount_price'] - (($product['discount_price'] * $coupon['dis']) / 100)) * $product['quantity'];
                } else {
                    $productSum += $product['discount_price'] * $product['quantity'];
                }
            }
            $productSingleCouponTotal[] = [
                'amount' => $productSum,
                'couponName' => $coupon['ccode'],
                'isApplied' => $productSum < $productsum,
                'id' => $coupon['id'],
                'discount' => $coupon['dis'],
                'category' => $coupon['linked_category'],
            ];
        } elseif ($coupon['linked_category'] == 'book') {
            // Single or multiple book coupon logic
            $bookSum = 0;
            foreach ($bookCart as $book) {
                if (in_array((string) $book['item_id'], $linkedArray)) {
                    $bookSum += ($book['discount_price'] - (($book['discount_price'] * $coupon['dis']) / 100)) * $book['quantity'];
                } else {
                    $bookSum += $book['discount_price'] * $book['quantity'];
                }
            }
            $bookSingleCouponTotal[] = [
                'amount' => $bookSum,
                'couponName' => $coupon['ccode'],
                'isApplied' => $bookSum < $booksum,
                'id' => $coupon['id'],
                'discount' => $coupon['dis'],
                'category' => $coupon['linked_category'],
            ];
        }
    }

    // Find the minimum price among available coupons
    $minPrice = $sumTotal;
    $finalCoupon = [];
    if (!empty($availableCoupons)) {
        if ($couponId) {
            $minPrice = min(array_column($availableCoupons, 'totalAmount'));
        } else {
            $minPrice = min(array_column($availableCoupons, 'totalAmount'));
        }
        $finalCoupon = array_filter($availableCoupons, fn($coupon) => $coupon['totalAmount'] == $minPrice);
    }

    $paybleAmount += $minPrice;
    $appliedCoupon = !empty($finalCoupon) ? $finalCoupon[0]['id'] : '';

    // Return the results
    return [
        'coursecart' => $coursecart,
        'productCart' => $productCart,
        'bookCart' => $bookCart,
        'bookVideosCart' => $bookVideosCart,
        'courseSingleCouponTotal' => $courseSingleCouponTotal,
        'productSingleCouponTotal' => $productSingleCouponTotal,
        'bookSingleCouponTotal' => $bookSingleCouponTotal,
        'coursesum' => $coursesum,
        'productsum' => $productsum,
        'booksum' => $booksum,
        'bookvideossum' => $bookvideossum,
        'sumTotal' => $sumTotal,
        'cartvisetotal' => $cartvisetotal,
        'paybleAmount' => $paybleAmount,
        'availableCoupons' => $availableCoupons,
        'appliedCouponId' => $couponId ? $couponId : $appliedCoupon,
        'shippingCharges' => (count($productCart) != 0 && count(array_filter($productCart, fn($element) => $element['pincode'] != '311001')) != 0 ? 90 : 0),
        'wallet' => count($userWallet) != 0 ? $userWallet[0]['wallet'] : 0,
    ];
}

// API to get user cart data
// if (isset($_SERVER['HTTP_TOKEN'])) {
  $data = $_GET;



  $user_id = $mysqli->real_escape_string($data['user_id']);
  $couponId = isset($data['couponId']) ? $mysqli->real_escape_string($data['couponId']) : null;

  $couponsQuery = "SELECT * FROM `coupon` WHERE `status` = 1";
  $userWalletQuery = "SELECT `wallet` FROM `users` WHERE `id` = '$user_id'";
  $courseCartQuery = "
      SELECT c.`id`, c.`category`, c.`cart_category`, c.`course_id` AS item_id, c.`quantity`, 
             b.`title` AS name, b.`discount_price`, b.`image_path`, 
             SUM(b.`discount_price` * c.`quantity`) AS totalAmount 
      FROM `cart` c 
      JOIN (
          SELECT `title`, `discount_price`, `id`, 
                 (SELECT `path` FROM `images` WHERE `images`.`course_id` = `courses`.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
          FROM `courses`
      ) b ON b.`id` = c.`course_id` 
      WHERE `user_id` = '$user_id' AND `cart_category` IS NULL AND `category` = 'course' 
      GROUP BY c.`id`";
  $productCartQuery = "
      SELECT c.`id`, c.`category`, c.`cart_category`, c.`product_id` AS item_id, c.`quantity`, c.`address`, 
             c.`description`, c.`pincode`, c.`image_path`, b.`name`, b.`discount_price`, 
             SUM(b.`discount_price` * c.`quantity`) AS totalAmount 
      FROM `cart` c 
      JOIN (
          SELECT `name`, `discount_price`, `id` FROM `products`
      ) b ON b.`id` = c.`product_id` 
      WHERE `user_id` = '$user_id' AND `cart_category` IS NULL AND `category` = 'product' 
      GROUP BY c.`id`";
  $bookCartQuery = "
      SELECT c.`id`, c.`category`, c.`cart_category`, c.`book_id` AS item_id, c.`quantity`, 
             b.`title` AS name, b.sub_category, b.`discount_price`, b.`image_path`, 
             SUM(b.`discount_price` * c.`quantity`) AS totalAmount 
      FROM `cart` c 
      JOIN (
          SELECT `title`, `discount_price`, `id`, `category` AS sub_category, 
                 (SELECT `path` FROM `images` WHERE `images`.`book_id` = `books`.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
          FROM `books`
      ) b ON b.`id` = c.`book_id` 
      WHERE `user_id` = '$user_id' AND `cart_category` IS NULL AND `category` = 'book' 
      GROUP BY c.`id`";
  $bookVideosCartQuery = "
      SELECT c.`id`, c.`category`, c.`cart_category`, c.`book_id` AS item_id, c.`quantity`, 
             b.`title` AS name, b.`discount_price`, b.`image_path`, 
             SUM(b.`discount_price` * c.`quantity`) AS totalAmount 
      FROM `cart` c 
      JOIN (
          SELECT `title`, `discount_price`, `id`, 
                 (SELECT `path` FROM `images` WHERE `images`.`book_id` = `books`.`id` AND `iv_category` = 'image' LIMIT 1 OFFSET 0) AS image_path 
          FROM `books`
      ) b ON b.`id` = c.`book_id` 
      WHERE `user_id` = '$user_id' AND `cart_category` IS NULL AND `category` = 'book-videos' 
      GROUP BY c.`id`";

  $result = [];
  $result['coupons'] = $mysqli->query($couponsQuery)->fetch_all(MYSQLI_ASSOC);
  $result['userWallet'] = $mysqli->query($userWalletQuery)->fetch_assoc();
  $result['courseCart'] = $mysqli->query($courseCartQuery)->fetch_all(MYSQLI_ASSOC);
  $result['productCart'] = $mysqli->query($productCartQuery)->fetch_all(MYSQLI_ASSOC);
  $result['bookCart'] = $mysqli->query($bookCartQuery)->fetch_all(MYSQLI_ASSOC);
  $result['bookVideosCart'] = $mysqli->query($bookVideosCartQuery)->fetch_all(MYSQLI_ASSOC);

  // Assuming getCartData() is a PHP function you'd define to calculate or modify the final cart result
  if (function_exists('getCartData')) {
      $cartData = getCartData(
          $result['courseCart'],
          $result['productCart'],
          $result['bookCart'],
          $result['bookVideosCart'],
          $result['coupons'],
          $result['userWallet'],
          $couponId
      );
      echo json_encode($cartData);
  } else {
      echo json_encode($result);
  }
// } else {
//   echo json_encode([
//       "message" => "Auth_token_failure"
//   ]);
// }
?>
