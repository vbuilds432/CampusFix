<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
checkRole('Any');

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$complaint_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch Complaint Details
$stmt = $pdo->prepare("SELECT c.*, cat.name as cat_name, camp.name as camp_name, u.name as student_name
                      FROM complaints c 
                      JOIN complaint_categories cat ON c.category_id = cat.id 
                      JOIN campus camp ON c.campus_id = camp.id 
                      JOIN users u ON c.user_id = u.id
                      WHERE c.id = ?");
$stmt->execute([$complaint_id]);
$c = $stmt->fetch();

if (!$c) die("Complaint not found.");

// Auth check - Staff can only view assigned, Students can only view their own
if ($role == 'Student' && $c['user_id'] != $user_id) die("Unauthorized.");
if ($role == 'Staff') {
    $stmt = $pdo->prepare("SELECT id FROM assignments WHERE complaint_id = ? AND staff_id = ?");
    $stmt->execute([$complaint_id, $user_id]);
    if (!$stmt->fetch()) die("Unauthorized. Complaint not assigned to you.");
}

$error = '';
$success = '';

// Handle Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($role == 'Admin' || $role == 'Staff')) {
    
    // Status update logic
    if (isset($_POST['update_status'])) {
        $new_status = $_POST['new_status'];
        $remarks = sanitizeInputs($_POST['remarks']);
        
        if (!isTransitionAllowed($c['status'], $new_status, $role)) {
            $error = "Transition from " . $c['status'] . " to " . $new_status . " is not allowed.";
        } else {
            $pdo->beginTransaction();
            try {
                if ($new_status == 'Forwarded') {
                    throw new Exception("Manually setting status to Forwarded is not allowed.");
                }
                $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $complaint_id]);
                
                // Resolution file logic truncation...
                if ($new_status == 'Resolved' && isset($_FILES['resolution_file']) && $_FILES['resolution_file']['error'] == UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['resolution_file']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
                        $new_name = uniqid('res_', true) . '.' . $ext;
                        $upload_dir = 'uploads/resolutions/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                        if (move_uploaded_file($_FILES['resolution_file']['tmp_name'], $upload_dir . $new_name)) {
                            $stmtAttach = $pdo->prepare("INSERT INTO attachments (complaint_id, file_name, file_path, file_type, uploaded_by) VALUES (?, ?, ?, 'Resolution Proof', ?)");
                            $stmtAttach->execute([$complaint_id, $_FILES['resolution_file']['name'], $upload_dir . $new_name, $user_id]);
                        }
                    }
                }

                // Check for duplicate history
                $stmtLast = $pdo->prepare("SELECT status FROM complaint_history WHERE complaint_id = ? ORDER BY timestamp DESC, id DESC LIMIT 1");
                $stmtLast->execute([$complaint_id]);
                $lastHStatus = $stmtLast->fetchColumn();

                if ($lastHStatus !== $new_status) {
                    $stmtLog = $pdo->prepare("INSERT INTO complaint_history (complaint_id, status, remarks, updated_by) VALUES (?, ?, ?, ?)");
                    $stmtLog->execute([$complaint_id, $new_status, $remarks, $user_id]);
                }
                
                $pdo->commit();
                $success = "Status updated successfully.";
                

                
                $c['status'] = $new_status; // update local
            } catch(Exception $e) {
                $pdo->rollBack();
                $error = "Failed to update.";
            }
        }
    }

    // Assignment Logic (Admin only)
    if (isset($_POST['assign_staff']) && $role == 'Admin') {
        $staff_id = $_POST['staff_id'];
        
        // Validation: Verify selected staff_id exists, role is Staff, and approval_status is Approved
        $stmt_check = $pdo->prepare("SELECT u.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ? AND r.name = 'Staff' AND u.approval_status = 'Approved'");
        $stmt_check->execute([$staff_id]);
        $staffData = $stmt_check->fetch();

        if (!$staffData) {
            $error = "Invalid staff selection";
        }
        elseif (!isTransitionAllowed($c['status'], 'Assigned', $role)) {
            $error = "Assignment not allowed in current status: " . $c['status'];
        }
        elseif ($c['status'] == 'Reopened' && $c['approval_status'] != 'Approved') {
            $error = "Reopened complaints must be approved before reassignment.";
        } else {
            $staff_name = $staffData['name'];
            $pdo->beginTransaction();
            try {
                // Remove previous assignments if any
                $stmt = $pdo->prepare("DELETE FROM assignments WHERE complaint_id = ?");
                $stmt->execute([$complaint_id]);
                
                $stmt = $pdo->prepare("INSERT INTO assignments (complaint_id, staff_id, assigned_by) VALUES (?, ?, ?)");
                $stmt->execute([$complaint_id, $staff_id, $user_id]);
                
                $stmt = $pdo->prepare("UPDATE complaints SET status = 'Assigned', assigned_to = ?, assigned_at = NOW() WHERE id = ?");
                $stmt->execute([$staff_id, $complaint_id]);
                
                // Check for duplicate history
                $stmtLast = $pdo->prepare("SELECT status FROM complaint_history WHERE complaint_id = ? ORDER BY timestamp DESC, id DESC LIMIT 1");
                $stmtLast->execute([$complaint_id]);
                $lastHStatus = $stmtLast->fetchColumn();

                if ($lastHStatus !== 'Assigned') {
                    $stmtLog = $pdo->prepare("INSERT INTO complaint_history (complaint_id, status, remarks, updated_by) VALUES (?, 'Assigned', ?, ?)");
                    $remarks = "Assigned to " . $staff_name;
                    $stmtLog->execute([$complaint_id, $remarks, $user_id]);
                }
                
                $pdo->commit();
                $success = "Assigned successfully.";
                

                
                $c['status'] = 'Assigned';
            } catch(Exception $e) {
                $pdo->rollBack();
                $error = "Assignment failed.";
            }
        }
    }
    
    // Approval Logic (Admin only) for reopened complaints
    if(isset($_POST['approve_reopen']) && $role == 'Admin') {
        $stmt = $pdo->prepare("UPDATE complaints SET approval_status = 'Approved' WHERE id = ?");
        $stmt->execute([$complaint_id]);
        $c['approval_status'] = 'Approved';
        
        $stmtLog = $pdo->prepare("INSERT INTO complaint_history (complaint_id, status, remarks, updated_by) VALUES (?, 'Approved', 'Reopen approved by admin', ?)");
        $stmtLog->execute([$complaint_id, $user_id]);
        
        $success = "Reopen request approved. Please assign staff to continue.";
        

    }
    
    if(isset($_POST['reject_reopen']) && $role == 'Admin') {
        $stmt = $pdo->prepare("UPDATE complaints SET status = 'Closed', approval_status = 'Rejected', assigned_to = NULL WHERE id = ?");
        $stmt->execute([$complaint_id]);
        $c['approval_status'] = 'Rejected';
        $c['status'] = 'Closed';
        
        $stmtLog = $pdo->prepare("INSERT INTO complaint_history (complaint_id, status, remarks, updated_by) VALUES (?, 'Closed', 'Reopen request rejected by admin', ?)");
        $stmtLog->execute([$complaint_id, $user_id]);
        
        $success = "Reopen request rejected. Complaint remains Closed.";
        

    }
}

