<!-- 
This file contains instructions for modifying the insert.php file
to handle the special_instructions field.

1. Add the special_instructions field to the SQL query:

Find this line in insert.php:
$stmt = $conn->prepare("INSERT INTO orders (code, order_name, order_name2, order_quantity, order_size, order_price, order_addons, order_addons_price, order_addons2, order_addons_price2, order_addons3, order_addons_price3, order_image, basePrice) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

Replace it with:
$stmt = $conn->prepare("INSERT INTO orders (code, order_name, order_name2, order_quantity, order_size, order_price, order_addons, order_addons_price, order_addons2, order_addons_price2, order_addons3, order_addons_price3, order_image, basePrice, special_instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

2. Update the bind_param to include the special_instructions parameter:

Find this line:
$stmt->bind_param("sssisisisisisi", 
    $data['code'],
    $data['order_name'], 
    $data['order_name2'], 
    $data['order_quantity'], 
    $data['order_size'], 
    $data['order_price'], 
    $data['order_addons'], 
    $data['order_addons_price'], 
    $data['order_addons2'], 
    $data['order_addons_price2'], 
    $data['order_addons3'], 
    $data['order_addons_price3'], 
    $data['order_image'],
    $data['basePrice']
);

Replace it with:
$stmt->bind_param("sssisisisisisis", 
    $data['code'],
    $data['order_name'], 
    $data['order_name2'], 
    $data['order_quantity'], 
    $data['order_size'], 
    $data['order_price'], 
    $data['order_addons'], 
    $data['order_addons_price'], 
    $data['order_addons2'], 
    $data['order_addons_price2'], 
    $data['order_addons3'], 
    $data['order_addons_price3'], 
    $data['order_image'],
    $data['basePrice'],
    $data['special_instructions']
);

Note the added "s" in the parameter types and the additional special_instructions parameter.

3. Also update any other backend files that might be retrieving order data to include the special_instructions field.
--> 