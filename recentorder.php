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
switch($method) {
    case "GET":
        try {
            // Fetch the 5 most recent records
            $sql = "SELECT id, firstName, lastName, date, totalPrice, items FROM tbltransaction ORDER BY date DESC LIMIT 5";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Return the result as JSON
            header('Content-Type: application/json');
            echo json_encode($recentOrders);
        } catch (PDOException $e) {
            // Handle any database connection or query errors
            $error = ['status' => 0, 'message' => 'Database error: ' . $e->getMessage()];
            header('Content-Type: application/json');
            echo json_encode($error);
        }
        break;

    // Other HTTP methods (POST, PUT, DELETE) can be handled here if needed
}
