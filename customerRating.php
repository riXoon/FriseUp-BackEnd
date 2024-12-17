<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle CORS for all requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

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
        // Fetch reviews with product, user details, and formatted date
        $sql = "
        SELECT r.id, r.stars, r.review, 
               DATE_FORMAT(r.created_at, '%m-%d-%y') AS date, 
               CONCAT(u.firstName, ' ', u.lastName) AS name, 
               p.productName AS product
        FROM reviews r
        JOIN tblusercredential u ON r.user_id = u.id
        JOIN product p ON r.product_id = p.id
        ORDER BY r.created_at DESC
        ";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'reviews' => $reviews]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unsupported request method']);
        break;
}
?>
