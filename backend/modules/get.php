<?php
include '../config/connection.php';

// Function to get bestsellers
function getBestsellers($conn)
{
    $sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(items_ordered, '$[*].order_name')) AS order_names FROM total_sales";
    $result = $conn->query($sql);
    $order_counts = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $order_names = json_decode($row['order_names']);
            // Ensure $order_names is an array before iterating
            if (is_array($order_names)) {
                foreach ($order_names as $name) {
                    if (isset($order_counts[$name])) {
                        $order_counts[$name]++;
                    } else {
                        $order_counts[$name] = 1;
                    }
                }
            }
        }
    }
    $most_ordered = array_keys($order_counts, max($order_counts));
    echo json_encode($most_ordered);
}

// Function to get menu items
function getMenu($conn)
{
    $sql = "SELECT * FROM `pos-menu`"; // Fetch all menu items
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $menuItems = [];
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
        }
        echo json_encode($menuItems);
    } else {
        echo json_encode(["message" => "No menu items found."]);
    }
}

// Function to get items by code
function getItems($conn)
{
    $code = isset($_GET['code']) ? $_GET['code'] : ''; // Retrieve code from query parameters
    $sql = "SELECT * FROM `pos-menu` WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $items = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($items);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
}

// Function to get least sellers
function getLeastsellers($conn)
{
    $sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(items_ordered, '$[*].order_name')) AS order_names FROM total_sales";
    $result = $conn->query($sql);
    $order_counts = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $order_names = json_decode($row['order_names']);
            // Ensure $order_names is an array before iterating
            if (is_array($order_names)) {
                foreach ($order_names as $name) {
                    if (isset($order_counts[$name])) {
                        $order_counts[$name]++;
                    } else {
                        $order_counts[$name] = 1;
                    }
                }
            }
        }
    }
    $least_ordered = array_keys($order_counts, min($order_counts));
    echo json_encode($least_ordered);
}

// Function to get menu for dashboard
function getMenuDashboard($conn)
{
    $selectedCategory = $_GET['label']; // Use GET method for label1
    $selectedCategory2 = $_GET['label2']; // Use GET method for label2
    $query = "SELECT * FROM `pos-menu` WHERE label = ? OR label2 = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $selectedCategory, $selectedCategory2);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $menuItems = [];
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
        }
        echo json_encode($menuItems);
    } else {
        echo json_encode(["message" => "No menu items found."]);
    }
    $stmt->close();
}

// Function to get orders
function getOrders($conn)
{
    $sql = "SELECT * FROM orders";
    $result = $conn->query($sql);
    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    echo json_encode($orders);
}

// Function to get orders
function getQueOrders($conn)
{
    error_log("Fetching all queued orders");
    $sql = "SELECT * FROM que_orders ORDER BY receipt_number DESC";
    $result = $conn->query($sql);
    
    if (!$result) {
        error_log("Query error in getQueOrders: " . $conn->error);
        echo json_encode(["error" => "Database query error"]);
        return;
    }
    
    $orders = [];
    $count = 0;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count++;
            // Add table_number if missing
            if (!isset($row['table_number']) || empty($row['table_number'])) {
                $row['table_number'] = '';
            }
            // Log each order for debugging
            error_log("Found order: Receipt #" . $row['receipt_number'] . " for table " . $row['table_number']);
            $orders[] = $row;
        }
    }
    
    error_log("Returning " . $count . " total orders");
    echo json_encode($orders);
}

function getReserveTables($conn)
{
    $sql = "SELECT * FROM reserve_table";
    $result = $conn->query($sql);
    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    echo json_encode($orders);
}

function getVouchers($conn)
{
    $sql = "SELECT * FROM vouchers";
    $result = $conn->query($sql);
    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    echo json_encode($orders);
}

function getVouchersbyCode($conn)
{
    $voucher_code = isset($_GET['voucher_code']) ? $_GET['voucher_code'] : '';
    $sql = "SELECT voucher_discount FROM vouchers WHERE voucher_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $voucher_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    echo json_encode($orders);
}

function getInfoReserveTables($conn)
{
    $table_number = isset($_GET['table_number']) ? $_GET['table_number'] : ''; // Retrieve table_number from query parameters
    $sql = "SELECT * FROM reserve_table WHERE table_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $table_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    echo json_encode($orders);
    $stmt->close();
}

