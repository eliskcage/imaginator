<?php
/**
 * PHP Proxy for Node.js AI Server
 * Forwards requests from frontend to Node.js server running on localhost:3000
 */

// INCREASE TIMEOUTS FOR AI PROCESSING
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);
ini_set('default_socket_timeout', 300);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$nodeUrl = 'http://localhost:3000';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['health'])) {
        // Health check
        $endpoint = $nodeUrl . '/api/health';
        $response = file_get_contents($endpoint);

        if ($response === false) {
            throw new Exception('Node.js server not responding');
        }

        echo $response;
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Forward file upload to Node.js
        if (!isset($_FILES['audio'])) {
            throw new Exception('No audio file uploaded');
        }

        $file = $_FILES['audio'];
        $endpoint = $nodeUrl . '/api/analyze-dramatic';

        // Create cURL request
        $ch = curl_init($endpoint);

        $cfile = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
        $postData = ['audio' => $cfile];

        // Forward subtitles if provided
        if (isset($_POST['subtitles'])) {
            $postData['subtitles'] = $_POST['subtitles'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300, // 5 minutes for AI processing
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('Failed to connect to AI server: ' . $error);
        }

        http_response_code($httpCode);
        echo $response;
        exit;
    }

    throw new Exception('Invalid request method');

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
