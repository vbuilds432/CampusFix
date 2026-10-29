<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusFix - College Complaint Management System</title>
    <script>
        window.getTheme = function() {
            var cookies = document.cookie ? document.cookie.split(';') : [];
            for (var i = 0; i < cookies.length; i++) {
                var pair = cookies[i].trim();
                if (pair.indexOf('theme=') === 0) {
                    return pair.substring(6) || 'light';
                }
            }
            return 'light';
        };

        window.setTheme = function(mode) {
            var nextTheme = mode;
            if (mode === 'toggle') {
                nextTheme = window.getTheme() === 'dark' ? 'light' : 'dark';
            }
            
            document.cookie = 'theme=' + nextTheme + '; expires=' + new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString() + '; path=/';

            if (document.body) {
                if (nextTheme === 'dark') {
                    document.body.classList.add('dark-mode');
                } else {
                    document.body.classList.remove('dark-mode');
                }
            }

            var button = document.getElementById('themeToggleBtn');
            if (button) {
                button.setAttribute('aria-label', nextTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
                button.setAttribute('title', nextTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
                button.textContent = nextTheme === 'dark' ? '☀️' : '🌙';
            }
            return nextTheme;
        };
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body>
<script>
    window.setTheme(window.getTheme());
</script>

<nav class="navbar navbar-expand-lg border-bottom px-3 mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-primary" href="<?= isset($_SESSION['user_id']) ? redirectByRole($_SESSION['role']) : BASE_URL . '/login.php' ?>">CampusFix</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= redirectByRole($_SESSION['role']) ?>">Dashboard</a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/track_complaint.php">Track Complaint</a>
        </li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'Student'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/create_complaint.php">New Complaint</a></li>
            <?php endif; ?>
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/manage_areas.php">Manage Areas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/manage_categories.php">Manage Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/approve_staff.php">Approve Staff</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/escalated_reports.php">Reports</a></li>
            <?php endif; ?>
        <?php endif; ?>
      </ul>
      <div class="d-flex align-items-center">
        <button type="button" id="themeToggleBtn" class="btn btn-sm theme-toggle-btn me-2" aria-label="Toggle theme" title="Toggle theme" onclick="window.setTheme('toggle')">🌙</button>

        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="me-3 fw-semibold"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?> (<?= htmlspecialchars($_SESSION['role'] ?? '') ?>)</span>
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-danger">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-primary me-2">Login</a>
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-sm btn-outline-primary">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container main-content">
