<?php
require_once '../config/connection.php';

// Allow CORS
header("Access-Control-Allow-Origin: http://localhost:5173"); // Change this to your frontend URL
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Allow specific methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No content response
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the action from the request
    $action = $_GET['action'] ?? '';

    if ($action === 'editVoucher') {
        // Get the JSON data from the request
        $data = json_decode(file_get_contents("php://input"), true);

        // Extract the voucher details
        $voucher_id = $data['voucher_id'] ?? 0;
        $voucher_discount = $data['voucher_discount'] ?? 0;
        $voucher_deadline = $data['voucher_deadline'] ?? '';
        $voucher_description = $data['voucher_description'] ?? '';

        // Prepare the SQL statement to update the voucher using voucher_id
        $stmt = $conn->prepare("UPDATE vouchers SET voucher_discount = ?, voucher_deadline = ?, voucher_description = ? WHERE voucher_id = ?");
        $stmt->bind_param("issi", $voucher_discount, $voucher_deadline, $voucher_description, $voucher_id);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update voucher.']);
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>