// Function to get product quantity
function getProductQty($conn)
{
    $code = isset($_GET['code']) ? $_GET['code'] : ''; // Retrieve code from query parameters
    $sql = "SELECT qty FROM `pos-menu` WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row); // Return the quantity as JSON
    } else {
        echo json_encode(["qty" => 0]); // Return 0 if no record found
    }
    $stmt->close();
}

// Function to get remit returns
function getRemitReturns($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['date'])) {
            $date = $_GET['date'];
            $stmt = $conn->prepare("SELECT * FROM remit_returns WHERE return_date = ?");
            $stmt->bind_param("s", $date);
        } else {
            $stmt = $conn->prepare("SELECT * FROM remit_returns");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $remits = [];
        while ($row = $result->fetch_assoc()) {
            $remits[] = $row;
        }
        echo json_encode($remits);
        exit; // Stop further execution
    }
}

// Function to get remit sales
function getRemitSales($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['remit_code'])) {
            $remit_code = $_GET['remit_code'];
            $stmt = $conn->prepare("SELECT * FROM remit_sales WHERE remit_code = ?");
            $stmt->bind_param("s", $remit_code);
        } else {
            $stmt = $conn->prepare("SELECT * FROM remit_sales");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $remits = [];
        while ($row = $result->fetch_assoc()) {
            $remits[] = $row;
        }
        echo json_encode($remits);
        exit; // Stop further execution
    }
}

// Function to get return orders
function getReturnOrders($conn)
{
    $return_date = isset($_GET['return_date']) ? $_GET['return_date'] : '';
    if (!empty($return_date)) {
        $sql = "SELECT * FROM `return-orders` WHERE return_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $return_date);
    } else {
        $sql = "SELECT * FROM `total_returns`"; // Query to get all data
        $stmt = $conn->prepare($sql);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

// Function to get sales information
function getSalesInformation($conn)
{
    $sales_code = isset($_GET['sales_code']) ? $_GET['sales_code'] : null; // Retrieve sales_code from query parameters
    $query = "SELECT * FROM total_sales" . ($sales_code ? " WHERE sales_code = '$sales_code'" : ""); // Update query to filter by sales_code
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $salesData = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($salesData);
    } else {
        echo json_encode(["message" => "No sales data found"]);
    }
}

function getSalesInformationByDate($conn) {
    $sales_code = isset($_GET['sales_code']) ? $_GET['sales_code'] : null;
    $date = isset($_GET['date']) ? $_GET['date'] : null;
    $sql = "SELECT * FROM total_sales WHERE date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $salesData = [];
    while ($row = $result->fetch_assoc()) {
        $salesData[] = $row;
    }
    echo json_encode($salesData);
}

// Function to get today's sales
function getTodaySales($conn)
{
    $date = $_GET['date'] ?? null; // Check for date_time in query params
    if ($date === null) {
        $date = date('Y-m-d'); // Use current date if no date is provided
    }
    if ($date) {
        $sql = "SELECT SUM(total_amount) AS total_amount FROM total_sales WHERE date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $salesData = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $salesData[] = $row;
            }
        }
        echo json_encode($salesData);
    } else {
        echo json_encode(["error" => "Date parameter is required."]);
    }
}

// Function to get total orders
function getTotalOrders($conn)
{
    $query = "SELECT MAX(total_order) as total_order FROM total_sales";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $row['total_order'] += 1;
    echo json_encode($row);
}
function getTotalQueOrders($conn)
{
    $query = "SELECT MAX(total_order) as total_order FROM que_orders";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $row['total_order'] += 1;
    echo json_encode($row);
}

