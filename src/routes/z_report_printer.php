<?php
// Add CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function printZReport($reportData) {
    try {
        // Connect to the thermal printer
        $connector = new WindowsPrintConnector("thermal1");
        $printer = new Printer($connector);

        // Initialize printer settings
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        
        // Print header
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
        $printer->text("KAPE RUSTIKO\n");
        $printer->selectPrintMode();
        $printer->text("Cafe and Restaurant\n");
        $printer->text("Dewey Ave, Subic Bay Freeport Zone\n");
        $printer->text("VAT REG TIN: 123-456-789-12345\n\n");
        
        // Z-reading report header
        $printer->setEmphasis(true);
        $printer->text("Z-READING REPORT\n");
        $printer->setEmphasis(false);
        $printer->text("==============================\n");
        
        // Print date and time
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Report Date: " . $reportData['report_date'] . "\n");
        $printer->text("Printed: " . $reportData['printed_date'] . " " . $reportData['printed_time'] . "\n");
        $printer->text("==============================\n\n");
        
        // Print shift summary
        $printer->setEmphasis(true);
        $printer->text("SHIFT SUMMARY\n");
        $printer->setEmphasis(false);
        $printer->text("------------------------------\n");
        $printer->text(str_pad("Shift", 20) . str_pad("Sales", 10) . "Trans\n");
        $printer->text("------------------------------\n");
        
        // Morning shift
        $morningShiftSales = number_format($reportData['shifts']['morning']['sales'], 2);
        $morningShiftTrans = $reportData['shifts']['morning']['transactions'];
        $printer->text(str_pad("Morning Shift Duty", 20) . str_pad("₱" . $morningShiftSales, 10) . $morningShiftTrans . "\n");
        
        // Night shift
        $nightShiftSales = number_format($reportData['shifts']['night']['sales'], 2);
        $nightShiftTrans = $reportData['shifts']['night']['transactions'];
        $printer->text(str_pad("Night Shift Duty", 20) . str_pad("₱" . $nightShiftSales, 10) . $nightShiftTrans . "\n");
        $printer->text("------------------------------\n\n");
        
        // Print sales summary
        $printer->setEmphasis(true);
        $printer->text("SALES SUMMARY\n");
        $printer->setEmphasis(false);
        $printer->text("------------------------------\n");
        
        // Format all monetary values
        $grossSales = number_format($reportData['gross_sales'], 2);
        $cashSales = number_format($reportData['cash_sales'], 2);
        $pwdSeniorDiscount = number_format($reportData['pwd_senior_discount'], 2);
        $serviceCharge = number_format($reportData['service_charge'], 2);
        $zeroRatedSales = number_format($reportData['zero_rated_sales'], 2);
        $vatExemptedSales = number_format($reportData['vat_exempted_sales'], 2);
        $vatableSales = number_format($reportData['vatable_sales'], 2);
        $vat = number_format($reportData['vat'], 2);
        
        // Print sales details
        $printer->text("Gross Sales:       ₱" . $grossSales . "\n");
        $printer->text("Cash Sales:        ₱" . $cashSales . "\n");
        $printer->text("PWD/Senior Disc:   ₱" . $pwdSeniorDiscount . "\n");
        $printer->text("Service Charge:    ₱" . $serviceCharge . "\n");
        $printer->text("Zero Rated Sales:  ₱" . $zeroRatedSales . "\n");
        $printer->text("VAT Exempted:      ₱" . $vatExemptedSales . "\n");
        $printer->text("Vatable Sales:     ₱" . $vatableSales . "\n");
        $printer->text("VAT:               ₱" . $vat . "\n");
        $printer->text("------------------------------\n\n");
        
        // Print receipt range
        $printer->setEmphasis(true);
        $printer->text("RECEIPT RANGE\n");
        $printer->setEmphasis(false);
        $printer->text("------------------------------\n");
        $printer->text("Start Receipt:     " . $reportData['start_receipt'] . "\n");
        $printer->text("End Receipt:       " . $reportData['end_receipt'] . "\n");
        $printer->text("------------------------------\n\n");
        
        // Print voids and transaction summary
        $printer->setEmphasis(true);
        $printer->text("VOIDS & TRANSACTIONS\n");
        $printer->setEmphasis(false);
        $printer->text("------------------------------\n");
        
        $voidItems = number_format($reportData['void_items']);
        $voidedAmount = number_format($reportData['voided_amount'], 2);
        $numTransactions = number_format($reportData['num_transactions']);
        $netSales = number_format($reportData['net_sales'], 2);
        $runningTotal = number_format($reportData['running_total'], 2);
        
        $printer->text("Void Items:        " . $voidItems . "\n");
        $printer->text("Voided Amount:     ₱" . $voidedAmount . "\n");
        $printer->text("Transactions:      " . $numTransactions . "\n");
        $printer->text("Net Sales:         ₱" . $netSales . "\n");
        $printer->text("Running Total:     ₱" . $runningTotal . "\n");
        $printer->text("------------------------------\n\n");
        
        // Print footer
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("*** End of Z-Report ***\n");
        $printer->text("Printed by: " . ($reportData['cashier_name'] ?? "Admin") . "\n\n");
        
        // Cut the receipt
        $printer->cut();
        $printer->close();
        
        return ["success" => true, "message" => "Z-Report printed successfully"];
    } catch (Exception $e) {
        return ["success" => false, "message" => "Failed to print: " . $e->getMessage()];
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportData = json_decode(file_get_contents('php://input'), true);
    $result = printZReport($reportData);
    header('Content-Type: application/json');
    echo json_encode($result);
}
?> 