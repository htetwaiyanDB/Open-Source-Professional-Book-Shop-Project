<?php
declare(strict_types=1);

// Operator-only helper: set the password of the restricted runtime role
// (bookshop_app) with the migration owner credentials in ADMIN_DB_*.
//
//   BOOKSHOP_DB_PASSWORD='new-long-secret' php scripts/set-db-password.php [--write-env] [--dry-run]
//
// The new password is never accepted as a command-line argument: it would end
// up in shell history and in `ps` output. Provide it through the environment or
// type it at the hidden prompt. --write-env also updates DB_PASSWORD in the
// .env next to this repository, so the web application starts using it at once.
// Never expose this script through a web server.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';

const RUNTIME_ROLE = 'bookshop_app';

function set_db_password_env(string $name): string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        throw new RuntimeException("Missing required environment variable: $name");
    }
    return $value;
}

function read_new_secret(): string
{
    $fromEnvironment = getenv('BOOKSHOP_DB_PASSWORD');
    if (is_string($fromEnvironment) && $fromEnvironment !== '') {
        return $fromEnvironment;
    }

    $hideInput = stripos(PHP_OS_FAMILY, 'Windows') === false && function_exists('shell_exec');
    if ($hideInput) {
        shell_exec('stty -echo 2>/dev/null');
    }
    fwrite(STDERR, 'New password for ' . RUNTIME_ROLE . ': ');
    $typed = fgets(STDIN);
    if ($hideInput) {
        shell_exec('stty echo 2>/dev/null');
        fwrite(STDERR, PHP_EOL);
    }
    return $typed === false ? '' : trim($typed);
}

function validate_new_secret(string $password): void
{
    $length = strlen($password);
    if ($length < 12 || $length > 72) {
        throw new RuntimeException('The password must be 12-72 bytes long.');
    }
    if (preg_match('/[\x00-\x1f]/', $password) === 1) {
        throw new RuntimeException('The password must not contain control characters.');
    }
    if (trim($password) !== $password) {
        throw new RuntimeException('The password must not start or end with spaces; the .env reader trims them.');
    }
    if ($length >= 2 && in_array($password[0], ['"', "'"], true) && substr($password, -1) === $password[0]) {
        throw new RuntimeException('Do not wrap the password in quotes; the .env reader strips them.');
    }
}

function write_env_password(string $path, string $password): void
{
    if (!is_file($path)) {
        throw new RuntimeException("Cannot update $path: file not found.");
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException("Cannot read $path.");
    }
    $replaced = false;
    foreach ($lines as $index => $line) {
        if (preg_match('/^\s*DB_PASSWORD\s*=/', $line) === 1) {
            $lines[$index] = 'DB_PASSWORD=' . $password;
            $replaced = true;
        }
    }
    if (!$replaced) {
        $lines[] = 'DB_PASSWORD=' . $password;
    }
    $temporary = $path . '.tmp-' . bin2hex(random_bytes(4));
    if (file_put_contents($temporary, implode(PHP_EOL, $lines) . PHP_EOL) === false) {
        throw new RuntimeException("Cannot write $temporary.");
    }
    @chmod($temporary, 0640);
    if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
        @chown($temporary, 'root');
        @chgrp($temporary, 'www-data');
    }
    if (!rename($temporary, $path)) {
        @unlink($temporary);
        throw new RuntimeException("Cannot replace $path.");
    }
    @chmod($path, 0640);
}

$options = array_slice($argv, 1);
$unknown = array_diff($options, ['--write-env', '--dry-run']);
if ($unknown !== []) {
    fwrite(STDERR, 'Unknown option(s): ' . implode(' ', $unknown) . PHP_EOL);
    fwrite(STDERR, 'Usage: BOOKSHOP_DB_PASSWORD=... php scripts/set-db-password.php [--write-env] [--dry-run]' . PHP_EOL);
    exit(1);
}
$writeEnv = in_array('--write-env', $options, true);
$dryRun = in_array('--dry-run', $options, true);

