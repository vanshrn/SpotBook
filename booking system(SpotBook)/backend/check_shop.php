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
    $stmt = $pdo->prepare("SELECT shop_name FROM shops WHERE user_id = ?");
    $stmt->execute([$userId]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($shop) {
        echo json_encode(['status' => 'registered', 'shop_name' => $shop['shop_name']]);
    } else {
        echo json_encode(['status' => 'unregistered']);
    }
} catch (PDOException $e) {
    error_log("Shop check error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error checking shop status.']);
}
?>