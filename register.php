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
$error = $success = '';
$old = ['full_name' => '', 'username' => '', 'email' => ''];

if (isset($db_error)) {
    // Database connection failed; show message and skip registration logic
    $error = 'Database connection error: ' . htmlspecialchars($db_error);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfFromPost = $_POST['csrf_token'] ?? '';
    $csrfValid = validateCsrfToken($csrfFromPost);

    if (!$csrfValid) {
        $error = 'Security token expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $old['full_name'] = htmlspecialchars($full_name);
        $old['username'] = htmlspecialchars($username);
        $old['email'] = htmlspecialchars($email);

        if ($username === '' || $email === '' || $full_name === '' || $password === '' || $confirm === '') {
            $error = 'All fields are required for registration.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,20}$/', $username)) {
            $error = 'Username must be 3-20 characters and contain only letters, numbers, underscore, dot or hyphen.';
        } else {
            try {
                if (!isset($pdo) || !$pdo) throw new Exception('Database unavailable.');

                // normalize username to lower for uniqueness
                $normalizedUsername = strtolower($username);

                $stmt = $pdo->prepare("SELECT username, email FROM users WHERE LOWER(email) = ? OR LOWER(username) = ? LIMIT 1");
                $stmt->execute([strtolower($email), $normalizedUsername]);
                $existing = $stmt->fetch();

                if ($existing) {
                    if (!empty($existing['email']) && strtolower($existing['email']) === strtolower($email)) {
                        $error = 'Email already registered.';
                    } else {
                        $error = 'Username already taken.';
                    }
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);

                    // Try common schema variants so registration works even if deployed DB has older structure.
                    $insertVariants = [
                        [
                            "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'reader')",
                            [$username, $email, $hashed, $full_name]
                        ],
                        [
                            "INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)",
                            [$username, $email, $hashed, $full_name]
                        ],
                        [
                            "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'reader')",
                            [$username, $email, $hashed]
                        ],
                        [
                            "INSERT INTO users (username, email, password) VALUES (?, ?, ?)",
                            [$username, $email, $hashed]
                        ]
                    ];

                    $inserted = false;
                    $lastInsertError = null;

                    foreach ($insertVariants as $variant) {
                        try {
                            $stmt = $pdo->prepare($variant[0]);
                            if ($stmt->execute($variant[1])) {
                                $inserted = true;
                                break;
                            }
                        } catch (PDOException $insertEx) {
                            $lastInsertError = $insertEx;

                            // Unknown column / column count mismatch; try next variant.
                            if (in_array($insertEx->getCode(), ['42S22', '21S01'], true)) {
                                continue;
                            }

                            throw $insertEx;
                        }
                    }

                    if ($inserted) {
                        $success = 'Registration successful! You can now login.';
                        // clear old values on success
                        $old = ['full_name' => '', 'username' => '', 'email' => ''];
                    } else {
                        if ($lastInsertError instanceof Exception) {
                            throw $lastInsertError;
                        }
                        $error = 'Registration failed. Please try again later.';
                    }
                }
            } catch (Exception $e) {
                $error = 'Registration error: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Register - i-Discourse Mehfil (IDM) Knowledge Hub</title><link rel="stylesheet" href="css/islamic-style.css"><link rel="stylesheet" href="css/style.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"></head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">🕌 i-Discourse Mehfil (IDM) Knowledge Hub</a>
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
                <a href="login.php" class="nav-link">Login</a>
            </div>
        </div>
    </nav>
    <div class="auth-container"><div class="auth-card"><h2>Join i-Discourse Mehfil(IDM) Knowledge Hub</h2><p>Register to access research and publications</p>
    <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <form method="POST" class="no-validate">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required value="<?php echo $old['full_name']; ?>">
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required value="<?php echo $old['username']; ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?php echo $old['email']; ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>
    <div class="auth-footer"><p>Already have an account? <a href="login.php">Login here</a></p></div></div></div>
<script src="js/main.js?v=20260629a"></script>
</body>
</html>