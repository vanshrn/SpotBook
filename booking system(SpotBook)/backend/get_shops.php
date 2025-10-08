<?php
require_once 'db_config.php';
session_start();
header('Content-Type: application/json');

$categoryId = $_GET['category_id'] ?? null;

if (empty($categoryId)) {
    echo json_encode(['status' => 'error', 'message' => 'Category ID is required.']);
    exit;
}

try {
    // CRITICAL FIX: The query now joins on services.category_id, which is the permanent location for the data.
    $stmt = $pdo->prepare("
        SELECT 
            sh.id, 
            sh.shop_name, 
            sh.address,
            s.id AS service_id,
            s.name AS service_name
        FROM shops sh
        -- LEFT JOIN ensures shops with no services linked *yet* won't crash the query,
        -- but the WHERE clause ensures only shops linked to the category are visible.
        LEFT JOIN services s ON sh.id = s.shop_id 
        WHERE s.category_id = ?
        ORDER BY sh.shop_name
    ");
    $stmt->execute([$categoryId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group services by shop to prepare the final output
    $shops = [];
    foreach ($results as $row) {
        $shopId = $row['id'];
        if (!isset($shops[$shopId])) {
            $shops[$shopId] = [
                'id' => $shopId,
                'shop_name' => $row['shop_name'],
                'address' => $row['address'],
                'service_names' => [],
                'service_ids' => []
            ];
        }
        // Only add services if they actually exist (LEFT JOIN handles null service IDs)
        if ($row['service_name']) {
            $shops[$shopId]['service_names'][] = $row['service_name'];
            $shops[$shopId]['service_ids'][] = $row['service_id'];
        }
    }
    
    // Final filter: Ensure only shops that actually have services linked are shown.
    $shops = array_filter($shops, function($shop) {
        return !empty($shop['service_names']);
    });


    // Convert associative array to indexed array for JSON output
    echo json_encode(['status' => 'success', 'shops' => array_values($shops)]);

} catch (PDOException $e) {
    error_log("Error fetching shops: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Could not retrieve shops.']);
}
?>