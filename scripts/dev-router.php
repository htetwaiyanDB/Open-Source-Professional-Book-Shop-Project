<?php
declare(strict_types=1);

// Development router for the PHP built-in server. Apache/shared hosting uses
// .htaccess instead. Denies private tooling and serves only allow-listed paths.
//   php -S 127.0.0.1:8099 scripts/dev-router.php

$root = dirname(__DIR__);
$uri = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$uri = str_replace('\\', '/', $uri);
$uri = preg_replace('#/+#', '/', $uri) ?? '/';

function deny(): void
{
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Not found\n";
    exit;
}

if ($uri === '/' || $uri === '') {
    $uri = '/User/home.php';
}
$blocked = ['/.env', '/.git', '/database', '/supabase', '/scripts', '/includes', '/tests', '/tools'];
foreach ($blocked as $prefix) {
    if ($uri === $prefix || strpos($uri, $prefix . '/') === 0) {
        deny();
    }
}
if (strpos($uri, '..') !== false || strpos($uri, "\0") !== false) {
    deny();
}

$relative = ltrim($uri, '/');
$path = realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));
$rootReal = realpath($root);
if ($path === false || $rootReal === false || strpos($path, $rootReal . DIRECTORY_SEPARATOR) !== 0) {
    deny();
}
$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

if ($extension === 'php') {
    $allowed = false;
    if ($relative === 'index.php') {
        $allowed = true;
    } elseif (preg_match('#^User/[A-Za-z0-9_.-]+\.php$#', $relative) === 1) {
        $allowed = basename($relative) !== 'userfunction.php';
    } elseif (preg_match('#^Admin/[A-Za-z0-9_.-]+\.php$#', $relative) === 1) {
        $allowed = !in_array(basename($relative), ['connect.php', 'function.php'], true);
    }
    if (!$allowed) {
        deny();
    }
    return false; // Let the built-in server execute the allow-listed script.
}

$allowedStatic = ['css', 'js', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'ico', 'svg', 'html', 'woff', 'woff2', 'ttf', 'eot'];
if (!in_array($extension, $allowedStatic, true)) {
    deny();
}
$staticDir = dirname($relative) === '.' ? '.' : dirname($relative);
$allowedRoots = ['.', 'assets', 'Photos', 'Bookpalace', 'User', 'Admin'];
$topLevel = explode('/', $staticDir)[0];
if (!in_array($topLevel, $allowedRoots, true)) {
    deny();
}
return false;
