<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include '../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    $receipt_number = $data['receipt_number'];

    $sql = "DELETE FROM que_orders WHERE receipt_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $receipt_number);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
}
$conn->close();
?>
