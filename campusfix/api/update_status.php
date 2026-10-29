<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $complaint_id = intval($_POST['complaint_id'] ?? 0);
    $new_status = sanitizeInputs($_POST['new_status'] ?? '');
    $user_id = (int)$_SESSION['user_id'];
    $role = $_SESSION['role'] ?? '';

    if ($complaint_id <= 0 || empty($new_status)) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT user_id, status FROM complaints WHERE id = ?");
    $stmt->execute([$complaint_id]);
    $c = $stmt->fetch();

    if ($c && isTransitionAllowed($c['status'], $new_status, $role)) {
        if ($role === 'Student' && (int)$c['user_id'] !== $user_id) {
            die("Unauthorized access.");
        }

        $pdo->beginTransaction();
        try {
            if ($new_status === 'Reopened') {
                $stmtDel = $pdo->prepare("DELETE FROM assignments WHERE complaint_id = ?");
                $stmtDel->execute([$complaint_id]);

                $stmt = $pdo->prepare("UPDATE complaints SET status = 'Reopened', approval_status = 'Pending', assigned_to = NULL WHERE id = ?");
                $stmt->execute([$complaint_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $complaint_id]);
            }

            $stmtLast = $pdo->prepare("SELECT status FROM complaint_history WHERE complaint_id = ? ORDER BY timestamp DESC, id DESC LIMIT 1");
            $stmtLast->execute([$complaint_id]);
            $lastHStatus = $stmtLast->fetchColumn();

            if ($lastHStatus !== $new_status) {
                $remarks = ($new_status === 'Reopened') ? 'Reopened by student' : 'Updated status';
                $stmtLog = $pdo->prepare("INSERT INTO complaint_history (complaint_id, status, remarks, updated_by) VALUES (?, ?, ?, ?)");
                $stmtLog->execute([$complaint_id, $new_status, $remarks, $user_id]);
            }

            $pdo->commit();
            header("Location: " . BASE_URL . "/view_complaint.php?id=" . $complaint_id);
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: " . BASE_URL . "/view_complaint.php?id=" . $complaint_id . "&error=update_failed");
            exit();
        }
    } else {
        die("Invalid or unauthorized status transition.");
    }
} else {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}
