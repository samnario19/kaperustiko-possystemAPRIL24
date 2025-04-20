<?php
    include '../config/connection.php';
    
    $query = "SELECT table_number, receipt_number FROM que_orders";
    $result = $conn->query($query);

    $tableStatus = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tableStatus[$row['table_number']] = !empty($row['receipt_number']);
        }
    }

    header('Content-Type: application/json');
    echo json_encode($tableStatus);
?>
    
    
    