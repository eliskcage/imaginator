<?php
// Render video with kinetic subtitles using FFmpeg
header('Content-Type: application/json');
set_time_limit(600); // 10 minutes for rendering

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_FILES['video']) || !isset($_POST['subtitles'])) {
        throw new Exception('Missing video or subtitles');
    }

    $videoFile = $_FILES['video'];
    $subtitles = json_decode($_POST['subtitles'], true);
    $uploadDir = 'uploads/subtitles/' . uniqid() . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $videoPath = $uploadDir . 'input.' . pathinfo($videoFile['name'], PATHINFO_EXTENSION);
    move_uploaded_file($videoFile['tmp_name'], $videoPath);

    // Create SRT subtitle file
    $srtPath = $uploadDir . 'subtitles.srt';
    $srtContent = '';

    foreach ($subtitles as $index => $sub) {
        $startTime = formatSrtTime($sub['start']);
        $endTime = formatSrtTime($sub['end']);
        $text = str_replace("\n", " ", $sub['text']);

        $srtContent .= ($index + 1) . "\n";
        $srtContent .= $startTime . " --> " . $endTime . "\n";
        $srtContent .= $text . "\n\n";
    }

    file_put_contents($srtPath, $srtContent);

    // Output video path
    $outputVideo = $uploadDir . 'output.mp4';

    // FFmpeg command: Burn subtitles with styling + ShortFactory watermark
    $ffmpegCmd = sprintf(
        "ffmpeg -i %s " .
        "-vf \"subtitles=%s:force_style='FontName=Arial,FontSize=28,Bold=1,PrimaryColour=&HFFFFFF&," .
        "OutlineColour=&H000000&,Outline=3,Shadow=2,Alignment=2',drawtext=text='SHORTFACTORY':" .
        "fontsize=20:fontcolor=white@0.7:x=w-tw-20:y=h-th-20:" .
        "box=1:boxcolor=black@0.7:boxborderw=8\" " .
        "-c:v libx264 -preset medium -crf 23 " .
        "-c:a copy " .
        "-y %s 2>&1",
        escapeshellarg($videoPath),
        escapeshellarg($srtPath),
        escapeshellarg($outputVideo)
    );

    $output = shell_exec($ffmpegCmd);

    if (!file_exists($outputVideo)) {
        throw new Exception('Video rendering failed: ' . $output);
    }

    // Return download URL
    $downloadUrl = str_replace($_SERVER['DOCUMENT_ROOT'], '', $outputVideo);
    $response['success'] = true;
    $response['message'] = 'Video rendered successfully!';
    $response['download_url'] = $downloadUrl;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

function formatSrtTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = floor($seconds % 60);
    $millis = floor(($seconds - floor($seconds)) * 1000);

    return sprintf("%02d:%02d:%02d,%03d", $hours, $minutes, $secs, $millis);
}

echo json_encode($response);
?>