// Student Reopen Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reopen_complaint']) && $role == 'Student') {
    $remarks = sanitizeInputs($_POST['reopen_reason']);
    if (empty(trim($remarks))) {
        $error = "Please provide a reason for reopening.";
    } elseif (!isTransitionAllowed($c['status'], 'Reopened', $role)) {
        $error = "Reopening is only allowed for Resolved or Closed complaints.";
    } else {
        $pdo->beginTransaction();
        try {
            // HARD RESET: Remove any previous assignments completely
            $stmtDel = $pdo->prepare("DELETE FROM assignments WHERE complaint_id = ?");
            $stmtDel->execute([$complaint_id]);

            $stmt = $pdo->prepare("UPDATE complaints SET status = 'Reopened', approval_status = 'Pending', assigned_to = NULL, assigned_at = NULL, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$complaint_id]);
            
            $stmtLog = $pdo->prepare("INSERT INTO complaint_history (complaint_id, status, remarks, updated_by) VALUES (?, 'Reopened', ?, ?)");
            $stmtLog->execute([$complaint_id, $remarks, $user_id]);
            
            $pdo->commit();
            $success = "Reopen request submitted. Waiting for Admin action.";
            

            
            $c['status'] = 'Reopened';
            $c['approval_status'] = 'Pending';
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = "Failed to reopen complaint.";
        }
    }
}


