<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Enforce role-based access control.
 *
 * @param string $role_needed Expected role name, or 'Any' for any authenticated user.
 */
function checkRole($role_needed) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
    if ($role_needed !== 'Any' && (!isset($_SESSION['role']) || $_SESSION['role'] !== $role_needed)) {
        http_response_code(403);
        die("Unauthorized access.");
    }
}

/**
 * Return landing dashboard URL based on user role.
 */
function redirectByRole($role) {
    switch ($role) {
        case 'Admin':
            return BASE_URL . '/dashboard_admin.php';
        case 'Staff':
            return BASE_URL . '/dashboard_staff.php';
        case 'Student':
            return BASE_URL . '/dashboard_student.php';
        default:
            return BASE_URL . '/login.php';
    }
}

/**
 * Calculate SLA status for a complaint.
 * - Completed: status is Resolved or Closed.
 * - Alert: Assigned but not resolved within 42 hours of assignment.
 * - Warning: Unassigned and more than 6 hours passed since creation.
 * - Normal: Within SLA limits.
 */
function getSLAStatus($created_at, $assigned_at, $status) {
    if (in_array($status, ['Resolved', 'Closed'])) {
        return 'Completed';
    }

    $now = time();
    $created = strtotime($created_at);
    $assigned = $assigned_at ? strtotime($assigned_at) : null;

    if ($assigned && $status !== 'Resolved' && (($now - $assigned) / 3600) > 42) {
        return 'Alert';
    }

    if (!$assigned && (($now - $created) / 3600) > 6) {
        return 'Warning';
    }

    return 'Normal';
}

/**
 * Calculate remaining SLA time based on a 48-hour resolution window.
 */
function getTimeLeft($created_at) {
    $now = time();
    $created = strtotime($created_at);
    $deadline = $created + (48 * 3600);
    $diff = $deadline - $now;

    if ($diff <= 0) {
        return "0 hours 0 minutes";
    }

    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    return "{$hours} hours {$minutes} minutes";
}

/**
 * Returns complete SLA metrics and badge color for UI display.
 */
function calculateSLA($pdo, $complaint_id, $current_status, $created_at, $assigned_at = null) {
    $status = getSLAStatus($created_at, $assigned_at, $current_status);
    $time_left = getTimeLeft($created_at);

    $colors = [
        'Normal'    => 'success',
        'Warning'   => 'warning',
        'Alert'     => 'danger',
        'Completed' => 'info'
    ];

    return [
        'status'    => $status,
        'color'     => $colors[$status] ?? 'secondary',
        'time_left' => ($status === 'Completed') ? 'N/A' : $time_left
    ];
}

/**
 * Validate permitted complaint status transitions per role.
 */
function isTransitionAllowed($current, $new, $role) {
    if ($current === $new) {
        return false;
    }

    $rules = [
        'Submitted'   => ['Verified' => ['Admin']],
        'Verified'    => ['Assigned' => ['Admin']],
        'Assigned'    => ['In Progress' => ['Staff', 'Admin']],
        'In Progress' => ['Resolved' => ['Staff', 'Admin']],
        'Resolved'    => ['Closed' => ['Admin'], 'Reopened' => ['Student']],
        'Closed'      => ['Reopened' => ['Student']],
        'Reopened'    => ['Assigned' => ['Admin']],
        'Forwarded'   => ['Assigned' => ['Admin'], 'Resolved' => ['Staff', 'Admin'], 'Closed' => ['Admin']]
    ];

    if (!isset($rules[$current]) || !isset($rules[$current][$new])) {
        return false;
    }

    return in_array($role, $rules[$current][$new], true);
}

/**
 * Basic input sanitization.
 */
function sanitizeInputs($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Human-readable relative time string.
 */
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $seconds = time() - $time_ago;

    if ($seconds < 60) {
        return "Just now";
    }

    $minutes = round($seconds / 60);
    if ($minutes < 60) {
        return ($minutes == 1) ? "1 minute ago" : "{$minutes} minutes ago";
    }

    $hours = round($seconds / 3600);
    if ($hours < 24) {
        return ($hours == 1) ? "1 hour ago" : "{$hours} hours ago";
    }

    $days = round($seconds / 86400);
    if ($days < 7) {
        return ($days == 1) ? "Yesterday" : "{$days} days ago";
    }

    $weeks = round($seconds / 604800);
    if ($weeks < 4.3) {
        return ($weeks == 1) ? "1 week ago" : "{$weeks} weeks ago";
    }

    $months = round($seconds / 2629440);
    if ($months < 12) {
        return ($months == 1) ? "1 month ago" : "{$months} months ago";
    }

    $years = round($seconds / 31553280);
    return ($years == 1) ? "1 year ago" : "{$years} years ago";
}
