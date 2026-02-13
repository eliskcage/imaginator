<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Check if Stripe is loaded
if (!class_exists('\Stripe\Stripe')) {
    echo json_encode(['error' => 'Stripe library not loaded. Run install_stripe.php first.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$scenario_id = intval($data['scenario_id']);
$scenario_name = $data['scenario_name'];
$price = floatval($data['price']);

try {
    // Create Stripe Checkout Session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'gbp',
                'product_data' => [
                    'name' => $scenario_name,
                    'description' => 'ShortFactory Video Scenario',
                ],
                'unit_amount' => $price * 100, // Convert to pence
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => SITE_URL . '/payment_success.php?session_id={CHECKOUT_SESSION_ID}&scenario_id=' . $scenario_id,
        'cancel_url' => SITE_URL . '/dashboard.php',
        'customer_email' => $_SESSION['email'] ?? null,
        'metadata' => [
            'user_id' => $_SESSION['user_id'],
            'scenario_id' => $scenario_id,
        ]
    ]);
    
    echo json_encode(['id' => $session->id]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>