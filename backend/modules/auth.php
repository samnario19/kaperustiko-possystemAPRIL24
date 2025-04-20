<?php
include '../config/connection.php';

try {
    // Check if 'action' key exists in POST request
    if (isset($_POST['action']) && !empty($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'login') {
            // Get data from POST request for login
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Prepare and bind for login
            $stmt = $conn->prepare("SELECT password, staff_token FROM `user-staff` WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($hashedPassword, $staffToken);
            $stmt->fetch();

            if ($stmt->num_rows > 0 && password_verify($password, $hashedPassword)) {
                echo json_encode(["status" => "success", "message" => "Login successful!", "staff_token" => $staffToken]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid email or password."]); // Invalid credentials
            }

            $stmt->close();
        } elseif ($action === 'register') {
            // Get data from POST request for registration
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $middleName = $_POST['middleName'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
            $contactNumber = $_POST['contactNumber'];

            // Check if email already exists
            $check_email = $conn->prepare("SELECT staff_no FROM `user-staff` WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            $check_email->store_result();
            
            if ($check_email->num_rows > 0) {
                echo json_encode(["status" => "error", "message" => "Email already exists"]);
                $check_email->close();
                exit;
            }
            $check_email->close();
            
            // Check if contact number already exists
            $check_contact = $conn->prepare("SELECT staff_no FROM `user-staff` WHERE contactNumber = ?");
            $check_contact->bind_param("s", $contactNumber);
            $check_contact->execute();
            $check_contact->store_result();
            
            if ($check_contact->num_rows > 0) {
                echo json_encode(["status" => "error", "message" => "Contact number already exists"]);
                $check_contact->close();
                exit;
            }
            $check_contact->close();
            
            // Generate waiter code - combination of first letter of first name, first letter of last name and 4 random digits
            $waiter_code = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1)) . rand(1000, 9999);
            
            // Prepare and bind for registration
            $stmt = $conn->prepare("INSERT INTO `user-staff` (firstName, lastName, middleName, email, password, contactNumber, avatar, waiter_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $avatar = 'default.jpg'; // Default avatar
            $stmt->bind_param("ssssssss", $firstName, $lastName, $middleName, $email, $password, $contactNumber, $avatar, $waiter_code);

            // Execute the statement
            if ($stmt->execute()) {
                // Get the last inserted ID
                $staff_no = $conn->insert_id; // Get the last inserted ID
                $staff_token = $staff_no; // Set staff_token to the same value as staff_no

                // Update the staff_token in the database
                $update_stmt = $conn->prepare("UPDATE `user-staff` SET staff_token = ? WHERE staff_no = ?");
                $update_stmt->bind_param("ii", $staff_token, $staff_no);
                $update_stmt->execute();
                $update_stmt->close();

                echo json_encode(["status" => "success", "message" => "Registration successful!", "waiter_code" => $waiter_code]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
            }

            $stmt->close();
        } elseif ($action === 'verifyWaiterCode') {
            // Get waiter_code from POST request
            $waiter_code = $_POST['waiter_code'];
            
            // Prepare and bind for waiter code verification
            $stmt = $conn->prepare("SELECT firstName, lastName FROM `user-staff` WHERE waiter_code = ?");
            $stmt->bind_param("s", $waiter_code);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $waiterName = $row['firstName'] . ' ' . $row['lastName'];
                echo json_encode([
                    "status" => "success", 
                    "message" => "Waiter code verified successfully!",
                    "waiterName" => $waiterName
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid waiter code."]);
            }
            
            $stmt->close();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Action not specified. Please provide 'action' in your request."]); // More informative error message
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "An error occurred: " . $e->getMessage()]);
}

$conn->close(); 