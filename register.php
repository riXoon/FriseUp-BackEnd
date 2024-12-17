<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db_connect.php';
$objDb = new db_connect;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "POST":
        $user = json_decode(file_get_contents('php://input'));

        // Check if email already exists
        $checkEmailSql = "SELECT COUNT(*) FROM tblusercredential WHERE email = :email";
        $checkStmt = $conn->prepare($checkEmailSql);
        $checkStmt->bindParam(':email', $user->email);
        $checkStmt->execute();
        $emailExists = $checkStmt->fetchColumn();

        if ($emailExists > 0) {
            $response = ['status' => 0, 'message' => 'Email is already registered.'];
            echo json_encode($response);
            exit(); // Stop further execution
        }

        // Hash the password before storing it in the database
        $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);

        // Prepare the SQL query to insert user data
        $sql = "INSERT INTO tblusercredential(firstName, lastName, password, email, contactNum, created_at, role) VALUES(:firstName, :lastName, :password, :email, :contactNum, :created_at, :role)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');

        // Bind parameters
        $stmt->bindParam(':firstName', $user->firstName);
        $stmt->bindParam(':lastName', $user->lastName);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':contactNum', $user->contactNum);
        $stmt->bindParam(':created_at', $created_at);
        $stmt->bindParam(':role', $user->role);

        // Execute the query
        if ($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record created successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to create record.'];
        }

        echo json_encode($response);
        break;
}
?>