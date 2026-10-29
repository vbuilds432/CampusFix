<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    // Fetch complaints that are NOT Resolved/Closed and either:
    // 1. Unassigned and older than 6 hours (Initial SLA Violation -> Warning)
    // 2. Assigned and older than 42 hours since assignment (Resolution SLA Violation -> Alert)
    $stmt = $pdo->prepare("SELECT id, complaint_id_str, title, priority, status, created_at, assigned_at 
                           FROM complaints 
                           WHERE status NOT IN ('Closed', 'Resolved') 
                           AND (
                               (assigned_at IS NULL AND created_at < DATE_SUB(NOW(), INTERVAL 6 HOUR))
                               OR 
                               (assigned_at IS NOT NULL AND assigned_at < DATE_SUB(NOW(), INTERVAL 42 HOUR))
                           )");
    $stmt->execute();
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $forwarded_data = [];
    foreach ($complaints as $c) {
        $slaStatus = getSLAStatus($c['created_at'], $c['assigned_at'], $c['status']);
        
        // Only include Warning (Initial breach) and Alert (Resolution breach)
        if ($slaStatus === 'Normal' || $slaStatus === 'Completed') {
            continue;
        }

        $now = time();
        $created = strtotime($c['created_at']);
        $diff_hours = floor(($now - $created) / 3600);

        $forwarded_data[] = [
            "complaint_id" => $c['complaint_id_str'],
            "title"        => $c['title'],
            "priority"     => $c['priority'],
            "status"       => $slaStatus,
            "hours_passed" => $diff_hours
        ];
    }

    echo json_encode([
        "status"          => "success",
        "total_forwarded" => count($forwarded_data),
        "data"            => $forwarded_data
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status"  => "error",
        "message" => "An error occurred fetching forwarded complaints."
    ]);
}
