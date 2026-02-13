<?php
// auth.php - CLEAN VERSION
require_once 'config.php';

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['theme'])) {
        header('Location: dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['register'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            
            if (strlen($username) < 3) {
                $error = 'Username must be at least 3 characters';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email address';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$username, $email, $hash])) {
                    $success = 'Account created! Please login.';
                } else {
                    $error = 'Username or email already exists';
                }
            }
        }
        
        if (isset($_POST['login'])) {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            $stmt = $pdo->prepare("SELECT id, email, password_hash, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $row = $stmt->fetch();
            
            if ($row && password_verify($password, $row['password_hash'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = $row['role'];
                
                // Redirect based on theme
                if (isset($_SESSION['theme'])) {
                    header('Location: dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            padding: 20px;
        }
        .auth-container {
            background: rgba(30,30,30,0.9);
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 450px;
            border: 2px solid #333;
        }
        .logo { 
            font-size: 2.5rem; 
            text-align: center; 
            margin-bottom: 30px; 
            color: #ff4444;
            font-weight: bold;
        }
        .theme-indicator {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: rgba(255,68,68,0.1);
            border-radius: 8px;
            color: #ff4444;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        .tab {
            flex: 1;
            padding: 12px;
            background: #222;
            border: 2px solid #333;
            color: white;
            cursor: pointer;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            transition: all 0.3s;
        }
        .tab:hover { background: #333; }
        .tab.active { background: #ff4444; border-color: #ff4444; }
        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            margin-bottom: 8px;
            color: #aaa;
            font-size: 0.95rem;
        }
        input {
            width: 100%;
            padding: 14px;
            background: #222;
            border: 2px solid #444;
            color: white;
            border-radius: 8px;
            font-size: 1rem;
        }
        input:focus { outline: none; border-color: #ff4444; }
        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            margin-top: 10px;
            text-transform: uppercase;
        }
        button[type="submit"]:hover { transform: scale(1.02); }
        .error { 
            background: #ff4444; 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            text-align: center;
        }
        .success { 
            background: #00ff00; 
            color: #000; 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #ff4444;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">SHORTF▲CTORY</div>
        
        <?php if (isset($_SESSION['theme'])): ?>
            <div class="theme-indicator">
                Selected Theme: <strong><?php echo strtoupper($_SESSION['theme']); ?></strong>
                <?php 
                    $icons = ['girl' => '💖', 'boy' => '⚡', 'zombie' => '🧟'];
                    echo $icons[$_SESSION['theme']] ?? '';
                ?>
            </div>
        <?php endif; ?>
        
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
            <form method="POST" action="auth.php">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" name="login">LOGIN</button>
            </form>
        </div>
        
        <div id="register" class="tab-content">
            <form method="POST" action="auth.php">
                <div class="form-group">
                    <label>Username (3+ characters)</label>
                    <input type="text" name="username" required minlength="3" autocomplete="username">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label>Password (6+ characters)</label>
                    <input type="password" name="password" required minlength="6" autocomplete="new-password">
                </div>
                <button type="submit" name="register">CREATE ACCOUNT</button>
            </form>
        </div>
        
        <div class="back-link">
            <a href="index.php">← Change Theme</a>
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
