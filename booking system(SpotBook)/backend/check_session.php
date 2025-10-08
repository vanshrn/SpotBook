<?php
session_start();
header('Content-Type: application/json');

// Check if user_id exists in the session to determine login status
$isLoggedIn = isset($_SESSION['user_id']);

// Get the user's role from the session, defaulting to null if not set
$userRole = $_SESSION['user_role'] ?? null; 

// Output the data as JSON
echo json_encode([
    'isLoggedIn' => $isLoggedIn,
    'userRole' => $userRole
]);
?>