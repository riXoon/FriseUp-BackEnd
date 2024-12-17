<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle CORS for all requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

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
    case "POST":
        $data = json_decode(file_get_contents('php://input'));

        $massage = $data->massage ?? null;
        $name = $data->name ?? null;
        $email = $data->email ?? null;
        $phone = $data->phone ?? null;

        if (!$massage || !$name || !$email || !$phone) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            break;
        }

        // Modify the SQL query to exclude `id`
        $sql = "INSERT INTO report (massage, name, email, phone) VALUES (:massage, :name, :email, :phone)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':massage', $massage, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit report']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

        case "GET":
            $sql = "SELECT id, massage, name, email, phone FROM report";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $reports]);
            break;

        
}
