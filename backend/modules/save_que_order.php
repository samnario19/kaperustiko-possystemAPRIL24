<?php
include '../config/connection.php';

// Handle different POST actions
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Handle queuing order
    
    if (isset($data['saveQueOrder'])) {

        if (!isset($data['date'], $data['time'], $data['cashierName'], $data['itemsOrdered'], $data['totalAmount'], $data['amountPaid'], $data['change'], $data['order_take'], $data['table_number'], $data['waiterName'], $data['waiterCode'])) {
            echo json_encode(["error" => "Invalid input data"]);
            exit; // Stop execution if input is invalid
        }

        $receiptNumber = $data['receiptNumber'];
        $date = $data['date'];
        $time = $data['time'];
        $cashierName = $data['cashierName'];
        $waiterName = $data['waiterName']; // New variable for waiter name
        $waiterCode = $data['waiterCode']; // New variable for waiter code
        $itemsOrderedJson = json_encode($data['itemsOrdered']); // Encode modified itemsOrdered
        $totalAmount = $data['totalAmount'];
        $amountPaid = isset($data['amountPaid']) ? $data['amountPaid'] : null; // Allow null if not set
        $change = isset($data['change']) ? $data['change'] : null; // Allow null if not set
        $orderTake = $data['order_take'];
        $tableNumber = $data['table_number']; // New variable for table number
        $orderStatus = "pending"; // Default order status

        // Insert into que_orders
        $stmt = $conn->prepare("INSERT INTO que_orders (receipt_number, date, time, items_ordered, total_amount, amount_paid, amount_change, order_take, table_number, order_status, waiter_name, waiter_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiiisssss", $receiptNumber, $date, $time, $itemsOrderedJson, $totalAmount, $amountPaid, $change, $orderTake, $tableNumber, $orderStatus, $waiterName, $waiterCode);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["message" => "Order queued successfully"]);
        } else {
            echo json_encode(["error" => "Error queuing order: " . $stmt->error]);
        }
        $stmt->close();
    }
}
?>