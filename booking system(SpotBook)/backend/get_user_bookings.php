<?php
require_once 'db_config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // CRITICAL FIX: Only fetch bookings where the booking_date is greater than or equal to TODAY.
    $stmt = $pdo->prepare("
        SELECT 
            b.id, s.name AS service_name, b.booking_date, b.booking_time
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        WHERE b.user_id = ? 
        AND b.booking_date >= CURDATE() 
        ORDER BY b.booking_date ASC, b.booking_time ASC
    ");
    $stmt->execute([$userId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'bookings' => $bookings]);
} catch (PDOException $e) {
    error_log("Error fetching user bookings: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred.']);
}
?>