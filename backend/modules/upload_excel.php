<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require '../../vendor/autoload.php'; // Adjusted path

use PhpOffice\PhpSpreadsheet\IOFactory;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sql12759808";

// Create connection
$connection = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if a file was uploaded
if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['excelFile']['tmp_name'];
    
    // Define the upload directory (ensure this directory exists and is writable)
    $uploadDir = '../../static/uploads/'; // Updated path
    $fileName = basename($_FILES['excelFile']['name']);
    $uploadFilePath = $uploadDir . $fileName;

    // Move the uploaded file to the desired location
    if (!move_uploaded_file($file, $uploadFilePath)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
        exit;
    }

    // Load the spreadsheet
    try {
        $spreadsheet = IOFactory::load($uploadFilePath);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error loading file: ' . $e->getMessage()]);
        exit;
    }
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    // Log the data being processed
    error_log("Data from Excel: " . print_r($data, true));

    // Loop through the data and update the database
    foreach ($data as $row) {
        // Skip the header row
        if ($row[0] === 'Product Code') {
            continue;
        }

        $code = $row[0];
        
        // Check if code is empty
        if (empty($code)) {
            continue; // Skip this row if code is empty
        }

        $title1 = $row[1];
        $title2 = $row[2];
        $label = $row[3];
        $label2 = $row[4];
        $price1 = $row[5];
        $price2 = $row[6];
        $price3 = $row[7];
        $qty = $row[8];

        // Log the data being inserted
        error_log("Inserting/updating: $code, $title1, $title2, $label, $label2, $price1, $price2, $price3, $qty");

        $stock_date = date('Y-m-d'); // Current date
        $stock_time = date('H:i:s'); // Current time

        // Update the product in the database
        $query = "UPDATE `pos-menu` SET 
                  title1 = ?, 
                  title2 = ?, 
                  label = ?, 
                  label2 = ?, 
                  price1 = ?, 
                  price2 = ?, 
                  price3 = ?, 
                  qty = ?, 
                  stock_date = ?, 
                  stock_time = ?
                  WHERE code = ?";

        $stmt = $connection->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $connection->error]);
            exit;
        }

        $stmt->bind_param('ssssssiiiss', $title1, $title2, $label, $label2, $price1, $price2, $price3, $qty, $stock_date, $stock_time, $code);
        
        if (!$stmt->execute()) {
            // Log the error if execution fails
            error_log("Execute failed: " . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
            exit;
        }
    }

    $stmt->close();
    $connection->close();
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error']);
}
?>