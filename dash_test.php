<?php
// dashboard_test.php - Debug Version
require_once 'config.php';

echo "<h1>Dashboard Debug</h1>";
echo "<style>body{font-family:Arial;padding:20px;background:#000;color:#fff;}</style>";

echo "<h2>1. Session Check:</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Data: " . print_r($_SESSION, true) . "\n";
echo "</pre>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red;'>❌ Not logged in! <a href='auth.php' style='color:#ff4444;'>Go to login</a></p>";
    exit;
}

echo "<p style='color:green;'>✅ Logged in as User ID: " . $_SESSION['user_id'] . "</p>";

if (!isset($_SESSION['theme'])) {
    echo "<p style='color:orange;'>⚠️ No theme selected! <a href='index.php' style='color:#ff4444;'>Choose theme</a></p>";
    exit;
}

echo "<p style='color:green;'>✅ Theme: " . $_SESSION['theme'] . "</p>";

echo "<h2>2. Database Check:</h2>";

try {
    $user_id = $_SESSION['user_id'];
    $theme = $_SESSION['theme'];
    
    // Test query
    $stmt = $pdo->prepare("SELECT s.*, u.username as creator_name FROM scenarios s JOIN users u ON s.creator_id = u.id WHERE s.status = 'published' ORDER BY s.votes DESC, s.purchases DESC LIMIT 5");
    $stmt->execute();
    $scenarios = $stmt->fetchAll();
    
    echo "<p style='color:green;'>✅ Found " . count($scenarios) . " scenarios</p>";
    
    if (count($scenarios) > 0) {
        echo "<h3>Scenarios:</h3><ul>";
        foreach ($scenarios as $s) {
            echo "<li>{$s['name']} by {$s['creator_name']} - £{$s['price']}</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Test Passed!</h2>";
echo "<p>If you see this, dashboard.php should work. <a href='dashboard.php' style='color:#ff4444;'>Try dashboard.php now</a></p>";
?>
