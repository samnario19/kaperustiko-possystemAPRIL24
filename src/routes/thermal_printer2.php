<?php
    // Add CORS headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
            
            // Print header
            $printer->setTextSize(2, 2);
            $printer->text("Kape Rustiko Cafe\n");
            $printer->text("and Restaurant\n");
            $printer->setTextSize(1, 1);
            $printer->text("Dewey Ave, Subic Bay Freeport Zone\n");
            $printer->text("VAT REG TIN: 123-456-789-12345\n");
            $printer->feed();

            // Print sales invoice title
            $printer->setEmphasis(true);
            $printer->text("Not an Official Reciept\n");
            $printer->setEmphasis(false);
            $printer->feed();

            // Print transaction details
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Date: " . date('Y-m-d') . "\n");
            $printer->text("Time: " . date('H:i:s') . "\n");
            $printer->text("Cashier: " . $orderData['cashierName'] . "\n");
            $printer->text("Receipt #: " . $orderData['orderNumber'] . "\n");
            $printer->feed();

            // Print divider
            $printer->text(str_repeat("-", 32) . "\n");

            // Print items header
            $printer->setEmphasis(true);
            $printer->text(sprintf("%-23s %8s\n", "ITEMS", "PRICE"));
            $printer->setEmphasis(false);
            $printer->text(str_repeat("-", 32) . "\n");

            // Print ordered items
            foreach ($orderData['orderedItems'] as $item) {
                $printer->text($item['order_name'] . " x" . $item['order_quantity'] . "\n");
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text("P " . number_format($item['basePrice'], 2) . "\n");
                
                // Print addons if any
                if (!empty($item['order_addons'])) {
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("  + " . $item['order_addons'] . "\n");
                    $printer->setJustification(Printer::JUSTIFY_RIGHT);
                    $printer->text("P " . number_format($item['order_addons_price'], 2) . "\n");
                }
                $printer->setJustification(Printer::JUSTIFY_LEFT);
            }

            // Print divider
            $printer->text(str_repeat("-", 32) . "\n");

            // Print totals
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text(sprintf("%-23s %8.2f\n", "Subtotal:", $orderData['totalOrderedItemsPrice']));
            $printer->text(sprintf("%-23s %8.2f\n", "Discount:", ($orderData['totalOrderedItemsPrice'] * $orderData['voucherDiscount'] / 100)));
            $printer->text(sprintf("%-23s %8.2f\n", "Service Charge (5%):", ($orderData['totalOrderedItemsPrice'] * 0.05)));
            $printer->text(sprintf("%-23s %8.2f\n", "VAT (12%):", ($orderData['totalOrderedItemsPrice'] * 0.12)));
            
            $printer->setEmphasis(true);
            $printer->text(sprintf("%-23s %8.2f\n", "TOTAL:", 
                ($orderData['totalOrderedItemsPrice'] * 1.12) * (1 - $orderData['voucherDiscount'] / 100) + 
                ($orderData['totalOrderedItemsPrice'] * 0.05)));
            $printer->setEmphasis(false);

            $printer->text(sprintf("%-23s %8.2f\n", "Amount Paid:", $orderData['payment']));
            $printer->text(sprintf("%-23s %8.2f\n", "Change:", 
                max(0, $orderData['payment'] - 
                    (($orderData['totalOrderedItemsPrice'] * 1.12) * (1 - $orderData['voucherDiscount'] / 100) + 
                    ($orderData['totalOrderedItemsPrice'] * 0.05)))));

            // Print footer
            $printer->feed();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Thank you for having your meal with us!\n");
            $printer->text("Please come again!\n");
            
            // Print QR Code with receipt number
            $printer->qrCode($orderData['orderNumber'], Printer::QR_ECLEVEL_L, 5);
            
            $printer->feed(3);
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