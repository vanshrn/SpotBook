<?php
require_once 'db_config.php';
// session_start(); // NOTE: No session_start needed if we are NOT checking user_id/role
//                   // However, if other backend files use it, keep it for consistency
session_start();

header('Content-Type: application/json');

// NO user role check or access check is needed here, 
// as categories must be available to populate the registration form dropdown.

try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'categories' => $categories]);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Could not retrieve categories due to database error.']);
}
?>