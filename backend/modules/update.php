<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

include '../config/connection.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'updateTableNumber':
            // Get the POST data
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['receipt_number']) || !isset($data['new_table_number'])) {
                echo json_encode(['error' => 'Missing required parameters']);
                exit;
            }

            $receipt_number = $data['receipt_number'];
            $new_table_number = $data['new_table_number'];

            try {
                // Update the table number in que_orders table
                $stmt = $conn->prepare("UPDATE que_orders SET table_number = ? WHERE receipt_number = ?");
                $stmt->bind_param("ss", $new_table_number, $receipt_number);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Table number updated successfully']);
                } else {
                    echo json_encode(['error' => 'Failed to update table number']);
                }
                
                $stmt->close();
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['error' => 'No action specified']);
}

$conn->close();
?>