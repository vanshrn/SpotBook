<?php
require_once 'db_config.php';
header('Content-Type: application/json');

$serviceId = $_GET['service_id'] ?? null;
$date = $_GET['date'] ?? null;
$shopId = $_GET['shop_id'] ?? null; // CRITICAL: Get the shop ID

if (empty($serviceId) || empty($date) || empty($shopId)) {
    echo json_encode(['status' => 'error', 'message' => 'Service ID, Shop ID, and Date are required.']);
    exit;
}

try {
    // 1. Define standard working hours and slot duration
    $startHour = 9; // 9:00 AM
    $endHour = 17;  // 5:00 PM (last slot starts at 4:30 PM)
    $slotDuration = 30; // minutes

    // 2. Get all existing booked times for the specific service, shop, and date
    // Note: It's technically more accurate to check *all* bookings for the SHOP on that date,
    // as the shop can only handle one appointment at a time (assuming single resource model).
    // We check ALL bookings for the shop to find true availability.
    $stmt = $pdo->prepare("
        SELECT b.booking_time, s.duration 
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        WHERE s.shop_id = ? AND b.booking_date = ?
    ");
    $stmt->execute([$shopId, $date]);
    $bookedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert booked slots into a simple array of start times for quick lookup
    $bookedStartTimes = array_column($bookedSlots, 'booking_time');

    // 3. Generate all possible time slots
    $availableSlots = [];
    $currentTime = strtotime("$date $startHour:00:00");
    $endTime = strtotime("$date $endHour:00:00");

    while ($currentTime < $endTime) {
        $slotTime = date('H:i:s', $currentTime);
        $slotDisplay = date('h:i A', $currentTime);

        // Check if the current slot is in the booked start times array
        if (!in_array($slotTime, $bookedStartTimes)) {
            $availableSlots[] = [
                'time_24h' => $slotTime,
                'time_display' => $slotDisplay,
                'is_available' => true
            ];
        } else {
             $availableSlots[] = [
                'time_24h' => $slotTime,
                'time_display' => $slotDisplay,
                'is_available' => false // Booked
            ];
        }

        // Move to the next slot (30 minutes)
        $currentTime = strtotime("+$slotDuration minutes", $currentTime);
    }

    echo json_encode(['status' => 'success', 'date' => $date, 'slots' => $availableSlots]);

} catch (PDOException $e) {
    error_log("Availability error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while checking availability.']);
}
?>