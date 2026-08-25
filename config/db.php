<?php
/**
 * PDO database connection.
 * Auto-selects local (XAMPP) or Hostinger credentials.
 *
 * Optional override: create config/db.local.php that returns an array:
 * ['host' => '', 'name' => '', 'user' => '', 'pass' => '', 'charset' => 'utf8mb4']
 */

declare(strict_types=1);

function db_is_local(): bool
{
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    $host = strtolower((string) $host);

    return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
        || str_ends_with($host, '.local')
        || str_contains($host, 'localhost');
}

function db_config(): array
{
    $localOverride = __DIR__ . '/db.local.php';
    if (is_file($localOverride)) {
        /** @var array $cfg */
        $cfg = require $localOverride;
        return $cfg;
    }

    if (db_is_local()) {
        return [
            'host' => '127.0.0.1',
            'name' => 'clinica_mi_doc',
            'user' => 'root',
            'pass' => '',
            'charset' => 'utf8mb4',
        ];
    }

    $hostinger = __DIR__ . '/db.hostinger.php';
    if (is_file($hostinger)) {
        /** @var array $cfg */
        $cfg = require $hostinger;
        return $cfg;
    }

    return [
        'host' => 'localhost',
        'name' => 'CAMBIAR_NOMBRE_BD',
        'user' => 'CAMBIAR_USUARIO',
        'pass' => 'CAMBIAR_PASSWORD',
        'charset' => 'utf8mb4',
    ];
}

/**
 * Returns a shared PDO instance or null on failure.
 * Does not throw to the browser; use db_last_error() for the message.
 */
function db(): ?PDO
{
    static $pdo = null;
    static $failed = false;

    if ($failed) {
        return null;
    }

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = db_config();
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $cfg['host'],
        $cfg['name'],
        $cfg['charset'] ?? 'utf8mb4'
    );

    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        $failed = true;
        $GLOBALS['__db_last_error'] = $e->getMessage();
        return null;
    }
}

function db_last_error(): ?string
{
    return $GLOBALS['__db_last_error'] ?? null;
}

/**
 * Lightweight health check for APIs / status pages.
 *
 * @return array{ok: bool, environment: string, message: string}
 */
function db_status(): array
{
    $env = db_is_local() ? 'local' : 'production';
    $connection = db();

    if ($connection instanceof PDO) {
        return [
            'ok' => true,
            'environment' => $env,
            'message' => 'Conexión a la base de datos correcta.',
        ];
    }

    return [
        'ok' => false,
        'environment' => $env,
        'message' => 'No se pudo conectar a la base de datos: ' . (db_last_error() ?? 'error desconocido'),
    ];
}
