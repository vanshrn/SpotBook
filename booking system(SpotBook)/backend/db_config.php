<?php

define('PRODUCTION_DB_HOST', 'sql312.infinityfree.com');
define('PRODUCTION_DB_NAME', 'if0_40095387_booking_system');
define('PRODUCTION_DB_USER', 'if0_40095387');             
define('PRODUCTION_DB_PASS', 'jHvzIB4FcN7Y');  

// Database credentials
$host = PRODUCTION_DB_HOST;
$dbname = PRODUCTION_DB_NAME;
$user = PRODUCTION_DB_USER;
$pass = PRODUCTION_DB_PASS;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    // Set PDO attributes for error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set character set to UTF-8
    $pdo->exec("set names utf8");
} catch (PDOException $e) {
    // Graceful error handling for failed connection
    header('Content-Type: application/json');
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection error. Please try again later.'
    ]));
}
?>