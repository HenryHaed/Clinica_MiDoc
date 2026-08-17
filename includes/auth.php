<?php
/**
 * Auth helpers — sesión segura + login PDO (bcrypt).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/** Roles que pueden entrar al panel admin */
const AUTH_PANEL_ROLES = ['super_admin', 'admin', 'doctor', 'enfermero', 'recepcion'];

function auth_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('MIDOCSESSID');
    session_start();
}

function auth_csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['_csrf'];
}

function auth_csrf_validate(?string $token): bool
{
    $session = $_SESSION['_csrf'] ?? '';
    if ($session === '' || $token === null || $token === '') {
        return false;
    }
    return hash_equals($session, $token);
}

/**
 * @return array{id:int,email:string,nombre:string,rol_id:int,rol_slug:string,rol_nombre:string}|null
 */
function auth_user(): ?array
{
    if (empty($_SESSION['auth']['id'])) {
        return null;
    }
    /** @var array{id:int,email:string,nombre:string,rol_id:int,rol_slug:string,rol_nombre:string} */
    return $_SESSION['auth'];
}

function auth_check(): bool
{
    return auth_user() !== null;
}

function auth_role(): ?string
{
    $u = auth_user();
    return $u['rol_slug'] ?? null;
}

function auth_has_role(string ...$slugs): bool
{
    $role = auth_role();
    return $role !== null && in_array($role, $slugs, true);
}

/**
 * Capabilities simples por rol (sin BD aún para permisos).
 */
function auth_can(string $capability): bool
{
    $map = [
        'super_admin' => ['dashboard', 'citas', 'pacientes', 'personal', 'cms', 'usuarios', 'config', 'reportes', 'clinico'],
        'admin' => ['dashboard', 'citas', 'pacientes', 'cms', 'config', 'reportes'],
        'doctor' => ['dashboard', 'citas', 'pacientes', 'clinico'],
        'enfermero' => ['dashboard', 'citas', 'pacientes', 'clinico'],
        'recepcion' => ['dashboard', 'citas', 'pacientes'],
    ];

    $role = auth_role();
    if ($role === null) {
        return false;
    }

    return in_array($capability, $map[$role] ?? [], true);
}

function auth_require(string ...$roles): void
{
    auth_start();
    if (!auth_check()) {
        header('Location: login.php');
        exit;
    }

    if ($roles !== [] && !auth_has_role(...$roles)) {
        http_response_code(403);
        echo 'No tienes permiso para acceder a esta sección.';
        exit;
    }
}

function auth_logout(): void
{
    auth_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

/**
 * Login por email + password.
 *
 * @return array{ok:bool,message:string}
 */
function auth_attempt(string $email, string $password): array
{
    $email = strtolower(trim($email));
    $genericFail = 'Credenciales incorrectas o cuenta no autorizada.';

    if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => $genericFail];
    }

    // Anti fuerza bruta simple por sesión
    $fails = (int) ($_SESSION['_login_fails'] ?? 0);
    $lockedUntil = (int) ($_SESSION['_login_lock'] ?? 0);
    if ($lockedUntil > time()) {
        return ['ok' => false, 'message' => 'Demasiados intentos. Espera un momento e inténtalo de nuevo.'];
    }

    $pdo = db();
    if (!$pdo instanceof PDO) {
        return ['ok' => false, 'message' => 'No hay conexión a la base de datos. Verifica MySQL / phpMyAdmin.'];
    }

    $sql = 'SELECT u.id, u.email, u.password_hash, u.nombre, u.activo, u.deleted_at,
                   u.rol_id, r.slug AS rol_slug, r.nombre AS rol_nombre, r.activo AS rol_activo
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            WHERE u.email = :email
            LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();

    // Comparación constante aunque no exista el usuario
    $hash = is_array($row) ? (string) $row['password_hash'] : '$2y$10$usesomesillystringforexactly22';
    $valid = password_verify($password, $hash);

    if (
        !is_array($row)
        || !$valid
        || !(int) $row['activo']
        || $row['deleted_at'] !== null
        || !(int) $row['rol_activo']
        || !in_array((string) $row['rol_slug'], AUTH_PANEL_ROLES, true)
    ) {
        $_SESSION['_login_fails'] = $fails + 1;
        if ($_SESSION['_login_fails'] >= 5) {
            $_SESSION['_login_lock'] = time() + 60;
            $_SESSION['_login_fails'] = 0;
        }
        return ['ok' => false, 'message' => $genericFail];
    }

    // Rehash si el algoritmo por defecto cambió
    if (password_needs_rehash((string) $row['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE usuarios SET password_hash = :h WHERE id = :id');
        $upd->execute(['h' => $newHash, 'id' => (int) $row['id']]);
    }

    $updLogin = $pdo->prepare('UPDATE usuarios SET last_login_at = NOW() WHERE id = :id');
    $updLogin->execute(['id' => (int) $row['id']]);

    session_regenerate_id(true);
    unset($_SESSION['_login_fails'], $_SESSION['_login_lock']);

    $_SESSION['auth'] = [
        'id' => (int) $row['id'],
        'email' => (string) $row['email'],
        'nombre' => (string) $row['nombre'],
        'rol_id' => (int) $row['rol_id'],
        'rol_slug' => (string) $row['rol_slug'],
        'rol_nombre' => (string) $row['rol_nombre'],
    ];

    // Audit ligero
    try {
        $audit = $pdo->prepare(
            'INSERT INTO audit_log (usuario_id, accion, tabla_afectada, registro_id, payload_json, ip)
             VALUES (:uid, :accion, :tabla, :rid, :payload, :ip)'
        );
        $audit->execute([
            'uid' => (int) $row['id'],
            'accion' => 'login',
            'tabla' => 'usuarios',
            'rid' => (string) $row['id'],
            'payload' => json_encode(['rol' => $row['rol_slug']], JSON_UNESCAPED_UNICODE),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        // no bloquear login por audit
    }

    return ['ok' => true, 'message' => 'OK'];
}
