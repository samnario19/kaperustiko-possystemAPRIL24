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

function printThermalReceipt($orderData) {
    try {
        // Validate input data
        if (!isset($orderData['cashierName']) || !isset($orderData['orderNumber']) || !isset($orderData['orderedItems'])) {
            throw new Exception("Missing required data");
        }

        // Connect to the thermal printer
        $connector = new WindowsPrintConnector("thermal1");
        $printer = new Printer($connector);

        // Initialize printer settings
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
        $printer->text("KAPE RUSTIKO\n");
        $printer->selectPrintMode();
        $printer->text("Cafe and Restaurant\n");
        $printer->text("Dewey Ave, Subic Bay Freeport Zone\n");
        $printer->text("VAT REG TIN: 123-456-789-12345\n");
        $printer->feed();

        // Print order details
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Date: " . date('Y-m-d') . "\n");
        $printer->text("Time: " . date('H:i:s') . "\n");
        $printer->text("Cashier: " . $orderData['cashierName'] . "\n");
        $printer->text("Order #: " . $orderData['orderNumber'] . "\n");
        $printer->text("Table #: " . $orderData['tableNumber'] . "\n");
        if ($orderData['totalPax'] > 1) {
            $printer->text("Total PAX: " . $orderData['totalPax'] . "\n");
            if ($orderData['seniorCount'] > 0) {
                $printer->text("Senior/PWD Count: " . $orderData['seniorCount'] . "\n");
            }
        }
        $printer->feed();

        // Print items
        $printer->text("==========================================\n");
        $printer->text("ORDERED ITEMS:\n");
        $printer->text("==========================================\n");

        foreach ($orderData['orderedItems'] as $item) {
            $printer->text($item['order_quantity'] . "x " . $item['order_name']);
            if (!empty($item['order_name2'])) {
                $printer->text(" " . $item['order_name2']);
            }
            $printer->text("\n");
            
            if (!empty($item['order_size'])) {
                $printer->text("   Size: " . $item['order_size'] . "\n");
            }
             
            $printer->text("   Price: P" . number_format($item['basePrice'], 2) . "\n");
            $printer->text("------------------------------------------\n");
        }

        // Print totals
        $printer->feed();
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text("Subtotal: P" . number_format($orderData['totalOrderedItemsPrice'], 2) . "\n");
        
        if ($orderData['voucherDiscount'] > 0) {
            $voucherAmount = ($orderData['totalOrderedItemsPrice'] * $orderData['voucherDiscount'] / 100);
            $printer->text("Voucher Discount: P" . number_format($voucherAmount, 2) . "\n");
        }
        
        if ($orderData['seniorDiscount'] > 0) {
            $printer->text("Senior/PWD Discount: P" . number_format($orderData['seniorDiscount'], 2) . "\n");
        }

        $serviceCharge = $orderData['totalOrderedItemsPrice'] * 0.05;
        $printer->text("Service Charge (5%): P" . number_format($serviceCharge, 2) . "\n");

        $finalTotal = $orderData['totalOrderedItemsPrice'] * (1 - $orderData['voucherDiscount'] / 100) - $orderData['seniorDiscount'] + $serviceCharge;
        $printer->text("TOTAL: P" . number_format($finalTotal, 2) . "\n");

        if ($orderData['payment'] > 0) {
            $printer->text("Amount Paid: P" . number_format($orderData['payment'], 2) . "\n");
            $change = max(0, $orderData['payment'] - $finalTotal);
            $printer->text("Change: P" . number_format($change, 2) . "\n");
        }

        // Print footer
        $printer->feed();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("==========================================\n");
        $printer->text("Thank you for dining with us!\n");
        $printer->text("Please come again!\n");
        $printer->feed(2);

        // Cut the receipt
        $printer->cut();
        $printer->close();

        return ["success" => true, "message" => "Receipt printed successfully"];
    } catch (Exception $e) {
        return ["success" => false, "message" => "Failed to print: " . $e->getMessage()];
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderData = json_decode(file_get_contents('php://input'), true);
    $result = printThermalReceipt($orderData);
    header('Content-Type: application/json');
    echo json_encode($result);
}
?>