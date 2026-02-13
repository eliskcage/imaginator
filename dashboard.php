<?php
// dashboard.php - Working Version
require_once 'config.php';

// Enable error display temporarily
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

if (!isset($_SESSION['theme'])) {
    header('Location: index.php');
    exit;
}

$theme = $_SESSION['theme'];
$user_id = $_SESSION['user_id'];

// Get top scenarios
try {
    $stmt = $pdo->prepare("SELECT s.*, u.username as creator_name FROM scenarios s JOIN users u ON s.creator_id = u.id WHERE s.status = 'published' ORDER BY s.votes DESC, s.purchases DESC LIMIT 20");
    $stmt->execute();
    $top_scenarios = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Check if user has purchased scenario
function hasPurchased($user_id, $scenario_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM purchases WHERE user_id = ? AND scenario_id = ? AND status = 'completed'");
    $stmt->execute([$user_id, $scenario_id]);
    return $stmt->fetch() !== false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHORTF▲CTORY Dashboard</title>
    <script src="https://js.stripe.com/v3/"></script>
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
        .user-info a { color: #ff4444; text-decoration: none; font-weight: bold; }
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
            transition: transform 0.3s;
            text-decoration: none;
            display: inline-block;
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
        .buy-btn { background: #635bff; color: white; }
        .buy-btn:hover { background: #5850e5; }
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
            <a href="auth.php?logout=1">Logout</a>
        </div>
    </div>
    
    <div class="upload-section">
        <h2>🎬 INSTANT EDITOR</h2>
        <p style="color:#aaa;margin-bottom:20px;">Upload your video and transform it instantly</p>
        <a href="scenario_builder.php" class="upload-btn">
            ✨ Create New Scenario
        </a>
    </div>
    
    <h2 style="margin-bottom:20px;">🏆 TOP SCENARIOS</h2>
    <div class="scenarios-grid">
        <?php if (empty($top_scenarios)): ?>
            <div style="grid-column: 1/-1; text-align:center; padding:40px; color:#888;">
                No scenarios yet! Be the first to create one.
            </div>
        <?php endif; ?>
        
        <?php foreach ($top_scenarios as $scenario): 
            $purchased = hasPurchased($user_id, $scenario['id']);
            $is_creator = ($scenario['creator_id'] == $user_id);
        ?>
        <div class="scenario-card">
            <div class="scenario-title"><?php echo htmlspecialchars($scenario['name']); ?></div>
            <div class="scenario-creator">by @<?php echo htmlspecialchars($scenario['creator_name']); ?></div>
            <div class="scenario-desc"><?php echo htmlspecialchars($scenario['description']); ?></div>
            
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
                    <?php if ($scenario['price'] > 0): ?>
                        <button class="buy-btn" onclick="buyScenario(<?php echo $scenario['id']; ?>, '<?php echo htmlspecialchars($scenario['name']); ?>', <?php echo $scenario['price']; ?>)">
                            💳 BUY £<?php echo number_format($scenario['price'], 2); ?>
                        </button>
                    <?php else: ?>
                        <button class="use-btn" onclick="useScenario(<?php echo $scenario['id']; ?>)">
                            FREE - USE IT
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <script>
        const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        
        function vote(scenarioId) {
            fetch('api.php?action=vote', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({scenario_id: scenarioId})
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    alert('✅ Vote recorded!');
                    location.reload();
                } else {
                    alert(data.error || 'Voting failed');
                }
            });
        }
        
        async function buyScenario(scenarioId, name, price) {
            const response = await fetch('create_checkout.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    scenario_id: scenarioId,
                    scenario_name: name,
                    price: price
                })
            });
            
            const session = await response.json();
            
            if (session.error) {
                alert('Error: ' + session.error);
                return;
            }
            
            const result = await stripe.redirectToCheckout({
                sessionId: session.id
            });
            
            if (result.error) {
                alert(result.error.message);
            }
        }
        
        function useScenario(scenarioId) {
            location.href = `editor.php?scenario_id=${scenarioId}`;
        }
    </script>
</body>
</html>