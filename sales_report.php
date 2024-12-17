<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle CORS for all requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

include 'db_connect.php';
$objDb = new db_connect;
$conn = $objDb->connect();

// Fetching the date from query parameters
$selectedDate = $_GET['startDate'] ?? null;

if (!$selectedDate) {
    echo json_encode([
        'status' => 0,
        'message' => 'No date provided.',
        'data' => [],
        'totalRevenue' => 0
    ]);
    exit;
}

// Ensure valid date format for SQL query
$selectedDate = date('Y-m-d', strtotime($selectedDate));

// SQL query to fetch data for the selected date
$sql = "
    SELECT
        JSON_UNQUOTE(JSON_EXTRACT(t.itemsqty, '$[*].name')) AS productNames,
        JSON_UNQUOTE(JSON_EXTRACT(t.itemsqty, '$[*].quantity')) AS quantities,
        SUM(t.totalPrice) AS revenue
    FROM tbltransaction t
    WHERE DATE(t.date) = :selectedDate
    GROUP BY t.id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':selectedDate', $selectedDate);

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if data is empty and return the appropriate response
if (empty($data)) {
    echo json_encode([
        'status' => 0,
        'message' => 'No data found for the selected date.',
        'data' => [],
        'totalRevenue' => 0
    ]);
    exit;
}

// Process data
$productData = [];
$totalRevenue = 0;

foreach ($data as $row) {
    $productNames = $row['productNames'] ? json_decode($row['productNames'], true) : [];
    $quantities = $row['quantities'] ? json_decode($row['quantities'], true) : [];

    if ($productNames && $quantities) {
        foreach ($productNames as $index => $productName) {
            $quantity = $quantities[$index] ?? 0;
            if (!isset($productData[$productName])) {
                $productData[$productName] = ['purchases' => 0, 'revenue' => 0];
            }
            $productData[$productName]['purchases'] += $quantity;
            $productData[$productName]['revenue'] += $row['revenue'];
        }
    }
    $totalRevenue += $row['revenue'];
}

$response = [
    'status' => 1,
    'data' => $productData,
    'totalRevenue' => $totalRevenue,
];

echo json_encode($response);
?>