// Fetch Attachments
$stmt = $pdo->prepare("SELECT * FROM attachments WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
$attachments = $stmt->fetchAll();

// Fetch History
$stmt = $pdo->prepare("SELECT h.*, u.name as admin_name, u.role_id, r.name as role 
                      FROM complaint_history h JOIN users u ON h.updated_by = u.id JOIN roles r ON u.role_id = r.id
                      WHERE h.complaint_id = ? ORDER BY h.timestamp ASC");
$stmt->execute([$complaint_id]);
$history = $stmt->fetchAll();

// Fetch Staff for Assignment
$staffs = [];
if ($role == 'Admin') {
    $stmt_staff = $pdo->prepare("SELECT u.id, u.name FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'Staff' AND u.approval_status = 'Approved'");
    $stmt_staff->execute();
    $staffs = $stmt_staff->fetchAll();
}

$sla = calculateSLA($pdo, $c['id'], $c['status'], $c['created_at'], $c['assigned_at']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Complaint Details: <?= htmlspecialchars($c['complaint_id_str']) ?></h2>
    <a href="<?= redirectByRole($role) ?>" class="btn btn-outline-secondary">Back to Dashboard</a>
</div>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="row">
    <!-- Summary -->
    <div class="col-md-7 border-end">
        <h4 class="mb-3">Overview</h4>
        <table class="table table-bordered">
            <tr><th>Student Name</th><td><?= htmlspecialchars($c['student_name']) ?></td></tr>
            <tr><th>Title</th><td><?= htmlspecialchars($c['title']) ?></td></tr>
            <tr><th>Category</th><td><?= htmlspecialchars($c['cat_name'] . ($c['other_category'] ? ' ('.$c['other_category'].')' : '')) ?></td></tr>
            <tr><th>Location</th><td><?= htmlspecialchars($c['camp_name'] . ' -> ' . $c['building_name'] . ' -> ' . $c['spot_name']) ?> (<?= htmlspecialchars($c['exact_location']) ?>)</td></tr>
            <tr><th>Priority</th><td><span class="badge bg-<?= $c['priority'] == 'High' ? 'danger' : ($c['priority'] == 'Medium' ? 'warning' : 'info') ?>"><?= $c['priority'] ?></span></td></tr>
            <tr><th>Current Status</th><td>
                <span class="badge bg-<?= $c['status'] == 'Forwarded' ? 'danger' : 'secondary' ?>">
                    <?= $c['status'] == 'Forwarded' ? '⚠ SLA Breached' : $c['status'] ?>
                </span>
                <?php if($c['status'] == 'Reopened'): ?>
                    <span class="badge bg-warning text-dark">Waiting for Admin Action</span>
                <?php endif; ?>
            </td></tr>
            <tr><th>SLA Status</th><td>
                <span class="badge bg-<?= $sla['color'] ?>"><?= $sla['status'] ?></span>
                <?php if ($sla['status'] != 'Completed'): ?>
                    <small class="text-muted ms-2 sla-countdown" data-created="<?= $c['created_at'] ?>" data-status="<?= $sla['status'] ?>"><?= $sla['time_left'] ?></small>
                <?php endif; ?>
            </td></tr>
        </table>
        
        <h5 class="mt-4">Description</h5>
        <div class="p-3 bg-light border rounded text-dark mb-4">
            <?= nl2br(htmlspecialchars($c['description'])) ?>
        </div>

        <h5 class="mt-4">Attachments</h5>
        <?php if(empty($attachments)): ?>
            <p>No attachments.</p>
        <?php else: ?>
            <ul class="list-group">
            <?php foreach($attachments as $att): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $att['file_type'] ?>: <?= htmlspecialchars($att['file_name']) ?>
                    <a href="<?= BASE_URL ?>/<?= $att['file_path'] ?>" target="_blank" class="btn btn-sm btn-primary">View File</a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Actions & Timeline -->
    <div class="col-md-5 ps-4">
        
        <?php if($role == 'Admin' || $role == 'Staff'): ?>
            <div class="card mb-4 shadow-sm border-primary">
                <div class="card-header bg-primary text-white">Action Panel</div>
                <div class="card-body">
                    
                    <?php if($role == 'Admin' && !in_array($c['status'], ['Resolved', 'Closed'])): ?>
                        <?php if($c['status'] == 'Reopened' && $c['approval_status'] == 'Pending'): ?>
                            <form method="POST" class="mb-3 text-center border p-2 bg-light">
                                <p class="text-danger fw-bold mb-2">Waiting for Admin Approval</p>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="approve_reopen" class="btn btn-success flex-fill">Approve</button>
                                    <button type="submit" name="reject_reopen" class="btn btn-danger flex-fill">Reject</button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <form method="POST" class="mb-3 border-bottom pb-3">
                            <label class="mb-1 fw-bold">Assign to Staff</label>
                            <div class="input-group">
                                <?php if (empty($staffs)): ?>
                                    <select name="staff_id" class="form-select" disabled>
                                        <option value="">No approved staff available</option>
                                    </select>
                                    <button type="submit" name="assign_staff" class="btn btn-outline-primary" disabled>Assign</button>
                                <?php else: ?>
                                    <select name="staff_id" class="form-select" required>
                                        <option value="">Select Staff...</option>
                                        <?php foreach($staffs as $s): ?>
                                            <option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="assign_staff" class="btn btn-outline-primary" <?= ($c['status'] == 'Reopened' && $c['approval_status'] != 'Approved') ? 'disabled' : '' ?>>Assign</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <fieldset <?= ($c['status'] == 'Reopened' && $c['approval_status'] == 'Pending') ? 'disabled' : '' ?>>
                            <label class="mb-1 fw-bold">Update Status</label>
                            <select name="new_status" class="form-select mb-2" id="status_select" required>
                                <?php if($role == 'Admin'): ?>
                                    <option value="Verified">Verified</option>
                                    <option value="Closed">Closed</option>
                                <?php endif; ?>
                                <?php if($role == 'Staff'): ?>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Resolved">Resolved</option>
                                <?php endif; ?>
                            </select>
                            <textarea name="remarks" class="form-control mb-2" rows="2" placeholder="Remarks..." required></textarea>
                            
                            <div id="resolution_upload_div" style="display:none;" class="mb-2">
                                <label class="small text-muted mb-1">Upload Resolution Proof (Image/PDF)</label>
                                <input type="file" name="resolution_file" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                            </div>

                            <button type="submit" name="update_status" class="btn btn-success w-100">Update Status</button>
                        </fieldset>
                    </form>
                </div>
            </div>
            
            <script>
                // Show file upload only when Resolved selected
                $('#status_select').change(function() {
                    if($(this).val() == 'Resolved') {
                        $('#resolution_upload_div').show();
                    } else {
                        $('#resolution_upload_div').hide();
                    }
                });
            </script>
        <?php endif; ?>
        
        <?php if($role == 'Student' && $c['status'] == 'Closed'): ?>
            <div class="card mb-4 shadow-sm border-warning">
                <div class="card-body">
                    <p class="mb-2 fw-semibold">Is the issue not solved properly?</p>
                    <form method="POST">
                        <div class="mb-3">
                            <textarea name="reopen_reason" class="form-control" placeholder="Provide reason for reopening..." required></textarea>
                        </div>
                        <button type="submit" name="reopen_complaint" class="btn btn-warning w-100">Reopen Complaint</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>


        <h4 class="mb-3">Timeline</h4>
        <div class="timeline">
            <?php foreach($history as $h): ?>
                <div class="timeline-item">
                    <span class="badge bg-<?= $h['status'] == 'Forwarded' ? 'danger' : 'secondary' ?> mb-1"><?= $h['status'] == 'Forwarded' ? '⚠ SLA Breached' : $h['status'] ?></span>
                    <small class="text-muted ms-2"><?= date('d M Y, h:i A', strtotime($h['timestamp'])) ?></small>
                    <p class="mb-0 mt-1"><?= htmlspecialchars($h['remarks']) ?></p>
                    <small class="text-muted">By: <?= htmlspecialchars($h['admin_name']) ?> (<?= $h['role'] ?>)</small>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

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