// Function to get total sales
function getTotalSales($conn)
{
    $sql = "SELECT SUM(total_sales) AS total_sales_sum FROM remit_sales";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $totalSales = $row['total_sales_sum'];
        echo json_encode(['total_sales' => $totalSales]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get user information
function getUser($conn)
{
    $staff_token = $_GET['staff_token']; // Assuming you're getting the token from a GET request
    $query = "SELECT firstName FROM `user-staff` WHERE staff_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $staff_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $firstName = $result->fetch_assoc()['firstName'];
    echo json_encode($firstName);
}

// Function to update product quantity
function updateProductQty($conn)
{
    if (!isset($_GET['code'])) {
        echo json_encode(["status" => "error", "message" => "Code parameter is missing."]);
        return;
    }

    $code = $_GET['code'];

    // Get current quantity
    $current_qty_sql = "SELECT qty FROM `pos-menu` WHERE code = ?";
    $current_stmt = $conn->prepare($current_qty_sql);
    $current_stmt->bind_param("s", $code);
    $current_stmt->execute();
    $result = $current_stmt->get_result();
    $row = $result->fetch_assoc();
    $current_qty = $row['qty'];
    $current_stmt->close();

    $new_qty = $current_qty - 1;

    $sql = "UPDATE `pos-menu` SET qty = ? WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $new_qty, $code);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Quantity updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No rows updated."]);
    }

    $stmt->close();
}

