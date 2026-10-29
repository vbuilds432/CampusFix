<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . redirectByRole($_SESSION['role']));
    exit();
}

header("Location: " . BASE_URL . "/login.php");
exit();
