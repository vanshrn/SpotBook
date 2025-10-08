<?php
require_once 'db_config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'shopkeeper') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

$userId = $_SESSION['user_id'];
$serviceName = $_POST['service_name'] ?? '';
$duration = $_POST['duration'] ?? 0;
$description = $_POST['description'] ?? '';

try {
    // 1. Get the shop ID for the current logged-in shop keeper
    $shopStmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
    $shopStmt->execute([$userId]);
    $shopId = $shopStmt->fetchColumn();

    if (!$shopId) {
        echo json_encode(['status' => 'error', 'message' => 'Shop details not found. Please complete shop registration.']);
        exit;
    }
    
    // Get category ID from existing services
    $catStmt = $pdo->prepare("SELECT category_id FROM services WHERE shop_id = ? LIMIT 1");
    $catStmt->execute([$shopId]);
    $categoryId = $catStmt->fetchColumn();

    if (!$categoryId) {
        // If no services exist yet, get category ID from the first service created during shop registration
        $catStmt = $pdo->prepare("SELECT category_id FROM services WHERE shop_id = ? ORDER BY id ASC LIMIT 1");
        $catStmt->execute([$shopId]);
        $categoryId = $catStmt->fetchColumn();
        
        if (!$categoryId) {
            echo json_encode(['status' => 'error', 'message' => 'Shop category not found. Please contact support.']);
            exit;
        }
    }

    // 2. Insert the new service, linking it correctly to the shop and its determined category.
    $stmt = $pdo->prepare("INSERT INTO services (name, description, duration, shop_id, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$serviceName, $description, $duration, $shopId, $categoryId]);

    echo json_encode(['status' => 'success', 'message' => 'Service added successfully!']);

} catch (PDOException $e) {
    error_log("Add service error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while adding the service.']);
}
?>