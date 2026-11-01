<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$targets = [
    $root . DIRECTORY_SEPARATOR . 'index.php',
];

foreach (['User', 'Admin', 'Bookpalace'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . DIRECTORY_SEPARATOR . $directory));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), ['php', 'html'], true)) {
            $targets[] = $file->getPathname();
        }
    }
}

$updated = 0;
$assetVersion = '20261101-smooth2';
foreach ($targets as $path) {
    if (!is_file($path)) {
        continue;
    }

    $contents = file_get_contents($path);
    if ($contents === false || stripos($contents, '</head>') === false) {
        continue;
    }

    // Repair the malformed cache URL produced by an older installer version.
    $contents = preg_replace(
        '~\s*<link rel="stylesheet" href="[^"]*assets/css/0261101-smooth2">\s*~i',
        PHP_EOL,
        $contents
    );
    $contents = preg_replace(
        '~\s*<script defer src="[^"]*assets/js/0261101-smooth2"></script>\s*~i',
        PHP_EOL,
        $contents
    );

    if (str_contains($contents, 'page-transitions.css')) {
        $versionedContents = preg_replace(
            '/(page-transitions\.(?:css|js)\?v=)[^"\']+/i',
            '${1}' . $assetVersion,
            $contents
        );
        if ($versionedContents !== null && $versionedContents !== $contents) {
            file_put_contents($path, $versionedContents);
            $updated++;
        }
        continue;
    }

    $isRootPage = dirname($path) === $root;
    $prefix = $isRootPage ? '' : '../';
    $assets = '    <link rel="stylesheet" href="' . $prefix . 'assets/css/page-transitions.css?v=' . $assetVersion . '">' . PHP_EOL
        . '    <script defer src="' . $prefix . 'assets/js/page-transitions.js?v=' . $assetVersion . '"></script>' . PHP_EOL;
    $updatedContents = preg_replace('/\s*<\/head>/i', PHP_EOL . $assets . '</head>', $contents, 1);

    if ($updatedContents !== null && $updatedContents !== $contents) {
        file_put_contents($path, $updatedContents);
        $updated++;
    }
}

echo "Installed page transitions in {$updated} documents.\n";
