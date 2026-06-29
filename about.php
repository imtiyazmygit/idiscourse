<?php
require_once 'config/database.php';
$navCategories = [];
if (isset($pdo) && $pdo) {
    try {
        $navCategories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();
    } catch (Exception $e) {
        $navCategories = [];
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
    <title>About Us - i-Discourse Mehfil (IDM)</title>
    <link rel="stylesheet" href="css/islamic-style.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .about-container {
            max-width: 900px;
            margin: 3rem auto;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }
        .about-header {
            background: linear-gradient(135deg, #1e3a5f, #2d5a3b);
            padding: 3rem;
            color: white;
            text-align: center;
        }
        .about-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .about-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .about-content {
            padding: 2.5rem;
        }
        .about-section {
            margin-bottom: 2rem;
        }
        .about-section h2 {
            color: #1e3a5f;
            font-size: 1.6rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #2d5a3b;
            display: inline-block;
        }
        .about-section h3 {
            color: #2d5a3b;
            font-size: 1.3rem;
            margin: 1.5rem 0 1rem;
        }
        .about-section p {
            color: #444;
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        .milestone-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .milestone-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 16px;
            text-align: center;
            transition: transform 0.3s;
            border-bottom: 3px solid #2d5a3b;
        }
        .milestone-card:hover {
            transform: translateY(-5px);
        }
        .milestone-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .milestone-number {
            font-size: 2rem;
            font-weight: 800;
            color: #1e3a5f;
        }
        .milestone-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .initiative-list {
            list-style: none;
            padding: 0;
        }
        .initiative-list li {
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .initiative-list li i {
            font-size: 1.5rem;
            color: #2d5a3b;
        }
        .founding-year {
            font-size: 3rem;
            font-weight: 800;
            color: #ffd700;
            display: inline-block;
            margin-right: 1rem;
        }
        .stats-row {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 2rem 0;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f0f7f0, #e8f0e8);
            border-radius: 16px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #1e3a5f;
        }
        .stat-label {
            color: #666;
            font-size: 0.85rem;
        }
        .partners-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 16px;
            margin-top: 2rem;
        }
        .amount-highlight {
            background: #1e3a5f;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            display: inline-block;
            font-weight: 600;
            margin: 0.5rem 0;
        }
        @media (max-width: 768px) {
            .about-header h1 { font-size: 1.8rem; }
            .about-content { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">🎓 i-Discourse Mehfil (IDM)</a>
                
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="about.php" class="nav-link active">About</a>
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

    <main>
        <div class="about-container">
            <div class="about-header">
                <div class="arabic-decoration" style="font-size: 1rem;">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
                <h1>About i-Discourse Mehfil (IDM)</h1>
                <p>Knowledge, Service, and Community Building Since 2015</p>
            </div>

            <div class="about-content">
                <!-- Founding Story -->
                <div class="about-section">
                    <h2>Our Journey</h2>
                    <p><span class="founding-year">2015</span> — i-Discourse Mehfil (IDM) was founded with a vision to create a platform for meaningful intellectual exchange and community service. What began as a small discussion group has grown into a vibrant knowledge hub where scholars, researchers, and thinkers come together to share insights and contribute to humanity.</p>
                    <p>Over the years, I Discourse has evolved into a dynamic community that bridges academic discourse with practical humanitarian efforts. Our platform serves as a bridge between scholarly knowledge and real-world impact.</p>
                </div>

                <!-- Impact Statistics -->
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-number">10+</div>
                        <div class="stat-label">Years of Service</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">250+</div>
                        <div class="stat-label">Children Supported</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Knowledge Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">14</div>
                        <div class="stat-label">Orphans Educated</div>
                    </div>
                </div>

                <!-- Key Initiatives -->
                <div class="about-section">
                    <h2>Key Initiatives</h2>
                    <p>In our limited capacity, we have strived to serve humanity and especially the Muslim community through various initiatives:</p>
                    
                    <ul class="initiative-list">
                        <li>
                            <i class="fas fa-graduation-cap"></i>
                            <div>
                                <strong>Orphan Education Support</strong><br>
                                <strong>i-Discourse Mehfil (IDM) group</strong> has been supporting <strong>14 orphan students</strong> under the <strong>OrphanCare scholarship project</strong> of <strong>Human Welfare Foundation (HWF)</strong> since <strong>2020</strong>. 
                                <div class="amount-highlight">Annual contribution: Rs 20,000 (≈ MYR 900) per student</div>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-school"></i>
                            <div>
                                <strong>Refugee School Support</strong><br>
                                Provided assistance to a refugee school serving <strong>250 children</strong>, helping create a safe learning environment for displaced communities.
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-users"></i>
                            <div>
                                <strong>Community Gatherings (Mehfil)</strong><br>
                                Organized numerous offline and online meetings, lectures, and knowledge-sharing sessions to foster intellectual growth and community bonding.
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Partnership Section -->
                <div class="partners-section">
                    <h3 style="margin-top: 0; color: #1e3a5f;">🤝 Our Partners</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 2rem; align-items: center; justify-content: space-between;">
                        <div>
                            <strong>Human Welfare Foundation (HWF)</strong><br>
                            OrphanCare Scholarship Project Partner<br>
                            <small>Collaboration since 2020</small>
                        </div>
                        <div>
                            <strong>i-Discourse Mehfil (IDM)</strong><br>
                            Community Group & Knowledge Forum
                        </div>
                    </div>
                </div>

                <!-- Our Mission -->
                <div class="about-section">
                    <h2>Our Mission</h2>
                    <p>I Discourse is committed to:</p>
                    <ul style="margin-left: 1.5rem; line-height: 1.8; color: #444;">
                        <li>Providing a platform for scholars to publish and share knowledge</li>
                        <li>Bridging academic discourse with community service</li>
                        <li>Supporting orphan education through structured scholarship programs</li>
                        <li>Fostering meaningful intellectual exchange through regular gatherings (Mehfil)</li>
                        <li>Preserving and disseminating Islamic scholarly traditions</li>
                    </ul>
                </div>

                <!-- Our Vision -->
                <div class="about-section">
                    <h2>Our Vision</h2>
                    <p>To become a leading knowledge platform that empowers scholars and serves communities, creating a lasting impact through education, discourse, and humanitarian efforts. We envision a world where knowledge is freely shared and translated into action that benefits humanity.</p>
                </div>

                <!-- Call to Action -->
                <div class="about-section" style="background: #f0f7f0; padding: 2rem; border-radius: 16px; text-align: center; margin-top: 2rem;">
                    <h3 style="margin-top: 0;">Join Our Journey</h3>
                    <p style="margin-bottom: 1rem;">Whether you are a scholar wanting to share knowledge, a volunteer willing to contribute, or someone who wants to support our initiatives — we welcome you to be part of I Discourse.</p>
                    <a href="register.php" class="btn btn-primary" style="display: inline-block; margin-top: 0.5rem;">Become a Member →</a>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>🎓 i-Discourse Mehfil (IDM) Knowledge Hub</h3>
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
                        <li><i class="fas fa-map-marker-alt"></i> Malaysia</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 I Discourse Knowledge Hub. Established 2015. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>