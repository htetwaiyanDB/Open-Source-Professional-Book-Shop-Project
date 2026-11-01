<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$directories = ['User', 'Admin', 'Bookpalace'];
$extensions = ['php', 'html'];
$updated = 0;

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . DIRECTORY_SEPARATOR . $directory));

    foreach ($iterator as $file) {
        if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $extensions, true)) {
            continue;
        }

        $path = $file->getPathname();
        $contents = file_get_contents($path);
        if ($contents === false) {
            continue;
        }

        $localized = preg_replace(
            '~<link\b[^>]*href=["\']https?://[^"\']*bootstrap[^"\']*\.css[^"\']*["\'][^>]*>~i',
            '<link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">',
            $contents
        );
        $localized = preg_replace(
            '~<script\b[^>]*src=["\']https?://[^"\']*bootstrap[^"\']*\.js[^"\']*["\'][^>]*>\s*</script>~i',
            '<script defer src="../assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>',
            $localized
        );
        $localized = preg_replace(
            '~<link\b[^>]*href=["\']https?://[^"\']*font-awesome[^"\']*\.css[^"\']*["\'][^>]*>~i',
            '<link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">',
            $localized
        );
        $localized = preg_replace(
            '~<script\b[^>]*src=["\']https?://(?:kit\.fontawesome\.com|cdnjs\.cloudflare\.com/ajax/libs/font-awesome)[^"\']*["\'][^>]*>\s*</script>~i',
            '<link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">',
            $localized
        );
        $localized = preg_replace(
            '~<link\b[^>]*href=["\']https://fonts\.googleapis\.com/[^"\']+["\'][^>]*>~i',
            '',
            $localized
        );

        // Avoid parsing the same local stylesheet twice when a page previously loaded
        // both Font Awesome CSS and its JavaScript kit.
        $localized = preg_replace(
            '~(<link rel="stylesheet" href="\.\./assets/vendor/fontawesome/css/all\.min\.css">\s*){2,}~i',
            '<link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">' . PHP_EOL,
            $localized
        );

        if ($localized !== $contents) {
            file_put_contents($path, $localized);
            $updated++;
        }
    }
}

echo "Localized vendor assets in {$updated} files.\n";
