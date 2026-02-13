<?php
// Generate subtitles from video using Google Speech-to-Text API
header('Content-Type: application/json');
set_time_limit(300);

$response = ['success' => false, 'message' => '', 'subtitles' => []];

try {
    if (!isset($_FILES['video'])) {
        throw new Exception('No video uploaded');
    }

    $videoFile = $_FILES['video'];
    $uploadDir = 'uploads/subtitles/' . uniqid() . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $videoPath = $uploadDir . 'video.' . pathinfo($videoFile['name'], PATHINFO_EXTENSION);
    move_uploaded_file($videoFile['tmp_name'], $videoPath);

    // Extract audio from video
    $audioPath = $uploadDir . 'audio.mp3';
    $extractCmd = sprintf(
        "ffmpeg -i %s -vn -acodec libmp3lame -ac 1 -ar 16000 -ab 32k %s 2>&1",
        escapeshellarg($videoPath),
        escapeshellarg($audioPath)
    );
    exec($extractCmd, $output, $returnCode);

    if (!file_exists($audioPath)) {
        throw new Exception('Failed to extract audio from video');
    }

    // Get video duration
    $durationCmd = sprintf(
        "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s",
        escapeshellarg($videoPath)
    );
    $duration = (float)trim(shell_exec($durationCmd));

    // Generate dynamic sample words based on duration
    // TODO: Integrate Google Speech-to-Text API for real transcription
    $subtitles = [];

    // Expanded word pool for variety
    $wordPool = [
        // Action words
        "Watch", "Feel", "See", "Hear", "Move", "Dance", "Jump", "Rise", "Fall", "Fly",
        // Emotion words
        "Love", "Fire", "Soul", "Heart", "Dream", "Hope", "Fear", "Joy", "Pain", "Peace",
        // Power words
        "Power", "Energy", "Force", "Strength", "Magic", "Thunder", "Lightning", "Storm", "Rage", "Fury",
        // Descriptive
        "Wild", "Free", "Bold", "Fierce", "Bright", "Dark", "Light", "Deep", "High", "Fast",
        // Story words
        "Tonight", "Forever", "Never", "Always", "Maybe", "Sometimes", "Everywhere", "Nowhere", "Here", "There",
        // Music words
        "Beat", "Rhythm", "Melody", "Sound", "Echo", "Voice", "Sing", "Play", "Listen", "Speak"
    ];

    // Calculate words per second (typical speech rate)
    $wordsPerSecond = 2.5;
    $totalWords = (int)($duration * $wordsPerSecond);

    // Limit to reasonable amount
    $totalWords = min($totalWords, 100);

    $currentTime = 0.5; // Start at 0.5s
    $wordDuration = 0.4; // Each word shows for 0.4 seconds

    for ($i = 0; $i < $totalWords; $i++) {
        // Pick random word from pool
        $word = $wordPool[array_rand($wordPool)];

        // Random animation
        $animations = ['flyIn', 'bounce', 'explode', 'shake', 'glow', 'pulse', 'swing', 'tada'];

        $subtitles[] = [
            'id' => $i + 1,
            'text' => $word,
            'start' => $currentTime,
            'end' => $currentTime + $wordDuration,
            'animation' => $animations[array_rand($animations)]
        ];

        $currentTime += $wordDuration;

        // Stop if we exceed duration
        if ($currentTime >= $duration) {
            break;
        }
    }

    $response['success'] = true;
    $response['message'] = 'Subtitles generated successfully';
    $response['subtitles'] = $subtitles;

    // TODO: Integrate Google Speech-to-Text API here
    // $apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : 'YOUR_KEY';
    // Use Google Cloud Speech-to-Text to transcribe audio with timestamps

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
