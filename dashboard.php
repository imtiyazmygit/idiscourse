<?php
require_once 'config/database.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('login.php');
}

$navCategories = [];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$isAdmin = ($user_role === 'admin');

$totalPosts = $draftPosts = 0;
$totalViews = 0;
$recentPosts = [];
$totalScholars = 0;

if (isset($pdo) && $pdo) {
    try {
        $navCategories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();

        // Get user's posts (for scholars, only their own; for admin, all posts)
        if ($isAdmin) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM posts");
            $stmt->execute();
            $totalPosts = $stmt->fetch()['total'];
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM posts WHERE status = 'draft'");
            $stmt->execute();
            $draftPosts = $stmt->fetch()['total'];
            
            $stmt = $pdo->prepare("SELECT SUM(views) as total FROM posts");
            $stmt->execute();
            $totalViews = $stmt->fetch()['total'] ?? 0;
            
            $stmt = $pdo->prepare("SELECT p.*, u.full_name as author_name FROM posts p JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC LIMIT 10");
            $stmt->execute();
            $recentPosts = $stmt->fetchAll();
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM posts WHERE author_id = ?");
            $stmt->execute([$user_id]);
            $totalPosts = $stmt->fetch()['total'];
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM posts WHERE author_id = ? AND status = 'draft'");
            $stmt->execute([$user_id]);
            $draftPosts = $stmt->fetch()['total'];
            
            $stmt = $pdo->prepare("SELECT SUM(views) as total FROM posts WHERE author_id = ?");
            $stmt->execute([$user_id]);
            $totalViews = $stmt->fetch()['total'] ?? 0;
            
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE author_id = ? ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$user_id]);
            $recentPosts = $stmt->fetchAll();
        }

        // Get total users count for admin
        if ($isAdmin) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'scholar'");
            $totalScholars = $stmt->fetch()['total'];
        }
    } catch (Exception $e) {
        $db_error = $db_error ?? $e->getMessage();
    }
} else {
    $db_error = $db_error ?? 'Database unavailable.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - i-Discourse Mehfil (IDM)</title>
    <link rel="stylesheet" href="css/islamic-style.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">🎓 i-Discourse Mehfil (IDM)</a>
                <span class="malaysia-badge">Knowledge Hub</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
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
                <a href="write-post.php" class="nav-link btn-primary">✍️ Write New</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <p><?php echo $isAdmin ? 'Super Administrator Dashboard' : 'Your Scholarly Publishing Dashboard'; ?></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <h3><?php echo $totalPosts; ?></h3>
                    <p>Total Posts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✏️</div>
                <div class="stat-info">
                    <h3><?php echo $draftPosts; ?></h3>
                    <p>Drafts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👁️</div>
                <div class="stat-info">
                    <h3><?php echo number_format($totalViews); ?></h3>
                    <p>Total Views</p>
                </div>
            </div>
            <?php if($isAdmin): ?>
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-info">
                    <h3><?php echo $totalScholars; ?></h3>
                    <p>Total Scholars</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if($isAdmin): ?>
        <div class="admin-section">
            <h2>Admin Controls</h2>
            <div class="admin-buttons">
                <a href="users.php" class="btn btn-secondary">👥 Manage All Scholars</a>
                <a href="about.php" class="btn btn-secondary">⚙️ Site Info</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="recent-posts">
            <div class="section-header">
                <h2><?php echo $isAdmin ? 'All Scholars Publications' : 'Your Publications'; ?></h2>
                <a href="write-post.php" class="btn-small">+ New Post</a>
            </div>
            
            <?php if(count($recentPosts) > 0): ?>
                <div class="posts-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <?php if($isAdmin): ?>
                                <th>Author</th>
                                <?php endif; ?>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentPosts as $post): ?>
                            <tr>
                                <td><a href="view-post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars(substr($post['title'], 0, 40)); ?></a></td>
                                <?php if($isAdmin): ?>
                                <td><?php echo htmlspecialchars($post['author_name'] ?? 'Unknown'); ?></td>
                                <?php endif; ?>
                                <td><span class="badge"><?php echo str_replace('_', ' ', $post['post_type']); ?></span></td>
                                <td><span class="status-badge <?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span></td>
                                <td><?php echo number_format($post['views']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="action-btn edit">Edit</a>
                                    <a href="delete-post.php?id=<?php echo $post['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data">No posts yet. <a href="write-post.php">Write your first publication</a></p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 i-Discourse Mehfil (IDM) Knowledge Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>
<script src="js/main.js"></script>
</body>
</html>