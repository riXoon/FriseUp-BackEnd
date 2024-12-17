<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'db_connect.php';
$objDb = new db_connect;
$conn = $objDb->connect();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // No need for product_id parameter if we want overall ratings
    $sql = "SELECT stars, COUNT(*) as count FROM reviews GROUP BY stars";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $starsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($starsData) {
        // Return grouped data for all products
        echo json_encode(['success' => true, 'stars' => $starsData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No reviews found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
