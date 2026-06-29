<?php
require_once 'config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$navCategories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();

// Handle user role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['role'];
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$new_role, $user_id]);
    $_SESSION['success'] = "User role updated successfully.";
}

// Get all scholars and users
$users = $pdo->query("SELECT * FROM users ORDER BY role DESC, full_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link rel="stylesheet" href="../css/islamic-style.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="../index.php">🎓 i-Discourse Mehfil (IDM)</a>
            </div>
            <div class="nav-menu">
                <a href="../dashboard.php" class="nav-link">Dashboard</a>
                <div class="dropdown">
                    <button class="dropdown-btn">Categories ▾</button>
                    <div class="dropdown-content">
                        <?php if(count($navCategories)): ?>
                            <?php foreach($navCategories as $cat): ?>
                                <a href="../search.php?category=<?php echo urlencode($cat['slug']); ?>"><?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['name_en']); ?></a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="dropdown-placeholder">No categories available</span>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="users.php" class="nav-link active">Manage Users</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="dashboard-header">
            <h1>Manage Scholars & Users</h1>
            <p>Super Admin - User Management Panel</p>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="recent-posts">
            <div class="section-header">
                <h2>All Users</h2>
            </div>
            <div class="posts-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Institution</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(substr($user['institution'], 0, 40)); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" onchange="this.form.submit()">
                                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="scholar" <?php echo $user['role'] == 'scholar' ? 'selected' : ''; ?>>Scholar</option>
                                        <option value="reader" <?php echo $user['role'] == 'reader' ? 'selected' : ''; ?>>Reader</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
<script src="../js/main.js"></script>
</body>
</html>