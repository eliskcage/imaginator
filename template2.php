<?php
// ============================================
// config.php
// ============================================
class Config {
    const DB_HOST = 'YOUR_DB_HOST';
    const DB_USER = 'YOUR_DB_USER';
    const DB_PASS = 'YOUR_DB_PASS';
    const DB_NAME = 'YOUR_DB_NAME';

    const GEMINI_API_KEY = 'YOUR_GEMINI_API_KEY';
    const STRIPE_SECRET_KEY = 'YOUR_STRIPE_SECRET_KEY';
    const STRIPE_PUBLISHABLE_KEY = 'YOUR_STRIPE_PUBLISHABLE_KEY';
    
    const UPLOAD_DIR = __DIR__ . '/uploads/';
    const PROCESSED_DIR = __DIR__ . '/processed/';
    const TEMPLATES_DIR = __DIR__ . '/templates/';
    
    const PLATFORM_FEE_PERCENT = 30; // 30% platform fee
}

// ============================================
// database.php
// ============================================
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $this->conn = new mysqli(
            Config::DB_HOST,
            Config::DB_USER,
            Config::DB_PASS,
            Config::DB_NAME
        );
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function escape($str) {
        return $this->conn->real_escape_string($str);
    }
}

// ============================================
// index.php - Theme Selection Landing Page
// ============================================
?>
<?php
session_start();

if (isset($_POST['select_theme'])) {
    $_SESSION['theme'] = $_POST['theme'];
    header('Location: dashboard.php');
    exit;
}

