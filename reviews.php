<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle CORS for all requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true"); // Add this if using cookies/auth tokens

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    exit(0);
}

include 'db_connect.php';
$objDb = new db_connect;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        // Fetch reviews for a specific product (if product_id is provided)
        if (isset($_GET['product_id'])) {
            $product_id = $_GET['product_id'];
            
            // Fetch reviews for the specific product
            $sql = "SELECT r.id, r.stars, r.review, r.created_at AS date, u.firstName, u.lastName 
            FROM reviews r
            JOIN tblusercredential u ON r.user_id = u.id 
            WHERE r.product_id = ?
            ORDER BY r.created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $product_id, PDO::PARAM_INT);  // Bind the product_id using position
            $stmt->execute();
    
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'reviews' => $reviews]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        }
        break;

    case "POST":
        // Insert a new review
        $data = json_decode(file_get_contents('php://input'));

        $user_id = $data->user_id ?? null;
        $product_id = $data->product_id ?? null;  // Added product_id
        $stars = $data->stars ?? null;
        $review = $data->review ?? null;

        if (!isset($user_id, $product_id, $stars, $review) || empty(trim($review))) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            break;
        }

        // Check if product_id exists in the product table
        $sql = "SELECT COUNT(*) FROM product WHERE id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product_exists = $stmt->fetchColumn();

        if (!$product_exists) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            break;
        }

        // Insert the review
        $sql = "INSERT INTO reviews (user_id, product_id, stars, review) VALUES (:user_id, :product_id, :stars, :review)";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);  // Bind product_id
        $stmt->bindParam(':stars', $stars, PDO::PARAM_INT);
        $stmt->bindParam(':review', $review, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        // Handle unsupported methods
        echo json_encode(['success' => false, 'message' => 'Unsupported request method']);
        break;
}
