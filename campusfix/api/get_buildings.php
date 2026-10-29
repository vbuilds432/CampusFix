<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_GET['campus_id'])) {
    $campus_id = intval($_GET['campus_id']);
    $stmt = $pdo->prepare("SELECT id, name FROM building WHERE campus_id = ? AND status = 'Active' ORDER BY name ASC");
    $stmt->execute([$campus_id]);
    $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $buildings
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'campus_id is required'
    ]);
}
