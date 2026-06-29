<?php
$require_once_path = 'config/database.php';
require_once $require_once_path;
if (isLoggedIn()) redirect('index.php');
$navCategories = [];
if (isset($pdo) && $pdo) {
    try {
        $navCategories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();
    } catch (Exception $e) {
        $navCategories = [];
    }
}
$error = '';
$old = ['login_id' => ''];
$debugMode = isset($_GET['debug']) && $_GET['debug'] === '1';
$debugMessages = [];

if ($debugMode) {
    $debugMessages[] = 'Debug mode enabled.';
    $debugMessages[] = 'Request method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');
    $debugMessages[] = 'Session started: ' . (session_status() === PHP_SESSION_ACTIVE ? 'yes' : 'no');
}

if (isset($db_error)) {
    // Database connection failed; surface message to the login page
    $error = 'Database connection error: ' . htmlspecialchars($db_error);
    if ($debugMode) {
        $debugMessages[] = 'DB error detected: ' . $db_error;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfFromPost = $_POST['csrf_token'] ?? '';
    $csrfValid = validateCsrfToken($csrfFromPost);

    if ($debugMode) {
        $debugMessages[] = 'Posted login_id: ' . ($_POST['login_id'] ?? '(missing)');
        $debugMessages[] = 'Posted password length: ' . strlen((string)($_POST['password'] ?? ''));
        $debugMessages[] = 'CSRF in session: ' . (isset($_SESSION['csrf_token']) ? 'yes' : 'no');
        $debugMessages[] = 'CSRF valid: ' . ($csrfValid ? 'yes' : 'no');
    }

    if (!$csrfValid) {
        $error = 'Security token expired. Please try again.';
    } else {
        $loginId = trim($_POST['login_id'] ?? '');
        $password = $_POST['password'] ?? '';
        $old['login_id'] = htmlspecialchars($loginId);

        if ($loginId === '' || $password === '') {
            $error = 'Please enter both email/username and password.';
        } else {
            try {
                if (!isset($pdo) || !$pdo) throw new Exception('Database unavailable.');

                if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = ? LIMIT 1");
                    $stmt->execute([strtolower($loginId)]);
                    if ($debugMode) {
                        $debugMessages[] = 'Lookup mode: email';
                    }
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = ? LIMIT 1");
                    $stmt->execute([strtolower($loginId)]);
                    if ($debugMode) {
                        $debugMessages[] = 'Lookup mode: username';
                    }
                }

                $user = $stmt->fetch();
                $isAuthenticated = false;
                $storedPassword = $user['password'] ?? '';

                if ($debugMode) {
                    $debugMessages[] = 'User found: ' . ($user ? 'yes' : 'no');
                    $debugMessages[] = 'Stored password length: ' . strlen((string)$storedPassword);
                }

                if ($user && $storedPassword !== '' && password_verify($password, $storedPassword)) {
                    $isAuthenticated = true;
                    if ($debugMode) {
                        $debugMessages[] = 'Password check: hashed verify success';
                    }

                    // Keep password hashes up to date as hashing defaults evolve.
                    if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $upd->execute([$newHash, $user['id']]);
                    }
                } elseif ($user && $storedPassword !== '' && $password === $storedPassword) {
                    // Backward compatibility for legacy plain-text passwords; convert to hash immediately.
                    $isAuthenticated = true;
                    if ($debugMode) {
                        $debugMessages[] = 'Password check: legacy plain-text match success';
                    }
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $upd->execute([$newHash, $user['id']]);
                }

                if ($isAuthenticated) {
                    if ($debugMode) {
                        $debugMessages[] = 'Authentication result: success, redirecting to index.php';
                    }
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
                    $_SESSION['role'] = $user['role'] ?? 'reader';
                    redirect('index.php');
                } else {
                    if ($debugMode) {
                        $debugMessages[] = 'Authentication result: failed';
                    }
                    $error = 'Invalid email/username or password.';
                }
            } catch (Exception $e) {
                if ($debugMode) {
                    $debugMessages[] = 'Exception: ' . $e->getMessage();
                }
                $error = 'Login error: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Login - Islamic Knowledge Hub</title><link rel="stylesheet" href="css/islamic-style.css"><link rel="stylesheet" href="css/style.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"></head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">🕌 Islamic Knowledge Hub</a>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <div class="dropdown">
                    <button class="dropdown-btn">Categories ▾</button>
                    <div class="dropdown-content">
                        <?php if(count($navCategories)): ?>
                            <?php foreach($navCategories as $cat): ?>
                                <a href="search.php?category=<?php echo urlencode($cat['slug']); ?>"><?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['name_en']); ?></a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="dropdown-placeholder">No categories available</span>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="register.php" class="nav-link btn-primary">Register</a>
            </div>
        </div>
    </nav>
    <div class="auth-container"><div class="auth-card"><h2>Welcome Back</h2><p>Login to access your account</p>
    <?php if($debugMode && count($debugMessages)): ?>
        <div class="alert alert-success" style="font-family:monospace; font-size:12px; text-align:left;">
            <?php foreach($debugMessages as $msg): ?>
                <div><?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <div class="form-group">
            <label>Email or Username</label>
            <input type="text" name="login_id" required value="<?php echo $old['login_id']; ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    <div class="auth-footer"><p>Don't have an account? <a href="register.php">Register here</a></p></div></div></div>
<script src="js/main.js"></script>
</body>
</html>