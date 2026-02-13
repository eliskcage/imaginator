<?php
// ShortFactory Directory Zipper
// Drop this in the root and visit it in browser to create zip

set_time_limit(300); // 5 minutes max

$sourceDir = 'Shortfactory'; // Directory to zip
$outputZip = 'shortfactory_backup_' . date('Y-m-d_H-i-s') . '.zip';

function zipDirectory($source, $destination) {
    if (!extension_loaded('zip')) {
        die('❌ ZIP extension not loaded');
    }

    if (!file_exists($source)) {
        die('❌ Source directory not found: ' . $source);
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZipArchive::CREATE)) {
        die('❌ Failed to create zip file');
    }

    $source = realpath($source);

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $fileCount = 0;
    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($source) + 1);

            $zip->addFile($filePath, $relativePath);
            $fileCount++;
        }
    }

    $zip->close();

    return $fileCount;
}

echo "<h1>📦 ShortFactory Zipper</h1>";
echo "<p>Creating zip of: <strong>$sourceDir</strong></p>";

$fileCount = zipDirectory($sourceDir, $outputZip);

if (file_exists($outputZip)) {
    $size = round(filesize($outputZip) / 1024 / 1024, 2);
    echo "<p>✅ <strong>Success!</strong></p>";
    echo "<p>Files: $fileCount<br>Size: {$size} MB</p>";
    echo "<p><a href='$outputZip' style='font-size:20px;padding:10px 20px;background:#0a0;color:#fff;text-decoration:none;'>⬇️ Download $outputZip</a></p>";
} else {
    echo "<p>❌ Failed to create zip</p>";
}
?>
