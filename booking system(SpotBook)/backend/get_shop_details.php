<?php
require_once 'db_config.php';
session_start();
header('Content-Type: application/json');

$shopId = $_GET['shop_id'] ?? null;

if (empty($shopId)) {
    echo json_encode(['status' => 'error', 'message' => 'Shop ID is required.']);
    exit;
}

try {
    // Query only for shop_name and address (removing latitude and longitude)
    $stmt = $pdo->prepare("SELECT shop_name, address FROM shops WHERE id = ?");
    $stmt->execute([$shopId]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($shop) {
        // Fetch service name to display it on the booking page
        $serviceStmt = $pdo->prepare("SELECT name FROM services WHERE shop_id = ? LIMIT 1");
        $serviceStmt->execute([$shopId]);
        $serviceName = $serviceStmt->fetchColumn() ?: "General Service";

        echo json_encode(['status' => 'success', 'shop' => $shop, 'service_name' => $serviceName]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Shop not found.']);
    }
} catch (PDOException $e) {
    error_log("Shop details error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred.']);
}
?>