<?php
require_once 'config/database.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('login.php');
}

$navCategories = [];
$post = null;
$error = '';
$success = '';

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$isAdmin = ($user_role === 'admin');

if (isset($pdo) && $pdo) {
    try {
        $navCategories = $pdo->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();

        // Get post details
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();

        if (!$post) {
            $_SESSION['error'] = "Post not found.";
            redirect('dashboard.php');
        }

        // Check permission: Admin can edit any post, Scholar can only edit their own
        if (!$isAdmin && $post['author_id'] != $user_id) {
            $_SESSION['error'] = "You don't have permission to edit this post.";
            redirect('dashboard.php');
        }
    } catch (Exception $e) {
        $db_error = $db_error ?? $e->getMessage();
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

        if (empty($title) || empty($content)) {
            $error = 'Title and content are required';
        } else {
            if (!isset($pdo) || !$pdo) {
                $error = 'Cannot update post: database unavailable.';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, post_type = ?, status = ? WHERE id = ?");
                    if ($stmt->execute([$title, $content, $post_type, $status, $post_id])) {
                        // Update categories
                        $stmt = $pdo->prepare("DELETE FROM post_categories WHERE post_id = ?");
                        $stmt->execute([$post_id]);
                        if (!empty($selectedCats) && is_array($selectedCats)) {
                            $pcStmt = $pdo->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
                            foreach ($selectedCats as $catId) {
                                $pcStmt->execute([$post_id, (int)$catId]);
                            }
                        }
                        $success = 'Post updated successfully!';
                        // Refresh post data
                        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
                        $stmt->execute([$post_id]);
                        $post = $stmt->fetch();
                    } else {
                        $error = 'Failed to update post';
                    }
                } catch (Exception $e) {
                    $error = 'Error updating post: ' . htmlspecialchars($e->getMessage());
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Publication - i-Discourse Mehfil (IDM)</title>
    <link rel="stylesheet" href="css/islamic-style.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 500,
            menubar: true,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
        });
    </script>
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
        <div class="write-post-container">
            <h1>Edit Publication</h1>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?> <a href="view-post.php?id=<?php echo $post_id; ?>">View post</a></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required value="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
                
                <div class="form-row" style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Publication Type</label>
                        <select name="post_type">
                            <option value="article" <?php echo $post['post_type'] == 'article' ? 'selected' : ''; ?>>Article</option>
                            <option value="tafsir" <?php echo $post['post_type'] == 'tafsir' ? 'selected' : ''; ?>>Tafsir</option>
                            <option value="hadith_study" <?php echo $post['post_type'] == 'hadith_study' ? 'selected' : ''; ?>>Hadith Study</option>
                            <option value="fatwa" <?php echo $post['post_type'] == 'fatwa' ? 'selected' : ''; ?>>Fatwa</option>
                            <option value="fiqh" <?php echo $post['post_type'] == 'fiqh' ? 'selected' : ''; ?>>Fiqh</option>
                            <option value="journal" <?php echo $post['post_type'] == 'journal' ? 'selected' : ''; ?>>Journal</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label>Status</label>
                        <select name="status">
                            <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Content *</label>
                    <textarea id="content" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Publication</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
<script src="js/main.js"></script>
</body>
</html>