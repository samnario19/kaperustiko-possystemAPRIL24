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

// Define drink categories
function isDrinkCategory($label) {
    $drinkCategories = [
        'Frappe',
        'Soda',
        'Fruit Shake',
        'Beverage',
        'Juice',
        'Iced Coffee',
        'Hot Coffee',
        'Tea'
    ];
    return in_array($label, $drinkCategories);
}

function printKitchenOrder($orderData) {
    try {
        // Validate input data
        if (!isset($orderData['waiterName']) || !isset($orderData['itemsOrdered'])) {
            throw new Exception("Missing required data");
        }

        // Separate items into food and drinks
        $foodItems = [];
        $drinkItems = [];

        foreach ($orderData['itemsOrdered'] as $item) {
            // Check both label and label2 for drink categories
            if (isset($item['label']) && isDrinkCategory($item['label']) || 
                isset($item['label2']) && isDrinkCategory($item['label2'])) {
                $drinkItems[] = $item;
            } else {
                $foodItems[] = $item;
            }
        }

        $result = ["success" => true, "message" => []];

        // Print food items if any exist (to kitchen printer)
        if (!empty($foodItems)) {
            try {
                // Use thermal2 for kitchen orders
                $kitchenConnector = new WindowsPrintConnector("thermal2");
                $kitchenPrinter = new Printer($kitchenConnector);
                
                // Print divider and header
                $kitchenPrinter->setJustification(Printer::JUSTIFY_CENTER);
                $kitchenPrinter->text(str_repeat("=", 32) . "\n");
                $kitchenPrinter->setTextSize(2, 2);
                $kitchenPrinter->text("KITCHEN\n");
                $kitchenPrinter->text("ORDER\n");
                $kitchenPrinter->setTextSize(1, 1);
                $kitchenPrinter->text(str_repeat("=", 32) . "\n\n");
                
                // Print the actual order
                printFoodOrder($kitchenPrinter, $orderData, $foodItems);
                
                // Print final divider
                $kitchenPrinter->text("\n" . str_repeat("=", 32) . "\n");
                $kitchenPrinter->feed(2);
                $kitchenPrinter->cut();
                
                $kitchenPrinter->close();
                $result["message"][] = "Kitchen order printed successfully";
            } catch (Exception $e) {
                $result["message"][] = "Kitchen print failed: " . $e->getMessage();
                $result["success"] = false;
            }
        }

        // Print drink items if any exist (to bar printer)
        if (!empty($drinkItems)) {
            try {
                // Use thermal3 for drink orders
                $barConnector = new WindowsPrintConnector("thermal3");
                $barPrinter = new Printer($barConnector);
                
                // Print divider and header
                $barPrinter->setJustification(Printer::JUSTIFY_CENTER);
                $barPrinter->text(str_repeat("=", 32) . "\n");
                $barPrinter->setTextSize(2, 2);
                $barPrinter->text("COFFEE/BAR\n");
                $barPrinter->text("ORDER\n");
                $barPrinter->setTextSize(1, 1);
                $barPrinter->text(str_repeat("=", 32) . "\n\n");
                
                // Print the actual order
                printDrinkOrder($barPrinter, $orderData, $drinkItems);
                
                // Print final divider
                $barPrinter->text("\n" . str_repeat("=", 32) . "\n");
                $barPrinter->feed(2);
                $barPrinter->cut();
                
                $barPrinter->close();
                $result["message"][] = "Bar order printed successfully";
            } catch (Exception $e) {
                $result["message"][] = "Bar print failed: " . $e->getMessage();
                $result["success"] = false;
            }
        }

        return $result;
    } catch (Exception $e) {
        return ["success" => false, "message" => ["Failed to process order: " . $e->getMessage()]];
    }
}

function printFoodOrder($printer, $orderData, $foodItems) {
    // Print table number at the top
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(2, 2);
    $printer->text("TABLE " . $orderData['table_number'] . "\n");
    $printer->setTextSize(1, 1);
    
    // Print order details
    $printer->text(date('Y-m-d H:i:s') . "\n");
    $printer->text("Waiter: " . $orderData['waiterName'] . "\n");
    $printer->text("Order #: " . $orderData['receiptNumber'] . "\n");
    $printer->text(str_repeat("-", 32) . "\n");

    // Print ordered items with medium text
    foreach ($foodItems as $item) {
        // Print item name and quantity
        $printer->setTextSize(2, 1);
        $printer->setEmphasis(true);
        $printer->text("- " . $item['order_name'] . " (x" . $item['order_quantity'] . ")\n");
        $printer->setEmphasis(false);
        
        // Print size if applicable
        $printer->setTextSize(1, 1);
        if (!empty($item['order_size'])) {
            $printer->text("  Size: " . $item['order_size'] . "\n");
        }

        // Print special instructions if any
        if (!empty($item['special_instructions'])) {
            $printer->text("  Note: " . $item['special_instructions'] . "\n");
        }
        
        $printer->text("\n"); // Add space between items
    }

    // Print table number at bottom
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(2, 2);
    $printer->text("TABLE " . $orderData['table_number'] . "\n");
}

function printDrinkOrder($printer, $orderData, $drinkItems) {
    // Print table number at the top
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(2, 2);
    $printer->text("TABLE " . $orderData['table_number'] . "\n");
    $printer->setTextSize(1, 1);
    
    // Print order details
    $printer->text(date('Y-m-d H:i:s') . "\n");
    $printer->text("Waiter: " . $orderData['waiterName'] . "\n");
    $printer->text("Order #: " . $orderData['receiptNumber'] . "\n");
    $printer->text(str_repeat("-", 32) . "\n");

    // Print ordered items with medium text
    foreach ($drinkItems as $item) {
        // Print item name and quantity
        $printer->setTextSize(2, 1);
        $printer->setEmphasis(true);
        $printer->text("- " . $item['order_name'] . " (x" . $item['order_quantity'] . ")\n");
        $printer->setEmphasis(false);
        
        // Print size if applicable
        $printer->setTextSize(1, 1);
        if (!empty($item['order_size'])) {
            $printer->text("  Size: " . $item['order_size'] . "\n");
        }

        // Print special instructions if any
        if (!empty($item['special_instructions'])) {
            $printer->text("  Note: " . $item['special_instructions'] . "\n");
        }
        
        $printer->text("\n"); // Add space between items
    }

    // Print table number at bottom
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(2, 2);
    $printer->text("TABLE " . $orderData['table_number'] . "\n");
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderData = json_decode(file_get_contents('php://input'), true);
    $result = printKitchenOrder($orderData);
    header('Content-Type: application/json');
    echo json_encode($result);
}
?> 