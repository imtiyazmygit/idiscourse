<?php
require_once 'config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$navCategories = [];
$total_views = 0;
$most_viewed = null;
$views_by_type = [];
$top_articles = [];

if (isset($pdo) && $pdo) {
    try {
        $navCategories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();

        // Get total views
        $total_views = $pdo->query("SELECT SUM(views) as total FROM posts")->fetch()['total'] ?? 0;

        // Get most viewed article
        $most_viewed = $pdo->query("SELECT title, views FROM posts ORDER BY views DESC LIMIT 1")->fetch();

        // Get views by post type
        $views_by_type = $pdo->query("SELECT post_type, SUM(views) as total_views, COUNT(*) as count FROM posts GROUP BY post_type ORDER BY total_views DESC")->fetchAll();

        // Get top 10 articles
        $top_articles = $pdo->query("SELECT p.id, p.title, p.views, u.full_name as author FROM posts p JOIN users u ON p.author_id = u.id WHERE p.status = 'published' ORDER BY p.views DESC LIMIT 10")->fetchAll();
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
    <title>Analytics - i-Discourse Mehfil (IDM)</title>
    <link rel="stylesheet" href="css/islamic-style.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">🎓 i-Discourse Mehfil (IDM)</a>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
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
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="dashboard-header">
            <h1>📊 View Analytics</h1>
            <p>Track article performance and reader engagement</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👁️</div>
                <div class="stat-info">
                    <h3><?php echo number_format($total_views); ?></h3>
                    <p>Total Views</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-info">
                    <h3><?php echo number_format($most_viewed['views'] ?? 0); ?></h3>
                    <p>Most Viewed (Single Article)</p>
                </div>
            </div>
        </div>

        <div class="recent-posts">
            <h2>🏆 Top 10 Most Viewed Articles</h2>
            <div class="posts-table">
                <table>
                    <thead>
                        <tr><th>Rank</th><th>Title</th><th>Author</th><th>Views</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($top_articles as $index => $article): ?>
                        <tr>
                            <td>#<?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($article['title']); ?></td>
                            <td><?php echo htmlspecialchars($article['author']); ?></td>
                            <td><?php echo number_format($article['views']); ?></td>
                            <td><a href="view-post.php?id=<?php echo $article['id']; ?>">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="recent-posts">
            <h2>📈 Views by Publication Type</h2>
            <div class="posts-table">
                <table>
                    <thead><tr><th>Type</th><th>Total Views</th><th>Number of Articles</th><th>Avg Views per Article</th></tr></thead>
                    <tbody>
                        <?php foreach($views_by_type as $type): ?>
                        <tr>
                            <td><?php echo ucfirst(str_replace('_', ' ', $type['post_type'])); ?></td>
                            <td><?php echo number_format($type['total_views']); ?></td>
                            <td><?php echo $type['count']; ?></td>
                            <td><?php echo round($type['total_views'] / $type['count']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
<script src="js/main.js"></script>
</body>
</html>