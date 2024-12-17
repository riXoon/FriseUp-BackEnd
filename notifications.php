<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

include 'db_connect.php';
$objDb = new db_connect;
$conn = $objDb->connect();

// Check the database connection
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Fetch the notifications (consider adding a WHERE clause to filter by user ID)
$sql = "SELECT * FROM notifications ORDER BY time DESC";  // Adjust this to get the notifications correctly
$stmt = $conn->prepare($sql);

try {
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $notificationCount = count($notifications);

    if ($notifications) {
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'count' => $notificationCount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No notifications found',
            'count' => 0
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error executing query: ' . $e->getMessage()
    ]);
}
?>
