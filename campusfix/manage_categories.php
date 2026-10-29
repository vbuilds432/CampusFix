<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
checkRole('Admin');

$categories = $pdo->query("SELECT * FROM complaint_categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['add_category'])) {
        $stmt = $pdo->prepare("INSERT INTO complaint_categories (name) VALUES (?)");
        $stmt->execute([sanitizeInputs($_POST['name'])]);
        header("Location: manage_categories.php");
        exit();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h2>Manage Categories</h2>
</div>

<div class="card shadow-sm col-md-6">
    <div class="card-body">
        <form method="POST" class="d-flex mb-3">
            <input type="text" name="name" class="form-control me-2" placeholder="New Category Name" required>
            <button type="submit" name="add_category" class="btn btn-primary">Add</button>
        </form>
        <ul class="list-group">
            <?php foreach($categories as $c): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= htmlspecialchars($c['name']) ?>
                    <span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>"><?= $c['is_active'] ? 'Active' : 'Disabled' ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
