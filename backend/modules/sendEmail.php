<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php'; // Adjusted path

$mail = new PHPMailer(true); // Create a new PHPMailer instance

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = isset($data['email']) ? trim($data['email']) : ''; // Trim whitespace
$waiterCode = isset($data['waiterCode']) ? $data['waiterCode'] : ''; // Define the waiter code

// Debugging output
error_log("Email: $email"); // Log the email address for debugging

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    exit;
}

// SMTP configuration
$mail->isSMTP(); // Set mailer to use SMTP
$mail->Host = 'smtp-relay.brevo.com'; // Specify main and backup SMTP servers
$mail->SMTPAuth = true; // Enable SMTP authentication
$mail->Username = '8a46ac001@smtp-brevo.com'; // Your Brevo SMTP login
$mail->Password = 'ygtjLcADd4JkYxvr'; // Your Brevo SMTP key
$mail->SMTPSecure = 'tls'; // Enable TLS encryption
$mail->Port = 587; // TCP port to connect to

try {
    // Prepare the email data
    $mail->setFrom('mdd23300332@gmail.com', 'Kaperustiko POS System'); // Sender's email and name
    $mail->addAddress($email); // Add a recipient
    $mail->Subject = 'Your Reset Password Code';
    $mail->addCustomHeader('X-Mailer', 'Kaperustiko POS System'); // Add custom header for validation
    $mail->isHTML(true); // Set email format to HTML
    $mail->Body = "Hello,<br><br>Your reset password code is: $waiterCode<br><br>Thank you!";
    $mail->AltBody = "Hello,\n\nYour waiter code is: $waiterCode\n\nThank you!";

    $mail->send(); // Send the email
    echo json_encode(['status' => 'success', 'message' => 'Email sent successfully.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Email sending failed: ' . $e->getMessage()]);
}
?>