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
        // Fetch all transactions
        $searchQuery = isset($_GET['query']) ? $_GET['query'] : '';

        if (!empty($searchQuery)) {
            // Search transactions by first name, last name, or ID
            $sql = "SELECT * FROM tbltransaction
                    WHERE firstName LIKE :query OR lastName LIKE :query OR id LIKE :query";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':query', '%' . $searchQuery . '%', PDO::PARAM_STR);
        } else {
            // Fetch all transactions
            $sql = "SELECT * FROM tbltransaction";
            $stmt = $conn->prepare($sql);
        }
        $stmt->execute();
        $transaction = $stmt->fetchAll(PDO::FETCH_ASSOC); // Correct constant syntax
        // Calculate the sum of totalPrice (total earnings)
        $earningsSql = "SELECT SUM(totalPrice) AS totalEarnings FROM tbltransaction";
        $earningsStmt = $conn->prepare($earningsSql);
        $earningsStmt->execute();
        $earningsResult = $earningsStmt->fetch(PDO::FETCH_ASSOC);
        $totalEarnings = $earningsResult['totalEarnings'];

        // Calculate the sum of items (total orders)
        $ordersSql = "SELECT SUM(items) AS totalOrders FROM tbltransaction";
        $ordersStmt = $conn->prepare($ordersSql);
        $ordersStmt -> execute();
        $ordersResult = $ordersStmt->fetch(PDO::FETCH_ASSOC);
        $totalOrders = $ordersResult['totalOrders'];

        $customersSql = "SELECT COUNT(id) AS totalCustomers FROM tbltransaction";
        $customersStmt = $conn->prepare($customersSql);
        $customersStmt -> execute();
        $customersResult = $customersStmt->fetch(PDO::FETCH_ASSOC);
        $totalCustomers = $customersResult['totalCustomers'];

        // Send both the transactions and total earnings
        echo json_encode([
            'transactions' => $transaction, 
            'totalEarnings' => $totalEarnings,
            'totalOrders' => $totalOrders,
             'totalCustomers' => $totalCustomers
        ]);
        break;
        
        case "POST":
            $user = json_decode(file_get_contents('php://input'));
        
            // Set the timezone to Philippines (UTC+8)
            date_default_timezone_set('Asia/Manila');
        
            // Prepare SQL query to insert transaction
            $sql = "INSERT INTO tbltransaction(firstName, lastName, date, items, itemsqty, totalPrice, payment, promoCode, salesperson) 
                    VALUES(:firstName, :lastName, :date, :items, :itemsqty, :totalPrice, :payment, :promoCode, :salesperson)";
            $stmt = $conn->prepare($sql);
        
            $date = date('Y-m-d H:i:s');
        
            // Bind parameters
            $stmt->bindParam(':firstName', $user->firstName);
            $stmt->bindParam(':lastName', $user->lastName);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':items', $user->items); // Total quantity of all items
            $stmt->bindParam(':itemsqty', json_encode($user->itemsqty)); // Serialize itemsqty as JSON
            $stmt->bindParam(':totalPrice', $user->totalPrice);
            $stmt->bindParam(':payment', $user->payment);
            $promoCode = isset($user->promoCode) ? $user->promoCode : null;
            $stmt->bindParam(':promoCode', $promoCode);
            $stmt->bindParam(':salesperson', $user->salesperson);
        
            try {
                if ($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Record created successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to create record.'];
                }
            } catch (PDOException $e) {
                $response = ['status' => 0, 'message' => 'Database error: ' . $e->getMessage()];
            }
            echo json_encode($response);
            break;
        
        

    try {
        if ($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record created successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to create record.'];
        }
    } catch (PDOException $e) {
        $response = ['status' => 0, 'message' => 'Database error: ' . $e->getMessage()];
    }
    echo json_encode($response);
}
?>
