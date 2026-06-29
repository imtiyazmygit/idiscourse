<?php
require_once 'config/database.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    redirect('dashboard.php');
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Security token expired.';
    redirect('dashboard.php');
}

$post_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$isAdmin = ($user_role === 'admin');

if (!isset($pdo) || !$pdo) {
    $_SESSION['error'] = 'Database unavailable. Cannot delete post.';
    redirect('dashboard.php');
}

try {
    // Get post details
    $stmt = $pdo->prepare("SELECT author_id FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        $_SESSION['error'] = "Post not found.";
        redirect('dashboard.php');
    }

    // Check permission: Admin can delete any post, Scholar can only delete their own
    if ($isAdmin || $post['author_id'] == $user_id) {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        if ($stmt->execute([$post_id])) {
            $_SESSION['success'] = "Post deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete post.";
        }
    } else {
        $_SESSION['error'] = "You don't have permission to delete this post.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error deleting post: ' . htmlspecialchars($e->getMessage());
}

redirect('dashboard.php');
?>