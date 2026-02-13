<?php
require_once 'config.php';

echo "<h1>Gemini API Test</h1>";
echo "<style>body{font-family:Arial;padding:20px;background:#000;color:#fff;}</style>";

echo "<p><strong>API Key from config:</strong> " . GEMINI_API_KEY . "</p>";

$api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=" . GEMINI_API_KEY;

echo "<p><strong>Testing API URL:</strong><br>" . substr($api_url, 0, 100) . "...</p>";

$data = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => 'Say hello in one word']
            ]
        ]
    ]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<hr>";
echo "<p><strong>HTTP Status:</strong> $http_code</p>";

if ($http_code == 200) {
    echo "<p style='color:green;'>✅ API Working!</p>";
    $result = json_decode($response, true);
    echo "<pre>" . print_r($result, true) . "</pre>";
} elseif ($http_code == 429) {
    echo "<p style='color:red;'>❌ Still Rate Limited (429)</p>";
    echo "<p>This key may be from the same project. You need to:</p>";
    echo "<ol>
        <li>Go to <a href='https://aistudio.google.com/app/apikey' target='_blank' style='color:#ff4444;'>Google AI Studio</a></li>
        <li>Click 'Create API key in <strong>NEW PROJECT</strong>' (important!)</li>
        <li>Copy the new key</li>
    </ol>";
} else {
    echo "<p style='color:orange;'>⚠️ Error: $http_code</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
?>
