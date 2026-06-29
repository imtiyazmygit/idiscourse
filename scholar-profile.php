<?php
require_once 'config/database.php';

$navCategories = [];
$scholar = null;
$publications = [];
$total_views = 0;

$scholar_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($pdo) && $pdo) {
    try {
        $navCategories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'scholar'");
        $stmt->execute([$scholar_id]);
        $scholar = $stmt->fetch();

        if (!$scholar) {
            redirect('index.php');
        }

        $stmt = $pdo->prepare("SELECT * FROM posts WHERE author_id = ? AND status = 'published' ORDER BY created_at DESC");
        $stmt->execute([$scholar_id]);
        $publications = $stmt->fetchAll();

        $total_views = array_sum(array_column($publications, 'views'));
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
    <title><?php echo htmlspecialchars($scholar['full_name']); ?> - Islamic Scholar</title>
    <link rel="stylesheet" href="css/islamic-style.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container { max-width: 1000px; margin: 2rem auto; background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .profile-header { background: linear-gradient(135deg, #1e3a5f, #2d5a3b); padding: 3rem; color: white; position: relative; }
        .profile-header::before { content: "﴿ ﴾"; position: absolute; font-size: 150px; opacity: 0.1; bottom: -30px; right: 20px; font-family: 'Amiri', serif; }
        .profile-avatar { width: 120px; height: 120px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3.5rem; margin-bottom: 1.5rem; border: 4px solid #ffd700; }
        .profile-name { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .profile-institution { display: inline-block; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 30px; margin-top: 1rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin: 2rem 0; }
        .stat-item { background: #f8f9fa; padding: 1.5rem; text-align: center; border-radius: 16px; }
        .stat-number { font-size: 2rem; font-weight: 800; color: #1e3a5f; }
        .stat-label { color: #666; font-size: 0.85rem; }
        .section-title { font-size: 1.5rem; font-weight: 700; color: #1e3a5f; margin: 2rem 0 1rem; border-bottom: 3px solid #2d5a3b; display: inline-block; }
        .publication-item { background: #f8f9fa; margin-bottom: 1rem; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #2d5a3b; }
        .publication-title { font-size: 1.1rem; font-weight: 600; color: #1e3a5f; }
        .publication-title a { color: #1e3a5f; text-decoration: none; }
        .pub-type { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block; margin-right: 1rem; }
        .pub-type.tafsir { background: #e3f2fd; color: #1e3a5f; }
        .pub-type.hadith_study { background: #e8f5e9; color: #2d5a3b; }
        .pub-type.fatwa { background: #fff3e0; color: #e65100; }
        .btn-back { display: inline-block; margin-top: 2rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #1e3a5f, #2d5a3b); color: white; text-decoration: none; border-radius: 30px; }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .profile-header { padding: 2rem; } .profile-name { font-size: 1.5rem; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand"><a href="index.php">🕌 Islamic Knowledge Hub</a><span class="malaysia-badge">🇲🇾 MALAYSIA</span></div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
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
                <?php if(isLoggedIn() && isProfessor()): ?>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="write-post.php" class="nav-link btn-primary">✍️ Write</a>
                <?php endif; ?>
                <?php if(isLoggedIn()): ?>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">🕌</div>
                <h1 class="profile-name"><?php echo htmlspecialchars($scholar['full_name']); ?></h1>
                <div><?php echo htmlspecialchars($scholar['specialization']); ?></div>
                <div class="profile-institution"><i class="fas fa-university"></i> <?php echo htmlspecialchars($scholar['institution']); ?></div>
                <?php if($scholar['malaysia_state']): ?>
                <div class="profile-institution" style="background: rgba(255,255,255,0.15);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($scholar['malaysia_state']); ?>, Malaysia</div>
                <?php endif; ?>
            </div>

            <div style="padding: 2rem;">
                <div class="stats-grid">
                    <div class="stat-item"><div class="stat-number"><?php echo count($publications); ?></div><div class="stat-label">Publications</div></div>
                    <div class="stat-item"><div class="stat-number"><?php echo number_format($total_views); ?></div><div class="stat-label">Total Views</div></div>
                    <div class="stat-item"><div class="stat-number"><?php echo $scholar['id']; ?></div><div class="stat-label">Scholar ID</div></div>
                    <div class="stat-item"><div class="stat-number"><?php echo date('Y', strtotime($scholar['created_at'])); ?></div><div class="stat-label">Member Since</div></div>
                </div>

                <?php if($scholar['bio']): ?>
                <div><h2 class="section-title">Biography</h2><div style="margin: 1rem 0; line-height: 1.8;"><?php echo nl2br(htmlspecialchars($scholar['bio'])); ?></div></div>
                <?php endif; ?>

                <div><h2 class="section-title">Contact</h2><div style="margin: 1rem 0; background: #f8f9fa; padding: 1.5rem; border-radius: 16px;"><i class="fas fa-envelope"></i> <a href="mailto:<?php echo $scholar['email']; ?>"><?php echo $scholar['email']; ?></a></div></div>

                <div><h2 class="section-title">Publications</h2>
                <?php if(count($publications) > 0): ?>
                    <?php foreach($publications as $pub): ?>
                    <div class="publication-item">
                        <div class="publication-title"><a href="view-post.php?id=<?php echo $pub['id']; ?>"><?php echo htmlspecialchars($pub['title']); ?></a></div>
                        <div style="margin-top: 0.5rem;"><span class="pub-type <?php echo $pub['post_type']; ?>"><?php echo ucfirst(str_replace('_', ' ', $pub['post_type'])); ?></span> <span><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($pub['created_at'])); ?></span> <span><i class="far fa-eye"></i> <?php echo number_format($pub['views']); ?> views</span></div>
                        <div class="publication-excerpt" style="margin-top: 0.5rem; color: #666;"><?php echo htmlspecialchars(substr(strip_tags($pub['excerpt'] ?: $pub['content']), 0, 120)) . '...'; ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No publications yet.</p>
                <?php endif; ?>
                </div>

                <div style="text-align: center;"><a href="index.php#scholars" class="btn-back">← Back to All Scholars</a></div>
            </div>
        </div>
    </div>

    <footer class="footer"><div class="container"><div class="footer-bottom"><p>&copy; 2026 Islamic Knowledge Hub Malaysia</p></div></div></footer>
<script src="js/main.js"></script>
</body>
</html>