<?php
require_once 'config/database.php';

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_slug = isset($_GET['category']) ? trim($_GET['category']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$results = [];
$total_results = 0;
$total_pages = 0;
$categories = [];

// Build search query with optional category filter
$where = "WHERE p.status = 'published'";
$params = [];

if (!empty($search_query)) {
    $where .= " AND (p.title LIKE :search OR p.content LIKE :search OR p.excerpt LIKE :search)";
    $params[':search'] = "%$search_query%";
}

if (!empty($category_slug)) {
    $where .= " AND c.slug = :category_slug";
    $params[':category_slug'] = $category_slug;
}

if (isset($pdo) && $pdo) {
    try {
        $query = "SELECT p.*, u.full_name as author_name FROM posts p
          JOIN users u ON p.author_id = u.id";
        if (!empty($category_slug)) {
            $query .= " JOIN post_categories pc ON p.id = pc.post_id
                JOIN categories c ON pc.category_id = c.id";
        }
        $query .= " $where ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        // Get total count for pagination
        $count_query = "SELECT COUNT(DISTINCT p.id) as total FROM posts p
                JOIN users u ON p.author_id = u.id";
        if (!empty($category_slug)) {
            $count_query .= " JOIN post_categories pc ON p.id = pc.post_id
                      JOIN categories c ON pc.category_id = c.id";
        }
        $count_query .= " $where";
        $count_stmt = $pdo->prepare($count_query);
        foreach ($params as $key => $value) {
            $count_stmt->bindValue($key, $value);
        }
        $count_stmt->execute();
        $total_results = $count_stmt->fetch()['total'];
        $total_pages = $total_results > 0 ? ceil($total_results / $limit) : 0;

        $categories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();
    } catch (Exception $e) {
        $db_error = $db_error ?? $e->getMessage();
        $results = [];
        $total_results = 0;
        $total_pages = 0;
        $categories = [];
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
    <title>Search Results - I Discourse Knowledge Hub</title>
    <link rel="stylesheet" href="css/islamic-style.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">🎓 I Discourse</a>
                <span class="malaysia-badge">Knowledge Hub</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="index.php#scholars" class="nav-link">Scholars</a>
                <a href="index.php#publications" class="nav-link">Publications</a>
                <div class="dropdown">
                    <button class="dropdown-btn">Categories ▾</button>
                    <div class="dropdown-content">
                        <?php foreach($categories as $cat): ?>
                        <a href="search.php?category=<?php echo urlencode($cat['slug']); ?>"><?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['name_en']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if(isLoggedIn()): ?>
                    <?php if(isProfessor()): ?>
                        <a href="dashboard.php" class="nav-link">Dashboard</a>
                        <a href="write-post.php" class="nav-link btn-primary">✍️ Write</a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="search-results-header">
            <h1>Search Results</h1>
            <form class="search-form-inline" method="GET" action="search.php">
                <input type="text" name="search" placeholder="Search by title, author, or keyword..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">🔍 Search</button>
            </form>
        </div>

        <?php if(!empty($search_query)): ?>
            <div class="search-summary">
                <p>Found <strong><?php echo $total_results; ?></strong> result(s) for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
            </div>

            <?php if(count($results) > 0): ?>
                <div class="search-results-list">
                    <?php foreach($results as $result): ?>
                    <article class="search-result-card">
                        <div class="result-meta">
                            <span class="post-type <?php echo $result['post_type']; ?>"><?php echo ucfirst(str_replace('_', ' ', $result['post_type'])); ?></span>
                            <span class="result-date"><?php echo date('d M Y', strtotime($result['created_at'])); ?></span>
                        </div>
                        <h2><a href="view-post.php?id=<?php echo $result['id']; ?>"><?php echo htmlspecialchars($result['title']); ?></a></h2>
                        <p class="result-excerpt"><?php echo htmlspecialchars(substr(strip_tags($result['excerpt'] ?: $result['content']), 0, 160)) . '...'; ?></p>
                        <div class="result-footer">
                            <span class="result-author">By <?php echo htmlspecialchars($result['author_name']); ?></span>
                            <span class="result-views">👁️ <?php echo number_format($result['views']); ?> views</span>
                            <a href="view-post.php?id=<?php echo $result['id']; ?>" class="read-more">Read Full Article →</a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>No results found</h3>
                    <p>We couldn't find any articles matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>".</p>
                    <p>Suggestions:</p>
                    <ul>
                        <li>Check your spelling</li>
                        <li>Try different keywords</li>
                        <li>Browse our <a href="index.php#publications">latest publications</a></li>
                    </ul>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>Enter a search term</h3>
                <p>Use the search box above to find articles, scholars, and publications.</p>
                <p>You can search by:</p>
                <ul>
                    <li>Article title</li>
                    <li>Author name</li>
                    <li>Keywords in content</li>
                    <li>Publication type</li>
                </ul>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>🎓 I Discourse Knowledge Hub</h3>
                    <p>Platform for scholars to share knowledge and research since 2015.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Guidelines</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <ul>
                        <li><i class="fas fa-envelope"></i> info@idiscourse.my</li>
                        <li><i class="fas fa-map-marker-alt"></i> Cyberjaya, Malaysia</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 I Discourse Knowledge Hub. Established 2015. All rights reserved.</p>
            </div>
        </div>
    </footer>
<script src="js/main.js"></script>
</body>
</html>