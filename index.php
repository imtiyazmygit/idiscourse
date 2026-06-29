<?php
require_once 'config/database.php';

// Safe DB usage: gracefully handle missing/unavailable DB
$navCategories = [];
$scholars = $publications = $categories = $popularPosts = [];
$totalScholars = $totalPublications = 0;
if (isset($pdo) && $pdo) {
    try {
        // Fetch data
        $scholars = $pdo->query("SELECT * FROM users WHERE role = 'scholar' ORDER BY display_order ASC LIMIT 6")->fetchAll();
        $publications = $pdo->query("SELECT p.*, u.full_name as author_name FROM posts p JOIN users u ON p.author_id = u.id WHERE p.status = 'published' ORDER BY p.created_at DESC LIMIT 6")->fetchAll();
        $categories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();
        $totalScholars = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'scholar'")->fetch()['total'];
        $totalPublications = $pdo->query("SELECT COUNT(*) as total FROM posts WHERE status = 'published'")->fetch()['total'];
        $popularPosts = $pdo->query("SELECT id, title, views FROM posts WHERE status = 'published' ORDER BY views DESC LIMIT 5")->fetchAll();
        $navCategories = $categories;
    } catch (Exception $e) {
        $db_error = $db_error ?? $e->getMessage();
        // Leave defaults in place
    }
} else {
    $db_error = $db_error ?? 'Database unavailable.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>IDM Knowledge Hub | Islamic Scholars Platform</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container">
        <div class="logo">
            <span class="logo-icon">🎓</span>
            <span class="logo-text">IDM Knowledge Hub</span>
            <span class="badge">Malaysia</span>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle theme">🌙</button>
            <button class="menu-toggle" aria-label="Menu">☰</button>
        </div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link active">Home</a>
            <a href="about.php" class="nav-link">About</a>
            <a href="#scholars" class="nav-link">Scholars</a>
            <a href="#publications" class="nav-link">Publications</a>
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

<!-- HERO SECTION -->
<header class="hero">
    <div class="container hero-content">
        <div class="arabic">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
        <h1>i-Discourse Mehfil (IDM)<br>Knowledge Hub</h1>
        <p>Empowering Scholars, Serving Communities Since 2015</p>
        
        <div class="hero-stats">
            <div class="stat-card">
                <span class="stat-number"><?php echo $totalPublications; ?>+</span>
                <span class="stat-label">Publications</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $totalScholars; ?>+</span>
                <span class="stat-label">Scholars</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">2015</span>
                <span class="stat-label">Established</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">14</span>
                <span class="stat-label">Orphans Supported</span>
            </div>
        </div>
        
        <form class="search-form" method="GET" action="search.php">
            <input type="text" name="search" placeholder="Search articles, research papers, or scholars..." autocomplete="off">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</header>

<main>
    <!-- FEATURES SECTION -->
    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Join IDM Knowledge Hub?</h2>
                <p>A platform dedicated to scholarly excellence and community service</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📖</div>
                    <h3>Scholarly Articles</h3>
                    <p>Access peer-reviewed research and academic publications from leading scholars worldwide.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👨‍🏫</div>
                    <h3>Expert Scholars</h3>
                    <p>Connect with distinguished professors and researchers from Malaysia and beyond.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3>Community Impact</h3>
                    <p>Supporting orphan education and refugee children through knowledge sharing initiatives.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SCHOLARS SECTION -->
    <section id="scholars" class="scholars">
        <div class="container">
            <div class="section-header">
                <h2>Our Leading Scholars</h2>
                <p>Meet our distinguished academicians and researchers</p>
            </div>
            <div class="scholars-grid">
                <?php foreach($scholars as $scholar): ?>
                <div class="scholar-card">
                    <div class="scholar-image">
                        <?php if(!empty($scholar['profile_image']) && file_exists($scholar['profile_image'])): ?>
                            <img src="<?php echo $scholar['profile_image']; ?>" alt="<?php echo htmlspecialchars($scholar['full_name']); ?>">
                        <?php else: ?>
                            🎓
                        <?php endif; ?>
                    </div>
                    <div class="scholar-info">
                        <h3><?php echo htmlspecialchars($scholar['full_name']); ?></h3>
                        <p class="institution"><?php echo htmlspecialchars(substr($scholar['institution'], 0, 45)); ?></p>
                        <p class="specialization"><?php echo htmlspecialchars(substr($scholar['specialization'], 0, 55)); ?></p>
                        <a href="scholar-profile.php?id=<?php echo $scholar['id']; ?>" class="btn-outline">View Profile <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- PUBLICATIONS SECTION -->
    <section id="publications" class="publications">
        <div class="container">
            <div class="section-header">
                <h2>Recent Publications</h2>
                <p>Latest research and scholarly contributions</p>
            </div>
            <div class="posts-grid">
                <?php foreach($publications as $post): ?>
                <div class="post-card">
                    <div class="post-content">
                        <span class="post-badge"><?php echo ucfirst(str_replace('_', ' ', $post['post_type'])); ?></span>
                        <h3><a href="view-post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                        <div class="post-meta">
                            <span><i class="far fa-user"></i> <?php echo htmlspecialchars($post['author_name']); ?></span>
                            <span><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
                            <span><i class="far fa-eye"></i> <?php echo number_format($post['views']); ?> views</span>
                        </div>
                        <p class="post-excerpt"><?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 120)); ?>...</p>
                        <a href="view-post.php?id=<?php echo $post['id']; ?>" class="read-more">Read Full Article <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- POPULAR POSTS SECTION -->
    <?php if(count($popularPosts) > 0): ?>
    <section class="popular">
        <div class="container">
            <div class="section-header">
                <h2>🔥 Most Popular Articles</h2>
                <p>Most viewed content in our knowledge hub</p>
            </div>
            <div class="popular-grid">
                <?php foreach($popularPosts as $index => $post): ?>
                <div class="popular-item">
                    <div class="popular-rank"><?php echo $index + 1; ?></div>
                    <div class="popular-content">
                        <h4><a href="view-post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars(substr($post['title'], 0, 60)); ?></a></h4>
                        <span class="popular-views"><i class="fas fa-eye"></i> <?php echo number_format($post['views']); ?> views</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA SECTION -->
    <section class="cta-section">
        <div class="container">
            <h2>Join Our Scholarly Community</h2>
            <p>Are you a scholar or researcher? Share your knowledge and contribute to our growing repository.</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary">Register as Scholar <i class="fas fa-user-plus"></i></a>
                <a href="about.php" class="btn btn-outline-light">Learn More <i class="fas fa-info-circle"></i></a>
            </div>
        </div>
    </section>
</main>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3>🎓 IDM Knowledge Hub</h3>
                <p>Platform for scholars to share knowledge and research since 2015.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="ResearchGate"><i class="fab fa-researchgate"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="#scholars">Our Scholars</a></li>
                    <li><a href="#publications">Publications</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Our Initiatives</h3>
                <ul>
                    <li><a href="#">Orphan Education Support</a></li>
                    <li><a href="#">Refugee School Program</a></li>
                    <li><a href="#">Knowledge Gatherings</a></li>
                    <li><a href="#">Research Collaborations</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contact Info</h3>
                <ul>
                    <li><i class="fas fa-envelope"></i> info@idiscourse.my</li>
                    <li><i class="fas fa-map-marker-alt"></i> Cyberjaya, Malaysia</li>
                    <li><i class="fas fa-clock"></i> Mon - Fri, 9am - 5pm</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 i-Discourse Mehfil (IDM) Knowledge Hub. Established 2015. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>