<?php
require_once 'db_config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit;
}

$bookingId = $_POST['booking_id'] ?? null;
$userId = $_SESSION['user_id'];

if (empty($bookingId)) {
    echo json_encode(['status' => 'error', 'message' => 'Booking ID is required.']);
    exit;
}

try {
    // Check if the booking belongs to the current user
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Booking canceled successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Booking not found or you do not have permission to cancel it.']);
    }
} catch (PDOException $e) {
    error_log("Error canceling booking: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred.']);
}
?>