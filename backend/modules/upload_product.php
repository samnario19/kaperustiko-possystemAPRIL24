<?php
include '../config/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $code = $_POST['code'];
    $title1 = $_POST['title1'];
    $title2 = $_POST['title2'];
    $label = $_POST['label'];
    $price1 = $_POST['price1'];
    $price2 = $_POST['price2'];
    $price3 = $_POST['price3'];
    $qty = $_POST['qty'];
    $image = $_POST['image'] ?? 'image.jpg'; // Set default to 'image.jpg' if not provided
    $currentDate = date('Y-m-d'); // Get current date
    $currentTime = date('H:i:s'); // Get current time
    
    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO `pos-menu` (code, title1, title2, label, price1, price2, price3, qty, image, stock_date, stock_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiiissss", $code, $title1, $title2, $label, $price1, $price2, $price3, $qty, $image, $currentDate, $currentTime);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    
    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>