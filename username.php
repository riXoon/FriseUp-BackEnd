<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow CORS for all origins
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db_connect.php';
$objDb = new db_connect;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case "GET":
        // Get the user ID from the query string
        $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;

        if ($userId > 0) {
            $sql = "SELECT firstName, lastName FROM tblusercredential WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $response = [
                    'status' => 1,
                    'data' => $user,
                ];
            } else {
                $response = [
                    'status' => 0,
                    'message' => 'User not found.',
                ];
            }
        } else {
            $response = [
                'status' => 0,
                'message' => 'Invalid user ID.',
            ];
        }

        echo json_encode($response);
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 0, 'message' => 'Invalid request method.']);
        break;
}