// Function to get total shortage cost
function getTotalShortage($conn)
{
    $sql = "SELECT SUM(remit_shortage) AS total_shortage FROM remit_sales";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $totalShortage = $row['total_shortage'];
        echo json_encode(['total_shortage' => $totalShortage]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get today's total shortage cost
function getTodayShortage($conn)
{
    $today = date('Y-m-d'); // Get today's date
    $sql = "SELECT SUM(remit_shortage) AS total_shortage FROM remit_sales WHERE remit_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        $totalShortage = $row['total_shortage'] ?? 0; // Default to 0 if no records found
        echo json_encode(['total_shortage' => $totalShortage]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get today's total return cost
function getTodayReturnCost($conn)
{
    $today = date('Y-m-d'); // Get today's date
    $sql = "SELECT SUM(total_sales) AS total_return FROM `remit_returns` WHERE return_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        $totalReturn = $row['total_return'] ?? 0; // Default to 0 if no records found
        echo json_encode(['total_return' => $totalReturn]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get total return cost
function getTotalReturnCost($conn)
{
    $sql = "SELECT SUM(total_sales) AS total_return_cost FROM remit_returns";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $totalReturnCost = $row['total_return_cost'] ?? 0; // Default to 0 if no records found
        echo json_encode(['total_return_cost' => $totalReturnCost]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get monthly sales
function getMonthlySales($conn) {
    // Use STR_TO_DATE to properly parse the date format MM/DD/YYYY
    $sql = "SELECT 
                MONTH(STR_TO_DATE(date, '%m/%d/%Y')) AS month, 
                SUM(total_amount) AS total_amount 
            FROM total_sales 
            GROUP BY MONTH(STR_TO_DATE(date, '%m/%d/%Y'))
            ORDER BY month";
    
    $result = $conn->query($sql);
    
    // Initialize array with all months set to 0
    $monthlySales = array_fill(1, 12, [
        'month' => 0,
        'total_amount' => 0.0
    ]);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $month = (int)$row['month'];
            $monthlySales[$month] = [
                'month' => $month,
                'total_amount' => (float)$row['total_amount']
            ];
        }
    }
    
    // Convert to indexed array and preserve order
    $monthlySales = array_values($monthlySales);
    
    echo json_encode($monthlySales);
}

// Function to get table order details
function getTableOrderDetails($conn)
{
    // Check if table_number parameter is provided
    if (isset($_GET['table_number'])) {
        $table_number = $_GET['table_number'];
        
        error_log("Fetching order details for table: $table_number");
        
        // Query to get all orders for this table
        $sql = "SELECT que_order_no, receipt_number, date, time, items_ordered, total_amount, 
                amount_paid, amount_change, order_take, order_status, waiter_name, waiter_code, table_number 
                FROM que_orders 
                WHERE table_number = ? 
                ORDER BY receipt_number ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $table_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $formattedItems = [];
        $receiptCount = 0;
        $totalItemCount = 0;
        
        if ($result->num_rows == 0) {
            error_log("No orders found for table: $table_number");
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }
        
        error_log("Found " . $result->num_rows . " orders for table: $table_number");
        
        while ($row = $result->fetch_assoc()) {
            $receiptCount++;
            $receipt = $row['receipt_number'];
            
            // Log the order we're processing
            error_log("Processing order #$receipt for table $table_number with status: " . $row['order_status']);
            
            // Decode the JSON items_ordered string to an array
            $itemsOrderedJson = $row['items_ordered'];
            error_log("Items ordered JSON for receipt #$receipt: $itemsOrderedJson");
            
            $itemsOrdered = json_decode($itemsOrderedJson, true);
            
            if (!is_array($itemsOrdered)) {
                error_log("Failed to decode items_ordered JSON for receipt #$receipt. Error: " . json_last_error_msg());
                continue; // Skip this order if JSON is invalid
            }
            
            error_log("Successfully decoded " . count($itemsOrdered) . " items for receipt #$receipt");
            
            foreach ($itemsOrdered as $itemIndex => $item) {
                $totalItemCount++;
                
                // Format each item with receipt info
                $formattedItem = [
                    'receipt_number' => $row['receipt_number'],
                    'order_status' => $row['order_status'],
                    'waiter_name' => $row['waiter_name'],
                    'waiter_code' => $row['waiter_code'],
                    'date' => $row['date'],
                    'time' => $row['time'],
                    'table_number' => $row['table_number'],
                    'order_name' => isset($item['order_name']) ? $item['order_name'] : '',
                    'order_size' => isset($item['order_size']) ? $item['order_size'] : '',
                    'order_quantity' => isset($item['order_quantity']) ? str_replace('x', '', $item['order_quantity']) : 1,
                    'order_addons' => isset($item['order_addons']) ? $item['order_addons'] : 'None',
                    'order_addons_price' => isset($item['order_addons_price']) ? $item['order_addons_price'] : 0,
                    'order_addons2' => isset($item['order_addons2']) ? $item['order_addons2'] : 'None',
                    'order_addons_price2' => isset($item['order_addons_price2']) ? $item['order_addons_price2'] : 0,
                    'order_addons3' => isset($item['order_addons3']) ? $item['order_addons3'] : 'None',
                    'order_addons_price3' => isset($item['order_addons_price3']) ? $item['order_addons_price3'] : 0,
                    'delivered' => isset($item['delivered']) ? $item['delivered'] : "0"
                ];
                
                // Calculate item total price (base price + addons)
                $basePrice = isset($item['basePrice']) ? $item['basePrice'] : 0;
                $quantity = intval(str_replace('x', '', $formattedItem['order_quantity']));
                $addonsPrice = $formattedItem['order_addons_price'] + $formattedItem['order_addons_price2'] + $formattedItem['order_addons_price3'];
                $formattedItem['item_total_price'] = ($basePrice + $addonsPrice) * $quantity;
                
                $formattedItems[] = $formattedItem;
            }
        }
        
        error_log("Processed $receiptCount orders with total $totalItemCount items for table: $table_number");
        
        header('Content-Type: application/json');
        echo json_encode($formattedItems);
    } else {
        // Return error if table_number is not provided
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Table number is required']);
    }
}

// Function to get order items by receipt number
function getOrderItemsByReceipt($conn)
{
    // Check if receipt_number parameter is provided
    if (isset($_GET['receipt_number'])) {
        $receipt_number = $_GET['receipt_number'];
        
        error_log("Fetching order items for receipt: $receipt_number");
        
        // Query to get order details for this receipt
        $sql = "SELECT que_order_no, receipt_number, date, time, items_ordered, total_amount, 
                amount_paid, amount_change, order_take, order_status, waiter_name, waiter_code, table_number 
                FROM que_orders 
                WHERE receipt_number = ? OR que_order_no = ?
                ORDER BY receipt_number ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $receipt_number, $receipt_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $formattedItems = [];
        
        if ($result->num_rows == 0) {
            error_log("No order found with receipt number: $receipt_number");
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }
        
        while ($row = $result->fetch_assoc()) {
            // Get the table number for this order
            $table_number = $row['table_number'];
            error_log("Found order for receipt $receipt_number at table $table_number");
            
            // Decode the JSON items_ordered string to an array
            $items_ordered_json = $row['items_ordered'];
            $itemsOrdered = json_decode($items_ordered_json, true);
            
            error_log("Items ordered JSON: $items_ordered_json");
            
            if (is_array($itemsOrdered)) {
                error_log("Found " . count($itemsOrdered) . " items in receipt $receipt_number");
                foreach ($itemsOrdered as $item) {
                    // Format each item with receipt info
                    $formattedItem = [
                        'receipt_number' => $row['receipt_number'],
                        'order_status' => $row['order_status'],
                        'waiter_name' => $row['waiter_name'],
                        'waiter_code' => $row['waiter_code'],
                        'date' => $row['date'],
                        'time' => $row['time'],
                        'table_number' => $table_number,
                        'order_name' => isset($item['order_name']) ? $item['order_name'] : '',
                        'order_size' => isset($item['order_size']) ? $item['order_size'] : '',
                        'order_quantity' => isset($item['order_quantity']) ? str_replace('x', '', $item['order_quantity']) : 1,
                        'order_addons' => isset($item['order_addons']) ? $item['order_addons'] : 'None',
                        'order_addons_price' => isset($item['order_addons_price']) ? $item['order_addons_price'] : 0,
                        'order_addons2' => isset($item['order_addons2']) ? $item['order_addons2'] : 'None',
                        'order_addons_price2' => isset($item['order_addons_price2']) ? $item['order_addons_price2'] : 0,
                        'order_addons3' => isset($item['order_addons3']) ? $item['order_addons3'] : 'None',
                        'order_addons_price3' => isset($item['order_addons_price3']) ? $item['order_addons_price3'] : 0,
                        'delivered' => isset($item['delivered']) ? $item['delivered'] : "0"
                    ];
                    
                    // Calculate item total price (base price + addons)
                    $basePrice = isset($item['basePrice']) ? $item['basePrice'] : 0;
                    $quantity = intval(str_replace('x', '', $formattedItem['order_quantity']));
                    $addonsPrice = $formattedItem['order_addons_price'] + $formattedItem['order_addons_price2'] + $formattedItem['order_addons_price3'];
                    $formattedItem['item_total_price'] = ($basePrice + $addonsPrice) * $quantity;
                    
                    $formattedItems[] = $formattedItem;
                }
            } else {
                error_log("Invalid items_ordered format for receipt $receipt_number");
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($formattedItems);
    } else {
        // Return error if receipt_number is not provided
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Receipt number is required']);
    }
}

// Function to get waiter information by code
function getWaiterByCode($conn)
{
    $waiter_code = isset($_GET['waiter_code']) ? $_GET['waiter_code'] : ''; // Retrieve waiter_code from query parameters
    $query = "SELECT firstName, lastName FROM `user-staff` WHERE waiter_code = ?"; // Adjusted the query to include a comma
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $waiter_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $waiterData = $result->fetch_assoc();
        echo json_encode($waiterData); // Return the waiter's first name and last name as JSON
    } else {
        echo json_encode(["error" => "Waiter code not found."]); // Return an error if no waiter is found
    }
    
    $stmt->close();
}

// Function to get waiters with order counts
function getWaitersWithOrderCounts($conn)
{
    $sql = "SELECT firstName, lastName, order_count FROM `user-staff`"; // Query to get first name, last name, and order count
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $waiters = [];
        while ($row = $result->fetch_assoc()) {
            $waiters[] = $row; // Collect each waiter's data
        }
        echo json_encode($waiters); // Return the data as JSON
    } else {
        echo json_encode(["message" => "No waiters found."]); // Handle case with no waiters
    }
}

// Function to get waiter code by email
function getWaiterCodeByEmail($conn)
{
    $email = isset($_GET['email']) ? $_GET['email'] : ''; // Retrieve email from query parameters
    $query = "SELECT waiter_code FROM `user-staff` WHERE email = ?"; // Query to get waiter_code by email
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $waiterData = $result->fetch_assoc();
        echo json_encode($waiterData); // Return the waiter_code as JSON
    } else {
        echo json_encode(["error" => "Email not found."]); // Return an error if no waiter is found
    }
    
    $stmt->close();
}

// Function to get leading staff
function getLeadingStaff($conn) {
    $sql = "SELECT firstName, lastName, order_count FROM `user-staff` ORDER BY order_count DESC LIMIT 1"; // Query to get the staff with the highest order count
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $leadingStaff = $result->fetch_assoc(); // Fetch the leading staff data
        echo json_encode($leadingStaff); // Return the leading staff as JSON
    } else {
        echo json_encode(["message" => "No staff found."]); // Handle case with no staff
    }
}

// Function to get all data of a user
function getAllDataOfUser($conn)
{
    $sql = "SELECT * FROM `user-staff`"; // Query to select all user data
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $userData = $result->fetch_all(MYSQLI_ASSOC); // Fetch all user data as an associative array
        echo json_encode($userData); // Return user data as JSON
    } else {
        echo json_encode(["error" => "User not found."]); // Return an error if no user is found
    }
    
    $stmt->close();
}

// Function to verify the reset code
function verifyResetCode($conn, $code) {
    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM `user-staff` WHERE waiter_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Code exists
        echo json_encode(["status" => "success", "message" => "Code verified successfully."]);
    } else {
        // Code does not exist
        echo json_encode(["error" => "Invalid code. Please try again."]);
    }

    $stmt->close();
}

// Function to get all data from total_sales
function getAllTotalSales($conn)
{
    $sql = "SELECT DISTINCT date, sales_code FROM total_sales"; // Query to select unique dates and sales codes
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $salesData = $result->fetch_all(MYSQLI_ASSOC); // Fetch all unique sales data as an associative array
        echo json_encode($salesData); // Return unique sales data as JSON
    } else {
        echo json_encode(["message" => "No sales data found."]); // Handle case with no data
    }
}

// Function to get sales by date and shift
function getSalesByDateAndShift($conn)
{
    $date = isset($_GET['date']) ? $_GET['date'] : null; // Retrieve date from query parameters
    $shift = isset($_GET['shift']) ? $_GET['shift'] : null; // Retrieve shift from query parameters

    if ($date && $shift) {
        // Update SQL query to calculate the sum of total_amount for the specified date and shift
        $sql = "SELECT SUM(total_amount) AS total_sales FROM total_sales WHERE date = ? AND cashier_shift = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $date, $shift);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['total_sales' => $row['total_sales']]);
        } else {
            echo json_encode(['total_sales' => 0]); // Return 0 if no sales found
        }
    } else {
        echo json_encode(["error" => "Date and shift parameters are required."]);
    }
    $stmt->close();
}

