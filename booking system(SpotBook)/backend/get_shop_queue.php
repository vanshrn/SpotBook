<?php
require_once 'db_config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'shopkeeper') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d'); // Default to today
$userId = $_SESSION['user_id'];

try {
    // 1. Get the shop ID for the current logged-in shop keeper
    $shopStmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
    $shopStmt->execute([$userId]);
    $shopId = $shopStmt->fetchColumn();

    if (!$shopId) {
        echo json_encode(['status' => 'success', 'queue' => [], 'message' => 'Shop details not found for this account.']);
        exit;
    }

    // 2. Fetch bookings for that shop ID and date, ordered by time
    // We use INNER JOINs to ensure we only get bookings that have matching user and service records.
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_time, 
            s.name AS service_name, 
            u.username AS client_name,
            u.email AS client_email,
            ADDTIME(b.booking_time, '00:30:00') AS end_time  
        FROM bookings b
        INNER JOIN services s ON b.service_id = s.id
        INNER JOIN users u ON b.user_id = u.id
        WHERE s.shop_id = ? AND b.booking_date = ?
        ORDER BY b.booking_time
    ");
    $stmt->execute([$shopId, $date]);
    $queue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($queue)) {
         echo json_encode(['status' => 'success', 'queue' => [], 'message' => 'No bookings found for this date.']);
    } else {
        echo json_encode(['status' => 'success', 'queue' => $queue]);
    }

} catch (PDOException $e) {
    // CRITICAL: Log the detailed SQL error to the terminal
    error_log("Shop Queue fetch error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while fetching the queue.']);
}
?>