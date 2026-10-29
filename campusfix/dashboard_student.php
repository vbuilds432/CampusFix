<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
checkRole('Student');

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT c.*, cat.name as cat_name, camp.name as camp_name 
                      FROM complaints c 
                      JOIN complaint_categories cat ON c.category_id = cat.id 
                      JOIN campus camp ON c.campus_id = camp.id 
                      WHERE c.user_id = ? ORDER BY c.created_at DESC");
$stmt->execute([$user_id]);
$complaints = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>My Complaints</h2>
    <a href="<?= BASE_URL ?>/create_complaint.php" class="btn btn-primary">New Complaint</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>SLA Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($complaints as $c): 
                        $sla = calculateSLA($pdo, $c['id'], $c['status'], $c['created_at'], $c['assigned_at']);
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['complaint_id_str']) ?></strong></td>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td><?= htmlspecialchars($c['cat_name']) ?></td>
                        <td><?= htmlspecialchars($c['camp_name'] . ' -> ' . $c['building_name'] . ' -> ' . $c['spot_name']) ?></td>
                        <td><?= $c['priority'] ?></td>
                        <td><?= $c['status'] == 'Forwarded' ? 'SLA Breached' : $c['status'] ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-<?= $sla['color'] ?> me-2"><?= $sla['status'] ?></span>
                                <small class="text-muted sla-countdown" data-created="<?= $c['created_at'] ?>" data-status="<?= $sla['status'] ?>"><?= $sla['time_left'] ?></small>
                            </div>
                        </td>
                        <td><?= date('d M Y, h:i A', strtotime($c['created_at'])) ?></td>
                        <td><a href="<?= BASE_URL ?>/view_complaint.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($complaints)): ?>
                        <tr><td colspan="8" class="text-center py-4">No complaints submitted yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function updateSLATimers() {
        $('.sla-countdown').each(function() {
            let status = $(this).data('status');
            if (status === 'Completed' || status === 'N/A') return;

            let createdAt = new Date($(this).data('created')).getTime();
            let now = new Date().getTime();
            let deadline = createdAt + (48 * 60 * 60 * 1000);
            let diff = deadline - now;

            if (diff <= 0) {
                $(this).text('0h 0m');
                return;
            }

            let hours = Math.floor(diff / (1000 * 60 * 60));
            let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            $(this).text(hours + 'h ' + minutes + 'm');
        });
    }

    setInterval(updateSLATimers, 60000);
    updateSLATimers();
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
