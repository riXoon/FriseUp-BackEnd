<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

include 'db_connect.php';
$objDb = new db_connect;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "POST") {
    $data = json_decode(file_get_contents('php://input'));

    $reportId = $data->report_id ?? null;
    $reply = $data->reply ?? null;
    $sender = $data->sender ?? 'Admin';  // Assuming the sender is Admin by default
    $recipient = $data->recipient ?? null;  // The recipient of the reply/notification
    
    // Check if both report_id and reply are provided
    if (!$reportId || !$reply) {
        echo json_encode(['success' => false, 'message' => 'Report ID and reply are required']);
        exit;
    }

    try {
        // Insert the reply into the replies table
        $sql = "INSERT INTO replies (report_id, reply) VALUES (:reportId, :reply)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);
        $stmt->bindParam(':reply', $reply, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Get the recipient from the report table if not provided
            if (!$recipient) {
                $sqlRecipient = "SELECT email FROM report WHERE id = :reportId";
                $stmtRecipient = $conn->prepare($sqlRecipient);
                $stmtRecipient->bindParam(':reportId', $reportId, PDO::PARAM_INT);
                $stmtRecipient->execute();
                $recipientData = $stmtRecipient->fetch(PDO::FETCH_ASSOC);
                $recipient = $recipientData['email'];  // Set the recipient from the report
            }

            // Insert a notification for the reply
            $notificationMessage = " " . substr($reply, 0, 100) . "...";  // Preview the reply
            $sqlNotification = "INSERT INTO notifications (sender, recipient, message, reply, time) 
                                VALUES (:sender, :recipient, :message, :reply, NOW())";
            $stmtNotification = $conn->prepare($sqlNotification);
            $stmtNotification->bindParam(':sender', $sender, PDO::PARAM_STR);
            $stmtNotification->bindParam(':recipient', $recipient, PDO::PARAM_STR);
            $stmtNotification->bindParam(':message', $notificationMessage, PDO::PARAM_STR);
            $stmtNotification->bindParam(':reply', $reply, PDO::PARAM_STR);

            if ($stmtNotification->execute()) {
                echo json_encode(['success' => true, 'message' => 'Reply stored and notification sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send notification']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to store reply']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
