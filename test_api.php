<?php
require_once 'config.php';

// Simulate logged in user
$_SESSION['user_id'] = 1;

echo "<h1>API Test</h1>";
echo "<style>body{font-family:Arial;padding:20px;background:#000;color:#fff;}</style>";

$scenario_id = 1; // Test with demo scenario

$stmt = $pdo->prepare("
    SELECT s.* FROM scenarios s 
    LEFT JOIN purchases p ON p.scenario_id = s.id AND p.user_id = ? AND p.status = 'completed'
    WHERE s.id = ? AND (s.creator_id = ? OR p.id IS NOT NULL OR s.status = 'published')
");
$stmt->execute([$_SESSION['user_id'], $scenario_id, $_SESSION['user_id']]);

if ($row = $stmt->fetch()) {
    echo "<p style='color:green;'>✅ Scenario found: " . htmlspecialchars($row['name']) . "</p>";
    echo "<h3>Code Template Length: " . strlen($row['code_template']) . " characters</h3>";
    
    $json = json_encode(['success' => true, 'scenario' => $row]);
    echo "<p style='color:green;'>✅ JSON encoded successfully</p>";
    echo "<pre>" . substr($json, 0, 500) . "...</pre>";
} else {
    echo "<p style='color:red;'>❌ Scenario not found</p>";
}

echo "<hr>";
echo "<h3>Now test the actual API:</h3>";
echo "<p><a href='api.php?action=get_scenario&scenario_id=1' target='_blank' style='color:#ff4444;'>api.php?action=get_scenario&scenario_id=1</a></p>";
?>
