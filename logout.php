<?php
require_once 'config/database.php';
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
header("Location: index.php");
exit();
?>