<?php
require_once 'config/database.php';
if (!isLoggedIn() || !isProfessor()) redirect('login.php');

$navCategories = [];
$categories = [];
$error = $success = '';

if (isset($pdo) && $pdo) {
    try {
        $navCategories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();
        // Load categories for selection
        $categories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();
    } catch (Exception $e) {
        $db_error = $db_error ?? $e->getMessage();
        $navCategories = [];
        $categories = [];
    }
} else {
    $db_error = $db_error ?? 'Database unavailable.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $post_type = $_POST['post_type'] ?? 'article';
        $status = $_POST['status'] ?? 'draft';
        $selectedCats = isset($_POST['categories']) ? $_POST['categories'] : [];

        if (empty($title) || empty($content)) $error = 'Title and content required';
        else {
            if (!isset($pdo) || !$pdo) {
                $error = 'Cannot save publication: database unavailable.';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO posts (title, content, post_type, status, author_id) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt->execute([$title, $content, $post_type, $status, $_SESSION['user_id']])) {
                        $postId = $pdo->lastInsertId();
                        if (!empty($selectedCats) && is_array($selectedCats)) {
                            $pcStmt = $pdo->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
                            foreach ($selectedCats as $catId) {
                                $pcStmt->execute([$postId, (int)$catId]);
                            }
                        }
                        $success = 'Publication saved!';
                    } else $error = 'Failed to save';
                } catch (Exception $e) {
                    $error = 'Error saving publication: ' . htmlspecialchars($e->getMessage());
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Write Publication</title><link rel="stylesheet" href="css/islamic-style.css"><link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>tinymce.init({selector:'#content', height:400, menubar:true, plugins:'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table', toolbar:'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat'});</script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand"><a href="index.php">🕌 Islamic Knowledge Hub</a></div>
            <div class="nav-menu">
                <a href="index.php">Home</a>
                <a href="dashboard.php">Dashboard</a>
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
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <main class="container"><div class="write-post-container"><h1>Write New Publication</h1>
    <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
        <div class="form-row">
            <div class="form-group"><label>Type</label><select name="post_type"><option value="article">Article</option><option value="tafsir">Tafsir</option><option value="hadith_study">Hadith Study</option><option value="fatwa">Fatwa</option><option value="fiqh">Fiqh</option><option value="journal">Journal</option></select></div>
            <div class="form-group"><label>Status</label><select name="status"><option value="draft">Save as Draft</option><option value="published">Publish Now</option></select></div>
        </div>

        <div class="form-group">
            <label>Categories</label>
            <div class="category-checkboxes">
                <?php foreach($categories as $cat): ?>
                    <label style="display:block;margin:6px 0;"><input type="checkbox" name="categories[]" value="<?php echo $cat['id']; ?>"> <?php echo htmlspecialchars($cat['icon'].' '.$cat['name_en']); ?></label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group"><label>Content</label><textarea id="content" name="content"></textarea></div>
        <button type="submit" class="btn btn-primary">Save Publication</button>
    </form></div></main>
<script src="js/main.js"></script>
</body>
</html>