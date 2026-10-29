<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
checkRole('Admin');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['staff_id']) && isset($_POST['action'])) {
        $staff_id = (int)$_POST['staff_id'];
        $action = $_POST['action']; // either 'Approve' or 'Reject'
        
        $new_status = ($action === 'Approve') ? 'Approved' : 'Rejected';
        
        $stmt = $pdo->prepare("UPDATE users SET approval_status = ? WHERE id = ? AND role_id = 2");
        if ($stmt->execute([$new_status, $staff_id])) {
            $success = "Staff registration {$new_status} successfully.";
        } else {
            $error = "Failed to update status.";
        }
    }
}

// Fetch pending staff applications
$stmt = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role_id = 2 AND approval_status = 'Pending' ORDER BY created_at ASC");
$pending_staff = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h2>Staff Approval Panel</h2>
    <p class="text-muted">Review incoming staff registrations.</p>
</div>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pending_staff as $staff): ?>
                    <tr>
                        <td><?= $staff['id'] ?></td>
                        <td><?= htmlspecialchars($staff['name']) ?></td>
                        <td><a href="mailto:<?= htmlspecialchars($staff['email']) ?>"><?= htmlspecialchars($staff['email']) ?></a></td>
                        <td><?= date('d M Y, h:i A', strtotime($staff['created_at'])) ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">
                                <button type="submit" name="action" value="Approve" class="btn btn-sm btn-success me-2">Approve</button>
                                <button type="submit" name="action" value="Reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this application?');">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($pending_staff)): ?>
                        <tr><td colspan="5" class="text-center py-4">No pending staff registrations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
