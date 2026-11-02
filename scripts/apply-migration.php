<?php
declare(strict_types=1);

// Operator-only helper: apply one SQL migration file with the project-owner
// credentials in ADMIN_DB_* (the restricted runtime role cannot change the
// schema).
//
//   ADMIN_DB_HOST=… php scripts/apply-migration.php supabase/migrations/<file>.sql [--dry-run]
//
// The file is executed as one simple-query batch, so the migration must carry
// its own BEGIN/COMMIT. Only files inside supabase/migrations are accepted.
// Never expose this script through a web server.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';

function admin_config(string $name): string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        throw new RuntimeException("Missing required environment variable: $name");
    }
    return $value;
}

try {
    $arguments = array_slice($argv, 1);
    $dryRun = in_array('--dry-run', $arguments, true);
    $files = array_values(array_filter($arguments, static fn (string $argument): bool => $argument !== '--dry-run'));
    if (count($files) !== 1) {
        fwrite(STDERR, 'Usage: php scripts/apply-migration.php supabase/migrations/<file>.sql [--dry-run]' . PHP_EOL);
        exit(2);
    }

    $root = dirname(__DIR__);
    $migrationDirectory = realpath($root . '/supabase/migrations');
    $path = realpath($files[0]);
    if ($migrationDirectory === false || $path === false || !is_file($path)) {
        throw new RuntimeException('Migration file not found: ' . $files[0]);
    }
    $migrationPrefix = rtrim(str_replace('\\', '/', $migrationDirectory), '/') . '/';
    if (stripos(str_replace('\\', '/', $path), $migrationPrefix) !== 0 || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'sql') {
        throw new RuntimeException('Only .sql files inside supabase/migrations can be applied.');
    }

    $sql = file_get_contents($path);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('Migration file is empty: ' . $path);
    }

    $host = admin_config('ADMIN_DB_HOST');
    $port = getenv('ADMIN_DB_PORT') ?: '5432';
    $database = getenv('ADMIN_DB_NAME') ?: 'postgres';
    $user = admin_config('ADMIN_DB_USER');
    $password = admin_config('ADMIN_DB_PASSWORD');
    $sslmode = getenv('ADMIN_DB_SSLMODE') ?: 'verify-full';
    $rootcert = getenv('ADMIN_DB_SSLROOTCERT') ?: '';
    foreach ([$host, $port, $database, $user, $sslmode, $rootcert] as $value) {
        if (preg_match('/[;\r\n\x00]/', $value) === 1) {
            throw new RuntimeException('Invalid ADMIN_DB_* configuration.');
        }
    }
    if (!ctype_digit($port) || !in_array($sslmode, ['verify-full', 'disable', 'require'], true)) {
        throw new RuntimeException('Invalid ADMIN_DB_PORT or ADMIN_DB_SSLMODE.');
    }
    if ($sslmode !== 'disable' && $sslmode !== 'require' && ($rootcert === '' || !is_file($rootcert))) {
        throw new RuntimeException('verify-full requires a readable ADMIN_DB_SSLROOTCERT file.');
    }

    echo 'Migration   : ' . basename($path) . PHP_EOL;
    echo 'Owner login : ' . $user . '@' . $host . ':' . $port . '/' . $database . PHP_EOL;
    echo 'Size        : ' . strlen($sql) . ' bytes' . PHP_EOL;
    if ($dryRun) {
        echo 'Dry run: configuration and file are valid, nothing was executed.' . PHP_EOL;
        exit(0);
    }

    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=$sslmode;connect_timeout=15";
    if ($rootcert !== '') {
        $dsn .= ";sslrootcert=$rootcert";
    }
    $owner = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    try {
        $owner->exec($sql);
    } catch (Throwable $error) {
        // A failed statement leaves the implicit block aborted; the migration's own
        // BEGIN/COMMIT does not survive an error, so close it explicitly.
        try {
            $owner->exec('ROLLBACK');
        } catch (Throwable $ignored) {
            // Nothing to roll back.
        }
        throw $error;
    }
    $owner = null;

    echo 'Applied ' . basename($path) . '.' . PHP_EOL;
    echo 'Runtime role unchanged: only bookshop_app keeps the grants the migration set.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Migration failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
