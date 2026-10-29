<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT c.*, cat.name as cat_name, camp.name as camp_name 
                           FROM complaints c 
                           JOIN complaint_categories cat ON c.category_id = cat.id 
                           JOIN campus camp ON c.campus_id = camp.id 
                           WHERE c.id = ?");
    $stmt->execute([$id]);
    $complaint = $stmt->fetch();
    echo json_encode($complaint ? $complaint : ['error' => 'Not found']);
} else {
    echo json_encode(['error' => 'ID missing']);
}
