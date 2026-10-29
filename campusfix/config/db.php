<?php
/**
 * Database Configuration
 * 
 * Update credentials or environment variables as per your local setup.
 */

$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'campusfix';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed. Please verify your configuration in config/db.php.");
}

if (!defined('BASE_URL')) {
    // Determine the base web path relative to document root
    $doc_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $project_root = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    $base_url = '';
    if (!empty($doc_root) && strpos($project_root, $doc_root) === 0) {
        $base_url = substr($project_root, strlen($doc_root));
    }
    define('BASE_URL', rtrim($base_url, '/'));
}
