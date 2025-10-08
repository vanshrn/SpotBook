<?php
require_once 'db_config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'shopkeeper') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // 1. Get the shop ID for the current logged-in shop keeper
    $shopStmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
    $shopStmt->execute([$userId]);
    $shopId = $shopStmt->fetchColumn();

    if (!$shopId) {
        echo json_encode(['status' => 'error', 'message' => 'Shop details not found.']);
        exit;
    }

    // 2. Fetch all services linked to this shop, now including the DURATION.
    $stmt = $pdo->prepare("
        SELECT 
            s.id, 
            s.name, 
            s.description, /* Added for completeness, though duration is the fix */
            s.duration,    /* CRITICAL FIX: Include the duration column */
            s.category_id,
            c.name AS category_name
        FROM services s
        JOIN categories c ON s.category_id = c.id
        WHERE s.shop_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$shopId]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($services)) {
        echo json_encode(['status' => 'success', 'services' => [], 'message' => 'No services are currently linked to your shop.']);
    } else {
        echo json_encode(['status' => 'success', 'services' => $services]);
    }

} catch (PDOException $e) {
    error_log("Shop service fetch error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred fetching your services.']);
}
?>