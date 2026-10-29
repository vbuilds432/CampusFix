<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . redirectByRole($_SESSION['role']));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInputs($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role_id = (int)($_POST['role_id'] ?? 0);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif ($role_id !== 2 && $role_id !== 3) {
        $error = "Invalid role selected. Direct Admin registration is prohibited.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $approval_status = ($role_id === 2) ? 'Pending' : 'Approved';

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role_id, approval_status) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $role_id, $approval_status])) {
                if ($role_id === 2) {
                    $success = "Registration submitted successfully! Your staff account requires Admin approval before logging in.";
                } else {
                    $success = "Registration successful! You can now log in to submit complaints.";
                }
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Create an Account</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                        <div class="mt-2"><a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-success">Proceed to Login</a></div>
                    </div>
                <?php endif; ?>

                <?php if (empty($success)): ?>
                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="role_id" class="form-label">Register As</label>
                        <select name="role_id" id="role_id" class="form-select" required>
                            <option value="3" <?= (isset($_POST['role_id']) && $_POST['role_id'] == 3) ? 'selected' : '' ?>>Student</option>
                            <option value="2" <?= (isset($_POST['role_id']) && $_POST['role_id'] == 2) ? 'selected' : '' ?>>Staff (Maintenance / Department)</option>
                        </select>
                        <small class="text-muted">Staff accounts require Administrator approval before first login.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" minlength="6" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="6" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>
                <?php endif; ?>

                <div class="mt-3 text-center">
                    <p class="mb-0">Already have an account? <a href="<?= BASE_URL ?>/login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
