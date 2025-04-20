<?php
include '../config/connection.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    error_log(print_r($input, true)); // Log the input for debugging

    // Extract the code and quantity from the input
    $code = $input['code'];
    $qty = $input['qty'];

    // Prepare the SQL statement to update the quantity
    $stmt = $conn->prepare("UPDATE `pos-menu` SET qty = ? WHERE code = ?");
    $stmt->bind_param("is", $qty, $code); // Assuming qty is an integer and code is a string

    // Execute the statement and check for success
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quantity updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
    }

    $stmt->close();
}

$conn->close();
?>