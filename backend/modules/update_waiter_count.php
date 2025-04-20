<?php
include '../config/connection.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (isset($input['waiter_code'])) {
        $waiter_code = $input['waiter_code'];

        // Prepare and execute the update query
        $stmt = $conn->prepare("UPDATE `user-staff` SET order_count = order_count + 1 WHERE waiter_code = ?");
        $stmt->bind_param("s", $waiter_code);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Order count updated.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update order count.']);
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