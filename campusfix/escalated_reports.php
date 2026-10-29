<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
checkRole('Admin');

// Fetch Escalated (Forwarded) Complaints Report
$stmt = $pdo->query("SELECT c.id, c.complaint_id_str, c.title, c.created_at, cat.name as cat_name, camp.name as camp_name 
                    FROM complaints c 
                    JOIN complaint_categories cat ON c.category_id = cat.id
                    JOIN campus camp ON c.campus_id = camp.id
                    WHERE TIMESTAMPDIFF(HOUR, c.created_at, NOW()) > 48 AND c.status NOT IN ('Resolved', 'Closed', 'Reopened')");
$escalated = $stmt->fetchAll();


require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h2>Escalated Complaints Report (> 42 Hours)</h2>
    <p class="text-muted">Complaints exceeding 42 hours SLA resolution time.</p>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Complaint ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Campus</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($escalated as $e): ?>
                        <tr>
                            <td><a href="<?= BASE_URL ?>/view_complaint.php?id=<?= $e['id'] ?>"><?= $e['complaint_id_str'] ?></a></td>
                            <td><?= htmlspecialchars($e['title']) ?></td>
                            <td><?= $e['cat_name'] ?></td>
                            <td><?= $e['camp_name'] ?></td>
                            <td><?= date('d M Y, h:i A', strtotime($e['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($escalated)): ?>
                            <tr><td colspan="5" class="text-center">No escalated complaints! Good job.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