// Function to get total sales by code
function getTotalSalesByCode($conn)
{
    $sales_code = isset($_GET['sales_code']) ? $_GET['sales_code'] : null; // Retrieve sales_code from query parameters
    if ($sales_code) {
        $sql = "SELECT * FROM total_sales WHERE sales_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $sales_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $salesData = [];
        while ($row = $result->fetch_assoc()) {
            $salesData[] = $row;
        }
        echo json_encode($salesData);
    } else {
        echo json_encode(["error" => "Sales code parameter is required."]);
    }
    $stmt->close();
}

// Function to get total sales by date
function getTotalSalesByDate($conn)
{
    $date = isset($_GET['date']) ? $_GET['date'] : null; // Retrieve date from query parameters
    if ($date) {
        $sql = "SELECT SUM(total_amount) AS total_sales FROM total_sales WHERE date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['total_sales' => $row['total_sales']]);
        } else {
            echo json_encode(['total_sales' => 0]); // Return 0 if no sales found
        }
    } else {
        echo json_encode(["error" => "Date parameter is required."]);
    }
    $stmt->close();
}

// Function to get order counts by hour
function getOrderTime($conn)
{
    // Extract hour and count the number of orders for each hour
    $sql = "SELECT HOUR(time) AS hour, COUNT(*) AS count 
            FROM total_sales 
            WHERE HOUR(time) BETWEEN 7 AND 23
            GROUP BY HOUR(time)";
    
    $result = $conn->query($sql);
    $orderCounts = [];

    // Initialize all hours from 7 to 23 to zero
    for ($i = 7; $i <= 23; $i++) {
        $orderCounts[$i] = 0;
    }

    // Fill actual counts from database results
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hour = (int)$row['hour'];
            $orderCounts[$hour] = (int)$row['count'];
        }
    }

    // Return counts as JSON
    echo json_encode($orderCounts);
}


