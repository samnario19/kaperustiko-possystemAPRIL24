    <?php

header("Access-Control-Allow-Origin: *"); // Allow all origins, or specify a domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE"); // Updated to include PUT
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "sql12759808";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?> 
