<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    if (!isset($_GET['cid']) || empty(trim($_GET['cid']))) {
        echo json_encode(["status" => "error", "message" => "Complaint ID is required"]);
        exit;
    }

    $c_id = sanitizeInputs($_GET['cid']);

    $stmt = $pdo->prepare("SELECT id, complaint_id_str, title, status, priority, created_at, updated_at FROM complaints WHERE complaint_id_str = ?");
    $stmt->execute([$c_id]);
    $complaint = $stmt->fetch();

    if ($complaint) {
        $stmtHistory = $pdo->prepare("SELECT timestamp as last_updated FROM complaint_history WHERE complaint_id = ? ORDER BY timestamp DESC LIMIT 1");
        $stmtHistory->execute([$complaint['id']]);
        $history = $stmtHistory->fetch();

        $last_updated = $history ? $history['last_updated'] : $complaint['updated_at'];

        echo json_encode([
            "status" => "success",
            "data" => [
                "complaint_id"   => $complaint['complaint_id_str'],
                "title"          => $complaint['title'],
                "current_status" => $complaint['status'],
                "priority"       => $complaint['priority'],
                "created_at"     => $complaint['created_at'],
                "last_updated"   => $last_updated
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Complaint not found"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "An error occurred while fetching tracking details."]);
}
