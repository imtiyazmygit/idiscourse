<?php
session_start();

$serverName = strtolower((string)($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? ''));
$isLocalEnv = in_array($serverName, ['localhost', '127.0.0.1'], true)
    || str_ends_with($serverName, '.local')
    || $serverName === '';

$localDefaults = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'dbname' => 'u767322683_idiscourse',
    'username' => 'root',
    'password' => '',
];

$productionDefaults = [
    'host' => 'localhost',
    'port' => '3306',
    'dbname' => 'u767322683_idiscourse',
    'username' => 'u767322683_admin',
    'password' => 'Hurify@1234',
];

$selectedDefaults = $isLocalEnv ? $localDefaults : $productionDefaults;

// Environment variables take priority when available.
$host = getenv('DB_HOST') ?: $selectedDefaults['host'];
$port = getenv('DB_PORT') ?: $selectedDefaults['port'];
$dbname = getenv('DB_NAME') ?: $selectedDefaults['dbname'];
$username = getenv('DB_USER') ?: $selectedDefaults['username'];
$password = getenv('DB_PASS') ?: $selectedDefaults['password'];
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
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
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            // Fallback for environments where random_bytes intermittently fails.
            try {
                if (function_exists('openssl_random_pseudo_bytes')) {
                    $bytes = openssl_random_pseudo_bytes(32);
                    if (is_string($bytes) && strlen($bytes) === 32) {
                        $_SESSION['csrf_token'] = bin2hex($bytes);
                    }
                }
            } catch (Throwable $ignored) {
                // Continue to deterministic fallback below.
            }

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = hash('sha256', uniqid((string) mt_rand(), true));
            }
        }
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
?>