// Function to get sales information by sales code and date
function getSalesInformationByCodeAndDate($conn) {
    $sales_code = isset($_GET['sales_code']) ? $_GET['sales_code'] : null;
    $date = isset($_GET['date']) ? $_GET['date'] : null;

    if ($sales_code && $date) {
        // Log the values for debugging
        error_log("Sales Code: $sales_code, Date: $date");

        $stmt = $conn->prepare("SELECT * FROM total_sales WHERE sales_code = ? AND date = ?");
        $stmt->bind_param("ss", $sales_code, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $salesData = [];
        while ($row = $result->fetch_assoc()) {
            $salesData[] = $row;
        }
        echo json_encode($salesData);
    } else {
        echo json_encode(["error" => "Sales code and date are required."]);
    }
}

// Route handling based on request type
$requestMethod = $_SERVER['REQUEST_METHOD'];
switch ($requestMethod) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'getBestsellers':
                    getBestsellers($conn);
                    break;
                case 'getMenu':
                    getMenu($conn);
                    break;
                case 'getItems':
                    getItems($conn);
                    break;
                case 'getLeastsellers':
                    getLeastsellers($conn);
                    break;
                case 'getMenuDashboard':
                    getMenuDashboard($conn);
                    break;
                case 'getOrders':
                    getOrders($conn);
                    break;
                case 'getProductQty':
                    getProductQty($conn);
                    break;
                case 'getRemitReturns':
                    getRemitReturns($conn);
                    break;
                case 'getRemitSales':
                    getRemitSales($conn);
                    break;
                case 'getReturnOrders':
                    getReturnOrders($conn);
                    break;
                case 'getSalesInformation':
                    getSalesInformation($conn);
                    break;
                case 'getSalesInformationByDate':
                    getSalesInformationByDate($conn);
                    break;
                case 'getSalesInformationByCodeAndDate':
                    getSalesInformationByCodeAndDate($conn);
                    break;
                case 'getTodaySales':
                    getTodaySales($conn);
                    break;
                case 'getTotalOrders':
                    getTotalOrders($conn);
                    break;
                case 'getTotalSales':
                    getTotalSales($conn);
                    break;
                case 'getUser':
                    getUser($conn);
                    break;
                case 'updateProductQty':
                    updateProductQty($conn);
                    break;
                case 'getTotalShortage':
                    getTotalShortage($conn);
                    break;
                case 'getTodayShortage':
                    getTodayShortage($conn);
                    break;
                case 'getTodayReturnCost':
                    getTodayReturnCost($conn);
                    break;
                case 'getTotalReturnCost':
                    getTotalReturnCost($conn);
                    break;
                case 'getMonthlySales':
                    getMonthlySales($conn);
                    break;
                case 'getQueOrders':
                    getQueOrders($conn);
                    break;
                case 'getReserveTables':
                    getReserveTables($conn);
                    break;
                case 'getInfoReserveTables':
                    getInfoReserveTables($conn);
                    break;
                case 'getVouchers':
                    getVouchers($conn);
                    break;
                case 'getVouchersbyCode':
                    getVouchersbyCode($conn);
                    break;
                case 'getTableOrderDetails':
                    getTableOrderDetails($conn);
                    break;
                case 'getOrderItemsByReceipt':
                    getOrderItemsByReceipt($conn);
                    break;
                case 'getWaiterByCode':
                    getWaiterByCode($conn);
                    break;
                case 'getWaitersWithOrderCounts':
                    getWaitersWithOrderCounts($conn);
                    break;
                case 'getWaiterCodeByEmail':
                    getWaiterCodeByEmail($conn);
                    break;
                case 'getAllDataOfUser':
                    getAllDataOfUser($conn);
                    break;
                case 'getLeadingStaff':
                    getLeadingStaff($conn);
                    break;
                case 'verifyResetCode':
                    if (isset($_GET['code'])) {
                        $code = $_GET['code'];
                        verifyResetCode($conn, $code);
                    } else {
                        echo json_encode(["error" => "Code parameter is missing."]);
                    }
                    break;
                case 'getAllTotalSales':
                    getAllTotalSales($conn);
                    break;
                case 'getSalesByDateAndShift':
                    getSalesByDateAndShift($conn);
                    break;
                case 'getTotalSalesByCode':
                    getTotalSalesByCode($conn);
                    break;
                case 'getTotalSalesByDate':
                    getTotalSalesByDate($conn);
                    break;
                case 'getOrderTime':
                    getOrderTime($conn);
                    break;
                default:
                    echo json_encode(["status" => "error", "message" => "Invalid action"]);
                    break;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No action specified"]);
        }
        break;
    case 'POST':
        // Handle POST requests if needed
        break;
    default:
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
        break;
}

// Close the connection
$conn->close();
