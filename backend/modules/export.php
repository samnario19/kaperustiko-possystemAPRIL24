<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Add CORS headers at the very top
header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

require '../../vendor/autoload.php'; // Adjusted path

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "sql12759808";

// Create connection
$connection = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Update the query to fetch all necessary fields
$query = "SELECT code, title1, title2, label, label2, price1, price2, price3, qty FROM `pos-menu`"; // Adjusted query
$result = $connection->query($query);

if ($result === false) {
    die("Query failed: " . $connection->error);
}

// Prepare data for the spreadsheet
$data = [['Product Code', 'Title 1', 'Title 2', 'Label', 'Label2', 'Price 1', 'Price 2', 'Price 3', 'Quantity']]; // Header row

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['code'], 
            $row['title1'], 
            $row['title2'], 
            $row['label'], 
            $row['label2'], 
            $row['price1'], 
            $row['price2'], 
            $row['price3'], 
            $row['qty']
        ];
    }
} else {
    // Handle case where no products are found
    $data[] = ['No products found', '', '', '', '', '', '', '', ''];
}

$connection->close(); // Close the database connection

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Populate the spreadsheet with data
foreach ($data as $rowIndex => $row) {
    foreach ($row as $colIndex => $value) {
        $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);
        $sheet->setCellValue($cellCoordinate, $value);
    }
}

// Set the header to force download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="inventory.xlsx"');
header('Cache-Control: max-age=0');

// Write the file to the output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;