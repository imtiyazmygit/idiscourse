<?php
echo "<h2>Database Connection Tests</h2>";

// Test 1: Current config (localhost)
echo "<h3>Test 1: Localhost Connection</h3>";
$host = 'localhost';
$dbname = 'u767322683_idiscourse';
$username = 'admin';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    echo "✅ <strong>Connected to localhost!</strong><br>";
    echo "Host: $host<br>DB: $dbname<br>User: $username<br>";
} catch(PDOException $e) {
    echo "❌ <strong>Failed to connect to localhost</strong><br>";
    echo "Error: " . $e->getMessage() . "<br><br>";
}

// Test 2: Ask user for Hostinger credentials
echo "<h3>Test 2: Hostinger Connection</h3>";
echo "<form method='POST'>";
echo "Enter your Hostinger DB Host: <input type='text' name='host' placeholder='e.g., mysql.hostinger.com' required><br>";
echo "Enter your Hostinger DB Name: <input type='text' name='dbname' placeholder='e.g., u767322683_idiscourse' required><br>";
echo "Enter your Hostinger DB User: <input type='text' name='user' placeholder='e.g., u767322683_admin' required><br>";
echo "Enter your Hostinger DB Password: <input type='password' name='pass' required><br>";
echo "<button type='submit'>Test Hostinger Connection</button>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $h_host = $_POST['host'] ?? '';
    $h_dbname = $_POST['dbname'] ?? '';
    $h_user = $_POST['user'] ?? '';
    $h_pass = $_POST['pass'] ?? '';
    
    try {
        $pdo = new PDO("mysql:host=$h_host;dbname=$h_dbname;charset=utf8mb4", $h_user, $h_pass);
        echo "✅ <strong>Connected to Hostinger!</strong><br>";
        echo "Host: $h_host<br>DB: $h_dbname<br>User: $h_user<br><br>";
        echo "<strong>Update config/database.php with these credentials:</strong><br>";
        echo "<pre>";
        echo "\$host = '$h_host';\n";
        echo "\$dbname = '$h_dbname';\n";
        echo "\$username = '$h_user';\n";
        echo "\$password = '$h_pass';\n";
        echo "</pre>";
    } catch(PDOException $e) {
        echo "❌ <strong>Failed to connect to Hostinger</strong><br>";
        echo "Error: " . $e->getMessage() . "<br>";
    }
}
?>