if (isset($_SESSION['theme']) && isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHORTF▲CTORY - Choose Your Vibe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            background: #000;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }
        .bg-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.3;
            z-index: 0;
        }
        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 20px;
            max-width: 1200px;
            width: 100%;
        }
        .logo {
            font-size: 4rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 0 0 20px rgba(255,0,0,0.5);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .tagline {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #ff4444;
            font-weight: bold;
        }
        .subtitle {
            font-size: 1.2rem;
            margin-bottom: 50px;
            color: #aaa;
        }
        .theme-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        .theme-card {
            background: rgba(20,20,20,0.9);
            border: 3px solid transparent;
            border-radius: 15px;
            padding: 40px 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .theme-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.5s;
        }
        .theme-card:hover::before {
            left: 100%;
        }
        .theme-card:hover {
            transform: translateY(-10px) scale(1.02);
        }
        .theme-card.girl { border-color: #ff69b4; }
        .theme-card.girl:hover { box-shadow: 0 20px 40px rgba(255,105,180,0.4); }
        .theme-card.boy { border-color: #4169e1; }
        .theme-card.boy:hover { box-shadow: 0 20px 40px rgba(65,105,225,0.4); }
        .theme-card.zombie { border-color: #228b22; }
        .theme-card.zombie:hover { box-shadow: 0 20px 40px rgba(34,139,34,0.4); }
        .theme-icon { font-size: 5rem; margin-bottom: 20px; }
        .theme-title { font-size: 2rem; font-weight: bold; margin-bottom: 15px; }
        .theme-desc { color: #ccc; margin-bottom: 20px; line-height: 1.6; font-size: 1rem; }
        .select-btn {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border: none;
            color: white;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .select-btn:hover { 
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(255,68,68,0.5);
        }
        .girl .select-btn { background: linear-gradient(45deg, #ff69b4, #ff1493); }
        .boy .select-btn { background: linear-gradient(45deg, #4169e1, #1e90ff); }
        .zombie .select-btn { background: linear-gradient(45deg, #228b22, #00ff00); color: #000; }
        .demo-banner {
            margin-top: 50px;
            padding: 30px;
            background: rgba(255,68,68,0.1);
            border: 2px solid #ff4444;
            border-radius: 15px;
            font-size: 1.2rem;
        }
        @media (max-width: 768px) {
            .logo { font-size: 2.5rem; }
            .tagline { font-size: 1.3rem; }
            .theme-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <video class="bg-video" autoplay muted loop playsinline>
        <source src="https://www.shortfactory.shop/vidman/1.webm" type="video/webm">
    </video>
    
    <div class="container">
        <div class="logo">SHORTF▲CTORY</div>
        <div class="tagline">THE IMAGINATOR</div>
        <div class="subtitle">Turn Your Movies Into Hollywood Gold • Instantly</div>
        
        <div class="theme-grid">
            <div class="theme-card girl">
                <div class="theme-icon">💖</div>
                <div class="theme-title">Glamour</div>
                <div class="theme-desc">
                    Pink-tinted drama with romantic vibes and elegant transitions. Perfect for beauty, fashion, and emotional storytelling.
                </div>
                <form method="POST">
                    <input type="hidden" name="theme" value="girl">
                    <button type="submit" name="select_theme" class="select-btn">Choose Glamour</button>
                </form>
            </div>
            
            <div class="theme-card boy">
                <div class="theme-icon">⚡</div>
                <div class="theme-title">Action</div>
                <div class="theme-desc">
                    High-energy cuts with blue intensity and dynamic camera work. Built for sports, adventures, and adrenaline rushes.
                </div>
                <form method="POST">
                    <input type="hidden" name="theme" value="boy">
                    <button type="submit" name="select_theme" class="select-btn">Choose Action</button>
                </form>
            </div>
            
            <div class="theme-card zombie">
                <div class="theme-icon">🧟</div>
                <div class="theme-title">Apocalypse</div>
                <div class="theme-desc">
                    Green horror grading with shaky cam panic and survival tension. Designed for horror, thriller, and post-apocalyptic chaos.
                </div>
                <form method="POST">
                    <input type="hidden" name="theme" value="zombie">
                    <button type="submit" name="select_theme" class="select-btn">Choose Apocalypse</button>
                </form>
            </div>
        </div>
        
        <div class="demo-banner">
            🎬 <strong>FREE DEMO INCLUDED:</strong> "Scared Cameraman" scenario ready to use! Upload your footage and watch it transform into Grade-A Hollywood tension instantly.
        </div>
    </div>
</body>
</html>

<?php
// ============================================
// auth.php - Simple Login/Register
// ============================================
/*
<?php
session_start();
require_once 'config.php';
require_once 'database.php';

$db = Database::getInstance();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        if (strlen($username) < 3 || strlen($password) < 6) {
            $error = 'Username must be 3+ chars, password 6+ chars';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hash);
            
            if ($stmt->execute()) {
                $success = 'Account created! Please login.';
            } else {
                $error = 'Username or email already exists';
            }
        }
    }
    
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        $stmt = $db->prepare("SELECT id, password_hash, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $row['role'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - SHORTF▲CTORY</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #0a0a0a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .auth-container {
            background: rgba(30,30,30,0.9);
            padding: 40px;
            border-radius: 15px;
            width: 400px;
            max-width: 90%;
        }
        .logo { font-size: 2rem; text-align: center; margin-bottom: 30px; }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab {
            flex: 1;
            padding: 10px;
            background: #222;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        .tab.active { background: #ff4444; }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #aaa;
        }
        input {
            width: 100%;
            padding: 12px;
            background: #222;
            border: 1px solid #444;
            color: white;
            border-radius: 5px;
        }
        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            margin-top: 10px;
        }
        button[type="submit"]:hover { transform: scale(1.02); }
        .error { background: #ff4444; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .success { background: #00ff00; color: #000; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">SHORTF▲CTORY</div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="tabs">
            <button class="tab active" onclick="showTab('login')">Login</button>
            <button class="tab" onclick="showTab('register')">Register</button>
        </div>
        
        <div id="login" class="tab-content active">
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login">LOGIN</button>
            </form>
        </div>
        
        <div id="register" class="tab-content">
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required minlength="3">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <button type="submit" name="register">CREATE ACCOUNT</button>
            </form>
        </div>
    </div>
    
    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tab).classList.add('active');
        }
    </script>
</body>
</html>
*/

// ============================================
// dashboard.php - Main Platform
// ============================================
/*
<?php
session_start();
require_once 'config.php';
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

if (!isset($_SESSION['theme'])) {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance();
$theme = $_SESSION['theme'];
$user_id = $_SESSION['user_id'];

// Get top scenarios
$stmt = $db->prepare("SELECT s.*, u.username as creator_name FROM scenarios s JOIN users u ON s.creator_id = u.id WHERE s.status = 'published' ORDER BY s.votes DESC, s.purchases DESC LIMIT 20");
$stmt->execute();
$top_scenarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check if user has purchased scenario
function hasPurchased($user_id, $scenario_id) {
    global $db;
    $stmt = $db->prepare("SELECT id FROM purchases WHERE user_id = ? AND scenario_id = ? AND status = 'completed'");
    $stmt->bind_param("ii", $user_id, $scenario_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SHORTF▲CTORY Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #0a0a0a;
            color: #fff;
            padding: 20px;
            padding-bottom: 100px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: rgba(20,20,20,0.8);
            border-radius: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .logo { font-size: 2rem; font-weight: bold; }
        .theme-badge {
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
        }
        .theme-girl { background: #ff69b4; }
        .theme-boy { background: #4169e1; }
        .theme-zombie { background: #228b22; }
        .user-info { color: #aaa; }
        .upload-section {
            background: rgba(30,30,30,0.9);
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .upload-section h2 { margin-bottom: 15px; font-size: 2rem; }
        .upload-btn {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border: none;
            color: white;
            padding: 20px 50px;
            font-size: 1.3rem;
            border-radius: 50px;
            cursor: pointer;
            margin: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .upload-btn:hover { transform: scale(1.05); }
        .scenarios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        .scenario-card {
            background: rgba(30,30,30,0.9);
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #333;
            transition: all 0.3s;
        }
        .scenario-card:hover {
            border-color: #ff4444;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255,68,68,0.3);
        }
        .scenario-title { 
            font-size: 1.5rem; 
            margin-bottom: 10px;
            color: #ff4444;
        }
        .scenario-creator {
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 10px;
        }
        .scenario-desc {
            color: #ccc;
            margin: 15px 0;
            line-height: 1.6;
        }
        .scenario-stats { 
            color: #888; 
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            font-size: 0.95rem;
        }
        .scenario-tags {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            margin: 10px 0;
        }
        .tag {
            background: #222;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            color: #aaa;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .vote-btn, .buy-btn, .use-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            flex: 1;
            font-size: 1rem;
        }
        .vote-btn { background: #4169e1; color: white; }
        .vote-btn:hover { background: #1e90ff; }
        .buy-btn { background: #00ff00; color: #000; }
        .buy-btn:hover { background: #00cc00; }
        .use-btn { background: #ff4444; color: white; }
        .use-btn:hover { background: #cc0000; }
        .purchased { background: #228b22 !important; }
        @media (max-width: 768px) {
            .scenarios-grid { grid-template-columns: 1fr; }
            .logo { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SHORTF▲CTORY</div>
        <div class="theme-badge theme-<?php echo $theme; ?>">
            <?php echo strtoupper($theme); ?> THEME
        </div>
        <div class="user-info">
            👤 <?php echo htmlspecialchars($_SESSION['username']); ?> | 
            <a href="auth.php?logout=1" style="color:#ff4444;">Logout</a>
        </div>
    </div>
    
    <div class="upload-section">
        <h2>🎬 INSTANT EDITOR</h2>
        <p style="color:#aaa;margin-bottom:20px;">Upload your video and transform it instantly</p>
        <button onclick="location.href='upload.php'" class="upload-btn">
            📤 Upload & Transform
        </button>
        <button onclick="location.href='scenario_builder.php'" class="upload-btn">
            ✨ Create New Scenario
        </button>
    </div>
    
    <h2 style="margin-bottom:20px;">🏆 TOP SCENARIOS</h2>
    <div class="scenarios-grid">
        <?php foreach ($top_scenarios as $scenario): 
            $purchased = hasPurchased($user_id, $scenario['id']);
            $is_creator = ($scenario['creator_id'] == $user_id);
        ?>
        <div class="scenario-card">
            <div class="scenario-title"><?php echo htmlspecialchars($scenario['name']); ?></div>
            <div class="scenario-creator">by @<?php echo htmlspecialchars($scenario['creator_name']); ?></div>
            <div class="scenario-desc"><?php echo htmlspecialchars($scenario['description']); ?></div>
            
            <?php if ($scenario['tags']): ?>
            <div class="scenario-tags">
                <?php foreach (explode(',', $scenario['tags']) as $tag): ?>
                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="scenario-stats">
                <span>👍 <?php echo $scenario['votes']; ?> votes</span>
                <span>💰 <?php echo $scenario['purchases']; ?> sales</span>
                <span>£<?php echo number_format($scenario['price'], 2); ?></span>
            </div>
            
            <div class="action-buttons">
                <?php if ($is_creator): ?>
                    <button class="use-btn purchased" onclick="useScenario(<?php echo $scenario['id']; ?>)">
                        YOUR SCENARIO
                    </button>
                <?php elseif ($purchased): ?>
                    <button class="use-btn purchased" onclick="useScenario(<?php echo $scenario['id']; ?>)">
                        ✓ OWNED - USE IT
                    </button>
                <?php else: ?>
                    <button class="vote-btn" onclick="vote(<?php echo $scenario['id']; ?>)">VOTE</button>
                    <button class="buy-btn" onclick="buy(<?php echo $scenario['id']; ?>, <?php echo $scenario['price']; ?>)">
                        BUY £<?php echo number_format($scenario['price'], 2); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- AI Chatbot Widget -->
    <div id="chatbot-widget"></div>
    
    <script src="chatbot.js"></script>
    <script>
        function vote(scenarioId) {
            fetch('api.php?action=vote', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({scenario_id: scenarioId})
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
                else alert(data.error);
            });
        }
        
        function buy(scenarioId, price) {
            if (confirm(`Purchase this scenario for £${price}?`)) {
                location.href = `checkout.php?scenario_id=${scenarioId}`;
            }
        }
        
        function useScenario(scenarioId) {
            location.href = `editor.php?scenario_id=${scenarioId}`;
        }
    </script>
</body>
</html>
*/
?>