<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
checkRole('Admin');

$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('Submitted', 'Verified', 'Assigned', 'In Progress', 'Reopened')")->fetchColumn(),
    'resolved' => $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('Resolved', 'Closed')")->fetchColumn()
];

$stmt = $pdo->query("SELECT c.*, cat.name as cat_name FROM complaints c JOIN complaint_categories cat ON c.category_id = cat.id ORDER BY c.created_at DESC LIMIT 20");
$complaints = $stmt->fetchAll();

// Handle Dashboard Approval/Rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_reopen'])) {
    $comp_id = intval($_POST['complaint_id']);
    $action = $_POST['action_reopen'];
    $admin_id = $_SESSION['user_id'];
    
    if ($action == 'approve') {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE complaints SET approval_status = 'Approved' WHERE id = ?");
            $stmt->execute([$comp_id]);
            
            $stmtLog = $pdo->prepare("INSERT INTO complaint_history (complaint_id, status, remarks, updated_by) VALUES (?, 'Approved', 'Reopen approved by admin (from dashboard)', ?)");
            $stmtLog->execute([$comp_id, $admin_id]);
            
            $pdo->commit();
            header("Location: dashboard_admin.php?success=1");
            exit;
        } catch(Exception $e) { $pdo->rollBack(); }
    } elseif ($action == 'reject') {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE complaints SET status = 'Closed', approval_status = 'Rejected' WHERE id = ?");
            $stmt->execute([$comp_id]);
            
            $stmtLog = $pdo->prepare("INSERT INTO complaint_history (complaint_id, status, remarks, updated_by) VALUES (?, 'Closed', 'Reopen request rejected by admin (from dashboard)', ?)");
            $stmtLog->execute([$comp_id, $admin_id]);
            
            $pdo->commit();
            header("Location: dashboard_admin.php?success=2");
            exit;
        } catch(Exception $e) { $pdo->rollBack(); }
    }
}

// Fetch Pending Reopens
$stmt_reopens = $pdo->query("SELECT c.*, cat.name as cat_name FROM complaints c JOIN complaint_categories cat ON c.category_id = cat.id WHERE c.status = 'Reopened' AND c.approval_status = 'Pending' ORDER BY c.updated_at DESC");
$pending_reopens = $stmt_reopens->fetchAll();


require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h2>Admin Dashboard</h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_GET['success'] == 1 ? "Reopen request approved successfully!" : "Reopen request rejected." ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm mb-3">
            <div class="card-body bg-primary border rounded p-3">
                <h5 class="card-title">Total</h5>
                <h3><?= $stats['total'] ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark shadow-sm mb-3">
            <div class="card-body bg-warning border rounded p-3">
                <h5 class="card-title">Pending</h5>
                <h3><?= $stats['pending'] ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm mb-3">
            <div class="card-body bg-success border rounded p-3">
                <h5 class="card-title">Resolved</h5>
                <h3><?= $stats['resolved'] ?></h3>
            </div>
        </div>
    </div>
</div>

<div id="slaBreachesContainer" style="display:none;" class="mb-4">
    <div class="card border-danger shadow-sm">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">⚠️ SLA Breached</h5>
            <span class="badge bg-light text-danger" id="slaCountBadge">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Priority</th>
                            <th>Overdue By</th>
                        </tr>
                    </thead>
                    <tbody id="slaTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- New Reopen Requests Section -->
<?php if (!empty($pending_reopens)): ?>
<div class="card border-warning shadow-sm mb-4">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pending Reopen Requests</h5>
        <span class="badge bg-dark"><?= count($pending_reopens) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pending_reopens as $pr): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($pr['complaint_id_str']) ?></strong></td>
                            <td><?= htmlspecialchars($pr['title']) ?></td>
                            <td><?= $pr['priority'] ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="complaint_id" value="<?= $pr['id'] ?>">
                                        <button type="submit" name="action_reopen" value="approve" class="btn btn-success">Approve</button>
                                        <button type="submit" name="action_reopen" value="reject" class="btn btn-danger">Reject</button>
                                    </form>
                                    <a href="view_complaint.php?id=<?= $pr['id'] ?>" class="btn btn-outline-primary">View</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Recent Complaints</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>SLA / Action</th>
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
                        <td><?= $c['priority'] ?></td>
                        <td>
                            <?= $c['status'] == 'Forwarded' ? 'SLA Breached' : $c['status'] ?>
                            <?php if($c['status'] == 'Reopened'): ?>
                                <br><small class="text-danger fw-bold">Waiting for Admin Action</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-<?= $sla['color'] ?> me-2"><?= $sla['status'] ?></span>
                                <small class="text-muted sla-countdown" data-created="<?= $c['created_at'] ?>" data-status="<?= $sla['status'] ?>"><?= $sla['time_left'] ?></small>
                            </div>
                            <div class="mt-1">
                                <a href="<?= BASE_URL ?>/view_complaint.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($complaints)): ?>
                        <tr><td colspan="6" class="text-center py-4">No complaints found in the system.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function loadForwarded() {
        $.ajax({
            url: 'api/get_forwarded.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.total_forwarded > 0) {
                    $('#slaCountBadge').text(response.total_forwarded);
                    
                    let html = '';
                    response.data.forEach(function(item) {
                        html += `<tr>
                                    <td><strong class="text-danger">${item.complaint_id}</strong></td>
                                    <td>${item.title}</td>
                                    <td>${item.priority}</td>
                                    <td class="text-danger fw-bold">${item.hours_passed} Hours</td>
                                 </tr>`;
                    });
                    
                    $('#slaTableBody').html(html);
                    $('#slaBreachesContainer').slideDown();
                } else {
                    $('#slaBreachesContainer').hide();
                }
            }
        });
    }

    // Load immediately on page load
    loadForwarded();
    setInterval(loadForwarded, 60000);

    // Live SLA Countdown Logic
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

    setInterval(updateSLATimers, 60000); // Update every minute
    updateSLATimers(); 
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
