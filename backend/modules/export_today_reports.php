<?php

header("Access-Control-Allow-Origin: http://localhost:5173"); // Allow your frontend origin
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow specific methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Handle preflight request
    http_response_code(200);
    exit;
}

// ... existing code ...
require '../../vendor/autoload.php'; // Adjusted path

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if (!isset($data['date']) || !isset($data['report'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the title of the sheet
$sheet->setTitle('End of Day Report');

// Set the header row
$sheet->setCellValue('A1', 'Date: ' . $data['date']);
$sheet->setCellValue('A2', 'Item Name');
$sheet->setCellValue('B2', 'Quantity');

// Populate the data
$row = 2; // Start from the second row
$report = $data['report'];


// Continue populating food items
foreach ($report['foodItems'] as $itemName => $quantity) {
    $sheet->setCellValue('A' . $row, $itemName);
    $sheet->setCellValue('B' . $row, $quantity);
    $row++;
}

// Add totals
$sheet->setCellValue('A' . $row, 'Total Sales');
$sheet->setCellValue('B' . $row, $report['totalSales']);

// Set the response headers to download the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="End_of_Day_Report_' . $data['date'] . '.xlsx"');
header('Cache-Control: max-age=0');

// Write the file to the output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
