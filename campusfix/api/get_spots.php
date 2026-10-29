<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_GET['building_id'])) {
    $building_id = intval($_GET['building_id']);
    $stmt = $pdo->prepare("SELECT id, name FROM spot WHERE building_id = ? AND status = 'Active' ORDER BY name ASC");
    $stmt->execute([$building_id]);
    $spots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $spots
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'building_id is required'
    ]);
}
