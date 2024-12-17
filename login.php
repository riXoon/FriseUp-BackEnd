<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow requests from all origins (be cautious in production)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond OK for OPTIONS
    exit();
}

include 'db_connect.php';
$objDb = new db_connect;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case "POST":
        $user = json_decode(file_get_contents('php://input'));

        // Check if the required data exists
        if (!isset($user->email) || !isset($user->password) || !isset($user->role)) {
            $response = ['status' => 0, 'message' => 'Email, password, and role are required fields.'];
            echo json_encode($response);
            exit();
        }

        // Prepare query to check if the user exists and retrieve role
        $sql = "SELECT id, email, role, password FROM tblusercredential WHERE email = :email AND role = :role";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':role', $user->role);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Verify password if user exists
            if (password_verify($user->password, $result['password'])) {
                // Include the user's ID in the response
                $response = [
                    'status' => 1,
                    'message' => 'Login successful.',
                    'role' => $result['role'],
                    'id' => $result['id'] // Return user ID
                ];
            } else {
                $response = ['status' => 0, 'message' => 'Invalid password.'];
            }
        } else {
            $response = ['status' => 0, 'message' => 'User not found or role mismatch.'];
        }
        echo json_encode($response);
        break;

    default:
        // Handle invalid method
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 0, 'message' => 'Invalid request method.']);
        break;
}
