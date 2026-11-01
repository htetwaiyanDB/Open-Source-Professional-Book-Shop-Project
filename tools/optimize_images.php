<?php

declare(strict_types=1);

ini_set('memory_limit', '512M');

$sourceDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Photos';
$outputDirectory = $sourceDirectory . DIRECTORY_SEPARATOR . 'optimized';
$maximumDimension = 1600;
$webpQuality = 80;

if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0775, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, "Unable to create optimized image directory.\n");
    exit(1);
}

$sourceBytes = 0;
$optimizedBytes = 0;
$converted = 0;
$skipped = 0;

foreach (new DirectoryIterator($sourceDirectory) as $file) {
    if (!$file->isFile() || !preg_match('/\.(?:png|jpe?g)$/i', $file->getFilename())) {
        continue;
    }

    $sourcePath = $file->getPathname();
    $outputPath = $outputDirectory . DIRECTORY_SEPARATOR . $file->getFilename() . '.webp';
    $sourceBytes += $file->getSize();

    if (is_file($outputPath) && filemtime($outputPath) >= $file->getMTime()) {
        $optimizedBytes += filesize($outputPath);
        $skipped++;
        continue;
    }

    $contents = file_get_contents($sourcePath);
    $image = $contents === false ? false : imagecreatefromstring($contents);
    if ($image === false) {
        fwrite(STDERR, "Skipped unreadable image: {$file->getFilename()}\n");
        continue;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $scale = min(1, $maximumDimension / max($width, $height));
    $targetWidth = max(1, (int) round($width * $scale));
    $targetHeight = max(1, (int) round($height * $scale));

    if ($scale < 1 || !imageistruecolor($image)) {
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    if (!imagewebp($image, $outputPath, $webpQuality)) {
        fwrite(STDERR, "Failed to optimize: {$file->getFilename()}\n");
        imagedestroy($image);
        continue;
    }

    imagedestroy($image);
    $optimizedBytes += filesize($outputPath);
    $converted++;
}

$savedBytes = max(0, $sourceBytes - $optimizedBytes);
$savingPercent = $sourceBytes > 0 ? ($savedBytes / $sourceBytes) * 100 : 0;

printf(
    "Converted: %d, unchanged: %d, source: %.2f MB, optimized: %.2f MB, saved: %.1f%%\n",
    $converted,
    $skipped,
    $sourceBytes / 1048576,
    $optimizedBytes / 1048576,
    $savingPercent
);
