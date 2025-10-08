<?php
require_once 'db_config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'shopkeeper') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

$serviceId = $_POST['service_id'] ?? null;
$userId = $_SESSION['user_id'];

if (empty($serviceId)) {
    echo json_encode(['status' => 'error', 'message' => 'Service ID is required.']);
    exit;
}

try {
    // 1. Get the shop ID for the current logged-in shop keeper
    $shopStmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
    $shopStmt->execute([$userId]);
    $shopId = $shopStmt->fetchColumn();

    if (!$shopId) {
        echo json_encode(['status' => 'error', 'message' => 'Shop details not found.']);
        exit;
    }
    
    // 2. CRITICAL: Delete the service, but only if it belongs to this specific shop.
    // NOTE: If this service has active bookings, the database will throw a foreign key error (1451).
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND shop_id = ?");
    $stmt->execute([$serviceId, $shopId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Service deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Service not found or you do not have permission to delete it.']);
    }

} catch (PDOException $e) {
    // Check for specific Foreign Key Constraint failure (MySQL Error 1451/23000)
    if ($e->getCode() == '23000') {
        echo json_encode(['status' => 'error', 'message' => 'Cannot delete service: Active bookings rely on this service. Please cancel them first.']);
    } else {
        error_log("Delete service error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred during deletion.']);
    }
}
?>