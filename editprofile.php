<?php
header("Content-Type: application/json");

// Include database connection
require_once 'db_connect.php';

// Check the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Fetch user data
    $userId = 1; // Replace with session or authentication logic
    $sql = "SELECT name, email, contact_number, profile_pic FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        echo json_encode($userData);
    } else {
        echo json_encode(["error" => "User not found"]);
    }
} elseif ($method == 'PUT') {
    // Update user data
    $input = json_decode(file_get_contents("php://input"), true);

    $name = $input['name'];
    $email = $input['email'];
    $contactNumber = $input['contactNumber'];
    $profilePic = $input['profilePic'];
    $userId = 1; // Replace with session or authentication logic

    $sql = "UPDATE users SET name = ?, email = ?, contact_number = ?, profile_pic = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $contactNumber, $profilePic, $userId);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Profile updated successfully"]);
    } else {
        echo json_encode(["error" => "Failed to update profile"]);
    }
} else {
    // Invalid method
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Invalid HTTP method"]);
}

// Close connection
$conn->close();
?>
