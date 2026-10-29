<?php
/**
 * Database Configuration Sample
 *
 * Copy this file to config/db.php and update with your local database credentials.
 */

$host = 'localhost';
$db_name = 'campusfix';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed. Please check config/db.php settings.");
}

if (!defined('BASE_URL')) {
    $doc_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $project_root = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    $base_url = '';
    if (!empty($doc_root) && strpos($project_root, $doc_root) === 0) {
        $base_url = substr($project_root, strlen($doc_root));
    }
    define('BASE_URL', rtrim($base_url, '/'));
}