try {
    $host = set_db_password_env('ADMIN_DB_HOST');
    $port = set_db_password_env('ADMIN_DB_PORT');
    $database = set_db_password_env('ADMIN_DB_NAME');
    $user = set_db_password_env('ADMIN_DB_USER');
    $ownerPassword = getenv('ADMIN_DB_PASSWORD');
    $ownerPassword = $ownerPassword === false ? '' : $ownerPassword;
    $sslmode = getenv('ADMIN_DB_SSLMODE') ?: 'verify-full';
    $rootcert = getenv('ADMIN_DB_SSLROOTCERT') ?: '';
    $localOwner = in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    if ($ownerPassword === '' && !$localOwner) {
        throw new RuntimeException('Missing required environment variable: ADMIN_DB_PASSWORD');
    }
    foreach ([$host, $port, $database, $user, $sslmode, $rootcert] as $value) {
        if (preg_match('/[;\r\n\x00]/', $value) === 1) {
            throw new RuntimeException('Invalid ADMIN_DB_* configuration.');
        }
    }
    if (!ctype_digit($port) || !in_array($sslmode, ['verify-full', 'disable', 'require'], true)) {
        throw new RuntimeException('Invalid ADMIN_DB_PORT or ADMIN_DB_SSLMODE.');
    }
    if (!$localOwner && ($sslmode !== 'verify-full' || $rootcert === '' || !is_file($rootcert))) {
        throw new RuntimeException('Remote admin connections require verify-full and ADMIN_DB_SSLROOTCERT.');
    }

    $newPassword = read_new_secret();
    validate_new_secret($newPassword);

    echo 'Role        : ' . RUNTIME_ROLE . PHP_EOL;
    echo 'Owner login : ' . $user . '@' . $host . ':' . $port . '/' . $database . PHP_EOL;
    echo 'New secret  : ' . strlen($newPassword) . ' bytes' . PHP_EOL;
    if ($dryRun) {
        echo 'Dry run: configuration is valid, no password was changed.' . PHP_EOL;
        exit(0);
    }

    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=$sslmode;connect_timeout=15";
    if ($rootcert !== '') {
        $dsn .= ';sslrootcert=' . $rootcert;
    }
    $owner = new PDO($dsn, $user, $ownerPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $owner->beginTransaction();
    try {
        $role = $owner->prepare('SELECT rolname, rolcanlogin FROM pg_catalog.pg_roles WHERE rolname = ?');
        $role->execute([RUNTIME_ROLE]);
        $existing = $role->fetch();
        $role->closeCursor();
        if ($existing === false) {
            throw new RuntimeException('Role ' . RUNTIME_ROLE . ' does not exist; apply supabase/migrations/ first.');
        }

        // ALTER ROLE does not accept bind parameters, so let PostgreSQL quote the
        // literal itself (%L) instead of escaping it in PHP.
        $quoted = $owner->prepare("SELECT format('ALTER ROLE %I WITH PASSWORD %L', ?::text, ?::text) AS sql");
        $quoted->execute([RUNTIME_ROLE, $newPassword]);
        $statement = (string) $quoted->fetchColumn();
        $quoted->closeCursor();
        $owner->exec($statement);
        $owner->commit();
    } catch (Throwable $error) {
        if ($owner->inTransaction()) {
            $owner->rollBack();
        }
        throw $error;
    }
    $owner = null;

    echo 'Password updated for ' . RUNTIME_ROLE . '.' . PHP_EOL;

    if ($writeEnv) {
        $envPath = dirname(__DIR__) . '/.env';
        write_env_password($envPath, $newPassword);
        putenv('DB_PASSWORD=' . $newPassword);
        echo 'Updated DB_PASSWORD in ' . $envPath . '.' . PHP_EOL;
    }

    if (getenv('DB_HOST') && getenv('DB_USER')) {
        putenv('DB_PASSWORD=' . $newPassword);
        require_once dirname(__DIR__) . '/includes/database.php';
        try {
            database_connect();
            echo 'Runtime login verified: ' . RUNTIME_ROLE . ' can connect with the new password.' . PHP_EOL;
        } catch (Throwable $error) {
            echo 'Warning: runtime login check failed (' . $error->getMessage() . ').' . PHP_EOL;
            echo 'Update DB_PASSWORD where the web application reads it, then retry.' . PHP_EOL;
            exit(1);
        }
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'Password update failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
