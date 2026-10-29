<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . redirectByRole($_SESSION['role']));
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role_name'] === 'Staff') {
                if ($user['approval_status'] === 'Pending') {
                    $error = "Your staff account is currently awaiting Admin approval.";
                } elseif ($user['approval_status'] === 'Rejected') {
                    $error = "Your staff application was not approved.";
                } else {
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role_name'];
                    $_SESSION['approval_status'] = $user['approval_status'];
                    header("Location: " . redirectByRole($user['role_name']));
                    exit();
                }
            } else {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role_name'];
                $_SESSION['approval_status'] = $user['approval_status'];
                header("Location: " . redirectByRole($user['role_name']));
                exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Login to CampusFix</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </form>

                <div class="mt-3 text-center">
                    <p class="mb-0">Don't have an account? <a href="<?= BASE_URL ?>/register.php">Register here</a></p>
                    <p class="mt-1"><a href="<?= BASE_URL ?>/track_complaint.php">Track a complaint without logging in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
