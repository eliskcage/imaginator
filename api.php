<?php
require_once 'config.php';

// Set proper JSON header
header('Content-Type: application/json');

// Error handling
ini_set('display_errors', 0);
error_reporting(0);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

try {
    // VOTE FOR SCENARIO
    if ($action === 'vote') {
        $data = json_decode(file_get_contents('php://input'), true);
        $scenario_id = intval($data['scenario_id']);
        
        // Check if already voted
        $stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND scenario_id = ?");
        $stmt->execute([$user_id, $scenario_id]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Already voted']);
            exit;
        }
        
        // Add vote
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, scenario_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $scenario_id]);
        
        // Increment scenario votes
        $pdo->exec("UPDATE scenarios SET votes = votes + 1 WHERE id = $scenario_id");
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // SAVE NEW SCENARIO
    elseif ($action === 'save_scenario') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $name = $data['name'];
        $description = $data['description'];
        $theme_data = json_encode($data['theme_data']);
        $code_template = $data['code_template'];
        $tags = $data['tags'];
        $price = floatval($data['price']);
        
        $stmt = $pdo->prepare("INSERT INTO scenarios (creator_id, name, description, theme_data, code_template, tags, price, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'published')");
        $stmt->execute([$user_id, $name, $description, $theme_data, $code_template, $tags, $price]);
        
        echo json_encode(['success' => true, 'scenario_id' => $pdo->lastInsertId()]);
        exit;
    }
    
    // GET SCENARIO CODE
    elseif ($action === 'get_scenario') {
        $scenario_id = intval($_GET['scenario_id']);
        
        // Check if user owns or created this scenario
        $stmt = $pdo->prepare("
            SELECT s.* FROM scenarios s 
            LEFT JOIN purchases p ON p.scenario_id = s.id AND p.user_id = ? AND p.status = 'completed'
            WHERE s.id = ? AND (s.creator_id = ? OR p.id IS NOT NULL OR s.price = 0)
        ");
        $stmt->execute([$user_id, $scenario_id, $user_id]);
        
        if ($row = $stmt->fetch()) {
            // Don't send ALL data - code_template can be huge
            $response = [
                'success' => true,
                'scenario' => [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'code_template' => $row['code_template']
                ]
            ];
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'error' => 'Access denied or scenario not found']);
        }
        exit;
    }
    
    else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>
