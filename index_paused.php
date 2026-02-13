<?php
require_once 'config.php';

echo "<h1>Button Test</h1>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>POST data: " . print_r($_POST, true) . "</p>";

if (isset($_POST['select_theme'])) {
    $_SESSION['theme'] = $_POST['theme'];
    echo "<p style='color:green;'>✅ Theme set to: " . $_POST['theme'] . "</p>";
    echo "<p>Redirecting to auth.php in 2 seconds...</p>";
    echo "<script>setTimeout(() => window.location='auth.php', 2000);</script>";
} else {
    echo "<p>No theme selected yet.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial; padding: 40px; background: #000; color: #fff; }
        button { padding: 20px 40px; font-size: 1.2rem; cursor: pointer; margin: 10px; background: #ff4444; color: white; border: none; border-radius: 10px; }
        button:hover { background: #cc0000; }
    </style>
</head>
<body>
    <h2>Click a Theme:</h2>
    
    <form method="POST" style="margin: 20px 0;">
        <input type="hidden" name="theme" value="girl">
        <button type="submit" name="select_theme">💖 GLAMOUR</button>
    </form>
    
    <form method="POST" style="margin: 20px 0;">
        <input type="hidden" name="theme" value="boy">
        <button type="submit" name="select_theme">⚡ ACTION</button>
    </form>
    
    <form method="POST" style="margin: 20px 0;">
        <input type="hidden" name="theme" value="zombie">
        <button type="submit" name="select_theme">🧟 APOCALYPSE</button>
    </form>
    
    <hr>
    <p><a href="auth.php" style="color:#ff4444;">Skip to Login Page</a></p>
</body>
</html>