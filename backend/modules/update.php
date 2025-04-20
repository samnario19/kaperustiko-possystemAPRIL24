<?php
include '../config/connection.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    error_log(print_r($input, true)); // Log the input for debugging

    // Extract variables
    $receipt_number = $input['receipt_number'] ?? null;
    $order_status = $input['order_status'] ?? null;

    // Validate input
    if ($receipt_number && $order_status) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("UPDATE que_orders SET order_status = ? WHERE que_order_no = ?");
        $stmt->bind_param("si", $order_status, $receipt_number);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Order status updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update order status.', 'error' => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>