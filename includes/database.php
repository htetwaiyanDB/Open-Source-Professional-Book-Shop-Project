<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

final class DbResult
{
    private array $rows;
    private int $position = 0;
    public int $affectedRows;
    public function __construct(array $rows, int $affectedRows) { $this->rows = $rows; $this->affectedRows = $affectedRows; }
    public function fetch() { return $this->rows[$this->position++] ?? false; }
    public function count(): int { return count($this->rows); }
}
function database_connect(): PDO
{
    $host = getenv('DB_HOST') ?: '';
    $user = getenv('DB_USER') ?: '';
    $database = getenv('DB_NAME') ?: 'postgres';
    $port = getenv('DB_PORT') ?: '5432';
    $sslmode = getenv('DB_SSLMODE') ?: 'verify-full';
    $rootcert = getenv('DB_SSLROOTCERT') ?: '';
    if ($host === '' || $user === '') { throw new RuntimeException('Configure DB_HOST and DB_USER using .env.example.'); }
    foreach ([$host, $user, $database, $port, $sslmode, $rootcert] as $value) {
        if (preg_match('/[;\r\n\x00]/', $value)) { throw new RuntimeException('Invalid database configuration.'); }
    }
    $local = getenv('APP_ENV') === 'local' && in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    if (!$local && ($sslmode !== 'verify-full' || $rootcert === '' || !is_file($rootcert))) {
        throw new RuntimeException('Remote connections require verify-full and a valid DB_SSLROOTCERT file.');
    }
    if (!ctype_digit($port) || !in_array($sslmode, ['verify-full', 'disable', 'require'], true)) { throw new RuntimeException('Invalid PostgreSQL settings.'); }
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=$sslmode;connect_timeout=10";
    if ($rootcert !== '') { $dsn .= ';sslrootcert=' . $rootcert; }
    $pdo = new PDO($dsn, $user, getenv('DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_PERSISTENT => false, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $role = $pdo->query('SELECT current_user AS name, rolsuper, rolbypassrls FROM pg_catalog.pg_roles WHERE rolname = current_user')->fetch();
    if (!$role || $role['name'] !== 'bookshop_app' || $role['rolsuper'] || $role['rolbypassrls']) {
        throw new RuntimeException('Connect as restricted bookshop_app, never postgres or a service role.');
    }
    $pdo->exec("SET client_encoding = 'UTF8'");
    return $pdo;
}
// Trusted PHP session identity is set inside each transaction, never at pool-session scope.
function db_query(PDO $connection, string $sql, array $params = []): DbResult
{
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId !== null && (!ctype_digit((string) $userId) || (int) $userId < 1)) { throw new RuntimeException('Invalid server session identity.'); }
    $connection->beginTransaction();
    try {
        $context = $connection->prepare("SELECT set_config('app.user_id', ?, true), set_config('search_path', 'bookshop,pg_catalog', true), set_config('statement_timeout', '10s', true), set_config('lock_timeout', '5s', true)");
        $context->execute([$userId === null ? '' : (string) $userId]);
        $context->closeCursor();
        $statement = $connection->prepare($sql);
        $statement->execute(array_values($params));
        $rows = $statement->columnCount() > 0 ? $statement->fetchAll() : [];
        $affected = $statement->rowCount();
        $statement->closeCursor();
        $connection->commit();
        return new DbResult($rows, $affected);
    } catch (Throwable $error) {
        if ($connection->inTransaction()) { $connection->rollBack(); }
        throw $error;
    }
}
function db_fetch_array(DbResult $result) { return $result->fetch(); }
function db_fetch_assoc(DbResult $result) { return $result->fetch(); }
function db_num_rows(DbResult $result): int { return $result->count(); }
function db_execute(PDO $connection, string $sql, array $params = []): DbResult { return db_query($connection, $sql, $params); }
function db_scalar(PDO $connection, string $sql, array $params = []) { $row = db_fetch_assoc(db_query($connection, $sql, $params)); return $row ? reset($row) : null; }
function current_user_record(): ?array
{
    global $connection;
    if (empty($_SESSION['user_id'])) { return null; }
    return db_fetch_assoc(db_query($connection, 'SELECT userid, username, email, phone, address, role FROM users WHERE userid = ?', [(int) $_SESSION['user_id']])) ?: null;
}
function require_login(): array
{
    $user = current_user_record();
    if (!$user) { redirect('login.php'); }
    return $user;
}
function require_admin(): array
{
    $user = current_user_record();
    if (!$user) { redirect('index.php'); }
    if ($user['role'] !== 'admin') { http_response_code(403); exit('Administrator access required.'); }
    return $user;
}
function authenticate_user(string $requiredRole): void
{
    global $connection;
    verify_csrf();
    $now = time();
    $attempts = array_filter($_SESSION['login_attempts'] ?? [], static fn ($at) => $at > $now - 900);
    if (count($attempts) >= 5) { http_response_code(429); flash_error('Too many sign-in attempts. Please wait 15 minutes.'); return; }
    $attempts[] = $now;
    $_SESSION['login_attempts'] = $attempts;
    $row = db_fetch_assoc(db_query($connection, 'SELECT * FROM login_record(?)', [trim((string) ($_POST['username'] ?? ''))]));
    $hash = $row['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    $valid = password_verify((string) ($_POST['password'] ?? ''), $hash);
    if (!$row || !$valid || $row['role'] !== $requiredRole) { flash_error('Invalid username or password.'); return; }
    session_regenerate_id(true);
    $_SESSION = ['user_id' => (int) $row['userid'], 'user' => $row['username'], 'csrf_token' => bin2hex(random_bytes(32))];
    if ($requiredRole === 'admin') { $_SESSION['admin'] = $row['username']; }
    redirect($requiredRole === 'admin' ? 'dashboard.php' : 'userindex.php');
}
