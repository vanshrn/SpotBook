<?php
require_once 'db_config.php';
session_start();

header('Content-Type: application/json');

// Check if a user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in to book a service.']);
    exit;
}

// Get POST data
$serviceId = $_POST['service'] ?? null;
$date = $_POST['date'] ?? null;
$time = $_POST['time'] ?? null;
$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;

// Get the user ID from the session
$userId = $_SESSION['user_id'];

// Basic server-side validation
if (empty($serviceId) || empty($date) || empty($time) || empty($name) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

try {
    // Check if the service exists
    $stmt = $pdo->prepare("SELECT id FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid service selected.']);
        exit;
    }

    // Check for existing bookings at the same time and for the same service
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE service_id = ? AND booking_date = ? AND booking_time = ?");
    $stmt->execute([$serviceId, $date, $time]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This time slot is already booked.']);
        exit;
    }

    // Insert the new booking into the database, including the user_id
    $stmt = $pdo->prepare("INSERT INTO bookings (service_id, client_name, client_email, booking_date, booking_time, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$serviceId, $name, $email, $date, $time, $userId]);

    echo json_encode(['status' => 'success', 'message' => 'Booking successful! We will send a confirmation to your email.']);
} catch (PDOException $e) {
    // Log the error for debugging
    error_log("Booking error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again later.']);
}
?>