<?php
session_start();

$host = '127.0.0.1:3306';
$dbname = 'u767322683_idiscourse';
$username = 'u767322683_admin';
$password = 'Hurify@1234';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Do not stop execution with die(); set an error flag so pages can show a friendly message
    $pdo = null;
    $db_error = $e->getMessage();
    error_log("Database connection failed: " . $db_error);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isProfessor() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'scholar' || $_SESSION['role'] === 'admin');
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
?>
