<?php
require_once '../config/connection.php';
// Get the JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Prepare the SQL statement
$sql = "INSERT INTO remit_sales (cashier_name, total_sales, remit_date, remit_time, remit_shortage, remit_validation, cashier_shift, food_summary, remit_code, service_charge) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bind_param("sisssssssi", 
    $data['cashier_name'], 
    $data['total_sales'], 
    $data['remit_date'], 
    $data['remit_time'], 
    $data['remit_shortage'], 
    $data['remit_validation'], 
    $data['cashier_shift'], 
    $data['food_summary'], 
    $data['remit_code'], 
    $data['service_charge']
);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(["message" => "Data inserted successfully"]);
} else {
    echo json_encode(["error" => "Failed to insert data: " . $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

