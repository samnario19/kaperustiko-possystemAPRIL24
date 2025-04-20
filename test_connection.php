<?php
$host = 'sql12.freesqldatabase.com';
$dbname = 'sql12759808';
$user = 'sql12759808';
$password = 'B3NiNYECSl';
$port = 3306;

try {
    $connection = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $user, $password);
    echo "Connection successful!";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 