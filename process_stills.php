<?php
// Process stills + MP3 into video with ShortFactory watermark
header('Content-Type: application/json');
set_time_limit(300); // 5 minutes for processing

$response = ['success' => false, 'message' => ''];

try {
    // Check if files were uploaded
    if (!isset($_FILES['audio']) || empty($_FILES)) {
        throw new Exception('No files uploaded');
    }

    $audioFile = $_FILES['audio'];
    $uploadDir = 'uploads/stills/' . uniqid() . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Save audio file
    $audioPath = $uploadDir . 'audio.mp3';
    if (!move_uploaded_file($audioFile['tmp_name'], $audioPath)) {
        throw new Exception('Failed to save audio file');
    }

    // Save and sort image files
    $imageFiles = [];
    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'image_') === 0) {
            $index = (int)str_replace('image_', '', $key);
            $imagePath = $uploadDir . sprintf('img_%03d.jpg', $index);

            if (move_uploaded_file($file['tmp_name'], $imagePath)) {
                $imageFiles[$index] = $imagePath;
            }
        }
    }

    ksort($imageFiles); // Sort by index

    if (count($imageFiles) < 2) {
        throw new Exception('Need at least 2 images');
    }

    // Get audio duration
    $audioDuration = shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($audioPath));
    $audioDuration = (float)trim($audioDuration);

    if ($audioDuration <= 0) {
        $audioDuration = 30; // Default to 30 seconds
    }

    // Calculate duration per image
    $durationPerImage = $audioDuration / count($imageFiles);
    if ($durationPerImage < 0.5) {
        $durationPerImage = 0.5; // Minimum 0.5 seconds per image
    }

    // Create FFmpeg concat file with crossfade
    $concatFile = $uploadDir . 'concat.txt';
    $fp = fopen($concatFile, 'w');
    foreach ($imageFiles as $imagePath) {
        fwrite($fp, "file '" . basename($imagePath) . "'\n");
        fwrite($fp, "duration " . $durationPerImage . "\n");
    }
    // Add last image again for final frame
    fwrite($fp, "file '" . basename(end($imageFiles)) . "'\n");
    fclose($fp);

    // Output video path
    $outputVideo = $uploadDir . 'output.mp4';

    // FFmpeg command: Create video from images with crossfade + audio + watermark
    $watermarkText = "SHORTFACTORY";

    $ffmpegCmd = sprintf(
        "cd %s && ffmpeg -f concat -safe 0 -i concat.txt " .
        "-i audio.mp3 " .
        "-vf \"scale=720:1280:force_original_aspect_ratio=decrease,pad=720:1280:(ow-iw)/2:(oh-ih)/2," .
        "drawtext=text='%s':fontsize=24:fontcolor=white@0.7:x=w-tw-20:y=h-th-20:" .
        "box=1:boxcolor=black@0.7:boxborderw=10\" " .
        "-c:v libx264 -preset medium -crf 23 " .
        "-c:a aac -b:a 128k " .
        "-shortest " .
        "-y output.mp4 2>&1",
        escapeshellarg($uploadDir),
        $watermarkText
    );

    $output = shell_exec($ffmpegCmd);

    if (!file_exists($outputVideo)) {
        throw new Exception('Video creation failed: ' . $output);
    }

    // Return success with download URL
    $downloadUrl = str_replace($_SERVER['DOCUMENT_ROOT'], '', $outputVideo);
    $response['success'] = true;
    $response['message'] = 'Video created successfully!';
    $response['download_url'] = $downloadUrl;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
