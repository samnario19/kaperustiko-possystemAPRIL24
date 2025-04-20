<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include '../config/connection.php'; // Include the database connection

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // Exit for preflight requests
}

// Get the JSON input
$data = json_decode(file_get_contents("php://input"));

// Check if newPassword is provided
if (isset($data->newPassword) && isset($data->waiterCode)) {
    $newPassword = $data->newPassword;
    $waiterCode = $data->waiterCode; // Use waiter_code instead of staff_token

    // Hash the new password for security
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    $stmt = $conn->prepare("UPDATE `user-staff` SET password = ? WHERE waiter_code = ?"); // Update query using waiter_code
    $stmt->bind_param("ss", $hashedPassword, $waiterCode);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Password updated successfully."]);
    } else {
        echo json_encode(["message" => "Error updating password."]);
    }

    $stmt->close();
} else {
    echo json_encode(["message" => "New password or waiter code not provided."]);
}

$conn->close();
?>