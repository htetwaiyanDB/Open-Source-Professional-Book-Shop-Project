<?php
declare(strict_types=1);

// Process environment overrides the optional, untracked local .env file.
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) { continue; }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        if (preg_match('/^[A-Z][A-Z0-9_]*$/', $name) && getenv($name) === false) {
            $value = trim($value);
            if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
                $value = substr($value, 1, -1);
            }
            putenv($name . '=' . $value);
        }
    }
}
if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params(['httponly' => true, 'secure' => getenv('APP_ENV') !== 'local', 'samesite' => 'Lax', 'path' => '/']);
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    ob_start();
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: no-store');
    set_exception_handler(function (Throwable $error): void {
        // Never log SQL parameters, DSNs, credentials or customer data.
        error_log('Bookshop request failed [' . get_class($error) . ', code ' . $error->getCode() . ']');
        if (ob_get_level()) { ob_clean(); }
        http_response_code(500);
        echo 'The request could not be completed. Please try again or contact the administrator.';
    });
}
function e($value): string { return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): void { header('Location: ' . $path); exit; }
function csrf_field(): string
{
    if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}
function verify_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !is_string($_POST['csrf_token'] ?? null)
        || !is_string($_SESSION['csrf_token'] ?? null) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Invalid or expired request. Reload the form and try again.');
    }
}
function flash_error(string $message): void { $_SESSION['error_message'] = $message; }
function render_flash(): string
{
    $message = $_SESSION['error_message'] ?? '';
    unset($_SESSION['error_message']);
    return $message === '' ? '' : '<div class="alert alert-info" role="alert">' . e($message) . '</div>';
}
if (PHP_SAPI !== 'cli' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') { verify_csrf(); }