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
        try {
            // Query to calculate sales, customers, and orders grouped by month
            $sql = "
                SELECT 
                    DATE_FORMAT(date, '%b') AS month,
                    SUM(totalPrice) AS sales, 
                    COUNT(DISTINCT id) AS customers, 
                    SUM(items) AS orders
                FROM tbltransaction 
                GROUP BY MONTH(date)
                ORDER BY MONTH(date);

            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $analyticsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Return the result as JSON
            header('Content-Type: application/json');
            echo json_encode($analyticsData);
        } catch (PDOException $e) {
            // Handle any database connection or query errors
            $error = ['status' => 0, 'message' => 'Database error: ' . $e->getMessage()];
            header('Content-Type: application/json');
            echo json_encode($error);
        }
        break;

    // Other HTTP methods (POST, PUT, DELETE) can be handled here if needed
    default:
        $error = ['status' => 0, 'message' => 'Invalid request method'];
        header('Content-Type: application/json');
        echo json_encode($error);
        break;
}
?>
