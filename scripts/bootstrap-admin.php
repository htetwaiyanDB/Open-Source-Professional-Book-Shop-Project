<?php
declare(strict_types=1);

// Operator-only bootstrap for the first administrator. Run from the project root:
//   php scripts/bootstrap-admin.php
// Uses ADMIN_DB_* (migration owner) and BOOKSHOP_ADMIN_* from the environment/.env.
// Never expose this script through a web server and never pass a password as an argument.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';

function admin_env(string $name): string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        throw new RuntimeException("Missing required environment variable: $name");
    }
    return $value;
}

try {
    $host = admin_env('ADMIN_DB_HOST');
    $port = admin_env('ADMIN_DB_PORT');
    $database = admin_env('ADMIN_DB_NAME');
    $user = admin_env('ADMIN_DB_USER');
    $password = getenv('ADMIN_DB_PASSWORD');
    $password = $password === false ? '' : $password;
    $sslmode = getenv('ADMIN_DB_SSLMODE') ?: 'verify-full';
    $rootcert = getenv('ADMIN_DB_SSLROOTCERT') ?: '';
    $local = in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    if ($password === '' && !$local) {
        throw new RuntimeException('Missing required environment variable: ADMIN_DB_PASSWORD');
    }
    foreach ([$host, $port, $database, $user, $sslmode, $rootcert] as $value) {
        if (preg_match('/[;\r\n\x00]/', $value)) {
            throw new RuntimeException('Invalid ADMIN_DB_* configuration.');
        }
    }
    if (!ctype_digit($port) || !in_array($sslmode, ['verify-full', 'disable', 'require'], true)) {
        throw new RuntimeException('Invalid ADMIN_DB_PORT or ADMIN_DB_SSLMODE.');
    }
    if (!$local && ($sslmode !== 'verify-full' || $rootcert === '' || !is_file($rootcert))) {
        throw new RuntimeException('Remote admin connections require verify-full and ADMIN_DB_SSLROOTCERT.');
    }

    $username = trim(admin_env('BOOKSHOP_ADMIN_USERNAME'));
    $email = trim(admin_env('BOOKSHOP_ADMIN_EMAIL'));
    $plainPassword = admin_env('BOOKSHOP_ADMIN_PASSWORD');
    if (strlen($username) < 3 || strlen($username) > 100 || !preg_match('/^[^\x00-\x1f]+$/', $username)) {
        throw new RuntimeException('BOOKSHOP_ADMIN_USERNAME must be 3-100 printable characters.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('BOOKSHOP_ADMIN_EMAIL must be a valid email address.');
    }
    $length = strlen($plainPassword);
    if ($length < 12 || $length > 72) {
        throw new RuntimeException('BOOKSHOP_ADMIN_PASSWORD must be 12-72 bytes.');
    }

    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=$sslmode;connect_timeout=15";
    if ($rootcert !== '') {
        $dsn .= ';sslrootcert=' . $rootcert;
    }
    $owner = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $connection = $owner;
    $connection->beginTransaction();
    try {
        $exists = $connection->prepare('SELECT 1 FROM bookshop.users WHERE lower(username) = lower(?)');
        $exists->execute([$username]);
        if ($exists->fetchColumn() !== false) {
            throw new RuntimeException('That username already exists; choose another administrator username.');
        }
        $insert = $connection->prepare("INSERT INTO bookshop.users (username, password, email, phone, address, role) VALUES (?, ?, ?, '', '', 'admin')");
        $insert->execute([$username, $hash, $email]);
        $connection->commit();
    } catch (Throwable $error) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $error;
    }
    echo "Administrator created. Sign in through Admin/index.php and delete this shell history entry.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Bootstrap failed: ' . $error->getMessage() . "\n");
    exit(1);
}
