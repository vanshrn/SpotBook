<?php
require_once 'db_config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'shopkeeper') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied. Must be a logged-in shop keeper.']);
    exit;
}

$userId = $_SESSION['user_id'];
$shopName = $_POST['shop_name'] ?? '';
$address = $_POST['address'] ?? '';
$categoryId = $_POST['category_id'] ?? null; 

if (empty($categoryId)) {
    echo json_encode(['status' => 'error', 'message' => 'Shop category is required.']);
    exit;
}

try {
    // 1. Check if shop already exists
    $checkStmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
    $checkStmt->execute([$userId]);
    if ($checkStmt->fetchColumn()) {
        echo json_encode(['status' => 'error', 'message' => 'Shop already registered.']);
        exit;
    }

    // 2. Insert the new shop details
    // CRITICAL FIX: Removed 'latitude' and 'longitude' from the query
    $stmt = $pdo->prepare("INSERT INTO shops (user_id, shop_name, address) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $shopName, $address]);

    // 3. Since the category ID is necessary for the customer flow, we must link the first service now.
    // NOTE: In a cleaner design, you would skip this, but this guarantees the shop is visible.
    
    // Get the ID of the shop that was just inserted
    $newShopId = $pdo->lastInsertId(); 
    
    // Insert one default service for visibility, using the selected category ID
    $serviceSql = "INSERT INTO services (name, description, duration, shop_id, category_id) VALUES (?, ?, ?, ?, ?)";
    $serviceStmt = $pdo->prepare($serviceSql);
    
    // Insert a simple placeholder service linked to the selected category
    $serviceStmt->execute([
        'Consultation Slot',          
        'General booking slot for consultation.',
        30,                          
        $newShopId,                   
        $categoryId                   
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Shop registered and service linked successfully!']);
} catch (PDOException $e) {
    error_log("Shop registration error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred during shop registration.']);
}
?>