<?php
require_once 'config/database.php';

$navCategories = [];
$post = null;
$categories = [];
$author = [];
$related_posts = [];
$author_posts = [];

if (isset($pdo) && $pdo) {
    try {
        $navCategories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // Increment view count
        $stmt = $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);

        // Get post details with author info
        $stmt = $pdo->prepare("SELECT p.*, u.full_name as author_name, u.institution, u.id as author_id 
                       FROM posts p 
                       JOIN users u ON p.author_id = u.id 
                       WHERE p.id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if (!$post) {
            redirect('index.php');
        }

        // Get categories for this post
        $stmt = $pdo->prepare("SELECT c.name_en, c.icon, c.slug FROM categories c 
                       JOIN post_categories pc ON c.id = pc.category_id 
                       WHERE pc.post_id = ?");
        $stmt->execute([$id]);
        $categories = $stmt->fetchAll();

        // Get author details for the professor card
        $stmt = $pdo->prepare("SELECT profile_image, bio FROM users WHERE id = ?");
        $stmt->execute([$post['author_id']]);
        $author = $stmt->fetch();

        // Get related posts (same category)
        if (!empty($categories)) {
            $cat_id = $categories[0]['name_en'];
            $stmt = $pdo->prepare("SELECT p.id, p.title, p.views FROM posts p 
                           JOIN post_categories pc ON p.id = pc.post_id 
                           JOIN categories c ON pc.category_id = c.id 
                           WHERE c.name_en = ? AND p.id != ? AND p.status = 'published' 
                           LIMIT 3");
            $stmt->execute([$cat_id, $id]);
            $related_posts = $stmt->fetchAll();
        }

        // Get other posts by the same author
        $stmt = $pdo->prepare("SELECT id, title, created_at FROM posts WHERE author_id = ? AND status = 'published' AND id != ? ORDER BY created_at DESC LIMIT 4");
        $stmt->execute([$post['author_id'], $id]);
        $author_posts = $stmt->fetchAll();
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
    <title><?php echo htmlspecialchars($post['title']); ?> - i-Discourse Mehfil (IDM)</title>
    <link rel="stylesheet" href="css/islamic-style.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .view-count {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #f0f7f0;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            color: #2d5a3b;
        }
        .view-count i {
            font-size: 0.9rem;
        }
        .popular-badge {
            background: linear-gradient(135deg, #ffd700, #ffb347);
            color: #1e3a5f;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
    </style>
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
                <a href="about.php" class="nav-link">About</a>
                <a href="index.php#scholars" class="nav-link">Scholars</a>
                <a href="index.php#publications" class="nav-link">Publications</a>
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
        <article class="single-post">
            <div class="post-header">
                <div class="post-meta-top">
                    <span class="post-type <?php echo $post['post_type']; ?>"><?php echo ucfirst(str_replace('_', ' ', $post['post_type'])); ?></span>
                    <span class="view-count">
                        <i class="fas fa-eye"></i> 
                        <?php echo number_format($post['views']); ?> views
                        <?php if($post['views'] > 100): ?>
                            <span class="popular-badge">🔥 Popular</span>
                        <?php endif; ?>
                    </span>
                </div>
                <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="author-card">
                    <div class="author-avatar">
                        <?php if(!empty($author['profile_image']) && file_exists($author['profile_image'])): ?>
                            <img src="<?php echo $author['profile_image']; ?>" alt="<?php echo htmlspecialchars($post['author_name']); ?>">
                        <?php else: ?>
                            <span>👨‍🏫</span>
                        <?php endif; ?>
                    </div>
                    <div class="author-card-body">
                        <div class="author-card-heading">
                            <div>
                                <h3><?php echo htmlspecialchars($post['author_name']); ?></h3>
                                <p class="author-institution"><?php echo htmlspecialchars($post['institution']); ?></p>
                            </div>
                            <div class="post-stats">
                                <span><i class="far fa-calendar-alt"></i> <?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
                                <span><i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($post['created_at'])); ?></span>
                            </div>
                        </div>
                        <?php if(!empty($author['bio'])): ?>
                        <p class="author-bio"><?php echo htmlspecialchars(substr($author['bio'], 0, 220)); ?><?php echo strlen($author['bio']) > 220 ? '...' : ''; ?></p>
                        <?php endif; ?>
                        <div class="author-card-links">
                            <a href="scholar-profile.php?id=<?php echo $post['author_id']; ?>" class="btn btn-outline">More by this author</a>
                            <?php if(!empty($categories[0]['slug'])): ?>
                            <a href="search.php?category=<?php echo urlencode($categories[0]['slug']); ?>" class="btn btn-outline">More like this</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if(count($categories) > 0): ?>
                <div class="post-categories">
                    <strong><i class="fas fa-tags"></i> Categories:</strong>
                    <?php foreach($categories as $cat): ?>
                        <span class="category-tag"><?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['name_en']); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="post-content">
                <?php echo $post['content']; ?>
            </div>

            <?php if(!empty($author_posts)): ?>
            <div class="more-from-author">
                <h3>More articles by <?php echo htmlspecialchars($post['author_name']); ?></h3>
                <ul>
                    <?php foreach($author_posts as $article): ?>
                    <li>
                        <a href="view-post.php?id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a>
                        <span><?php echo date('d M Y', strtotime($article['created_at'])); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="post-footer-actions">
                <div class="share-buttons">
                    <span>Share this article:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="share-btn twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn linkedin"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <?php if(!empty($related_posts)): ?>
            <div class="related-posts">
                <h3>Related Articles</h3>
                <div class="related-grid">
                    <?php foreach($related_posts as $related): ?>
                    <a href="view-post.php?id=<?php echo $related['id']; ?>" class="related-card">
                        <h4><?php echo htmlspecialchars($related['title']); ?></h4>
                        <span class="related-views"><i class="fas fa-eye"></i> <?php echo number_format($related['views']); ?> views</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </article>
    </main>

    <style>
        .post-header {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .post-meta-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .author-card {
            display: flex;
            align-items: stretch;
            gap: 24px;
            flex-wrap: wrap;
            padding: 1.8rem;
            background: #fafbff;
            border-radius: 24px;
            margin: 1.5rem 0;
            box-shadow: 0 14px 45px rgba(30, 58, 95, 0.06);
        }
        .author-avatar {
            width: 120px;
            height: 120px;
            min-width: 120px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3a5f, #2d5a3b);
            border: 4px solid rgba(255,255,255,0.9);
            box-shadow: 0 16px 35px rgba(27, 70, 104, 0.15);
        }
        .author-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .author-card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
        }
        .author-card-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }
        .author-card h3 {
            margin: 0;
            font-size: 1.55rem;
            color: #0f2b3d;
        }
        .author-institution {
            margin: 0.35rem 0 0;
            color: #475569;
            font-size: 0.95rem;
        }
        .post-stats {
            display: flex;
            gap: 1rem;
            color: #64748b;
            font-size: 0.85rem;
            flex-wrap: wrap;
        }
        .author-bio {
            margin: 0;
            color: #475569;
            line-height: 1.8;
            max-width: 820px;
        }
        .author-card-links {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 0.75rem;
        }
        .author-card-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            color: var(--primary);
            background: rgba(26, 74, 111, 0.08);
            border-radius: 999px;
            border: 1px solid rgba(26, 74, 111, 0.12);
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.95rem;
            font-weight: 600;
        }
        .author-card-links a:hover {
            background: var(--primary);
            color: white;
        }
        .post-categories {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }
        .category-tag {
            background: #f0f0f0;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.78rem;
            color: #334155;
        }
        .more-from-author {
            background: white;
            border-radius: 24px;
            box-shadow: 0 8px 30px rgba(15, 43, 61, 0.06);
            padding: 1.8rem;
            margin-bottom: 2rem;
        }
        .more-from-author h3 {
            margin-bottom: 1rem;
            color: #1e3a5f;
        }
        .more-from-author ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .more-from-author li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(100, 116, 139, 0.12);
            font-size: 0.96rem;
        }
        .more-from-author li:last-child {
            border-bottom: none;
        }
        .more-from-author a {
            color: #0f2b3d;
            text-decoration: none;
            font-weight: 600;
        }
        .more-from-author a:hover {
            color: var(--primary);
        }
        .more-from-author span {
            color: #64748b;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        .post-content {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            line-height: 1.8;
        }
        .post-content h2 {
            color: #1e3a5f;
            margin: 1.5rem 0 1rem;
        }
        .post-content p {
            margin-bottom: 1rem;
        }
        .post-content ul, .post-content ol {
            margin: 1rem 0 1rem 2rem;
        }
        .post-footer-actions {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .share-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .share-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .share-btn.facebook { background: #1877f2; color: white; }
        .share-btn.twitter { background: #1da1f2; color: white; }
        .share-btn.linkedin { background: #0077b5; color: white; }
        .share-btn:hover { transform: translateY(-3px); }
        .related-posts {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .related-posts h3 {
            color: #1e3a5f;
            margin-bottom: 1rem;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .related-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .related-card:hover {
            transform: translateY(-3px);
            background: #e8f0e8;
        }
        .related-card h4 {
            color: #1e3a5f;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .related-views {
            font-size: 0.7rem;
            color: #64748b;
        }
        @media (max-width: 768px) {
            .post-header, .post-content { padding: 1.2rem; }
            .author-card { flex-direction: column; align-items: flex-start; }
            .author-avatar { width: 100px; height: 100px; min-width: 100px; }
        }
    </style>

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