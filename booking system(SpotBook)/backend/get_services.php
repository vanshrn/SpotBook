<?php
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, name FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($services);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Could not retrieve services.']);
}
?>